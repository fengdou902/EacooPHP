<?php
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2016 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author: 心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------

namespace app\install\controller;

class Index extends \think\Controller {

	protected $status;

	public function _initialize() {
		$this->status = [
			'index'    => 'info',
			'check'    => 'info',
			'config'   => 'info',
			'sql'      => 'info',
			'complete' => 'info',
		];

		if (request()->action() != 'complete' && is_file(APP_PATH . 'database.php') && is_file(APP_PATH . 'install.lock')) {
			return $this->redirect('index/index/index');
		}
		$this->assign('product_name',config('product_name'));//产品名
	}

	public function index() {
		$this->status['index'] = 'primary';
		$this->assign('status', $this->status);

        
        $this->assign('company_name',config('company_name'));//公司名
        $this->assign('company_website_domain',config('company_website_domain'));
        $this->assign('website_domain',config('website_domain'));
		return $this->fetch();
	}

	/**
	 * 检查目录
	 * @return [type] [description]
	 * @date   2017-09-07
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function check() {
		session('error', false);

		//环境检测
		$env = check_env();

		//目录文件读写检测
		if (IS_WRITE) {
			$dirfile = check_dirfile();
			$this->assign('dirfile', $dirfile);
		}

		//函数检测
		$func = check_func();

		session('step', 1);

		$this->assign('env', $env);
		$this->assign('func', $func);

		$this->status['index'] = 'success';
		$this->status['check'] = 'primary';
		$this->assign('status', $this->status);
		return $this->fetch();
	}

	/**
	 * 配置数据库
	 * @param  [type] $db [description]
	 * @param  [type] $admin [description]
	 * @param  [type] $webconfig [description]
	 * @return [type] [description]
	 * @date   2017-09-07
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function config($db = null, $admin = null, $webconfig = null) {
		if (request()->IsPost()) {
			//检测管理员信息
			if (!is_array($admin) || empty($admin[0]) || empty($admin[1]) || empty($admin[3])) {
				return $this->error('请填写完整管理员信息');
			} else if ($admin[1] != $admin[2]) {
				return $this->error('确认密码和密码不一致');
			} else {
				$info = [];
				list($info['username'], $info['password'], $info['repassword'], $info['email']) = $admin;
				//缓存管理员信息
				session('admin_info', $info);
			}

			//检测网站配置信息
			if (!is_array($webconfig) || empty($webconfig[0]) || empty($webconfig[1]) || empty($webconfig[3])) {
				return $this->error('请填写完整管理员信息');
			} else {
				$web_config = [];
				list($web_config['web_site_title'], $web_config['index_url'], $web_config['web_site_description'], $web_config['web_site_keyword']) = $webconfig;
				//缓存管理员信息
				session('web_config', $web_config);
			}

			//检测数据库配置
			if (!is_array($db) || empty($db[0]) || empty($db[1]) || empty($db[2]) || empty($db[3])) {
				return $this->error('请填写完整的数据库配置');
			} else {
				$DB = [];
				list($DB['type'], $DB['hostname'], $DB['database'], $DB['username'], $DB['password'],
					$DB['hostport'], $DB['prefix']) = $db;
				//缓存数据库配置
				session('db_config', $DB);

				//创建数据库
				$dbname = $DB['database'];
				unset($DB['database']);
				$db  = \think\Db::connect($DB);
				$sql = "CREATE DATABASE IF NOT EXISTS `{$dbname}` DEFAULT CHARACTER SET utf8";
				if (!$db->execute($sql)) {
					return $this->error($db->getError());
				} else {
					$this->redirect('install/index/sql');
				}
			}
		} else {
			$this->status['index']  = 'success';
			$this->status['check']  = 'success';
			$this->status['config'] = 'primary';
			$this->assign('status', $this->status);
			return $this->fetch();
		}
	}

	/**
	 * 数据库安装
	 * @return [type] [description]
	 */
	public function sql() {
		session('error', false);
		$this->status['index']  = 'success';
		$this->status['check']  = 'success';
		$this->status['config'] = 'success';
		$this->status['sql']    = 'primary';
		$this->assign('status', $this->status);
		echo $this->fetch();
		if (session('update')) {
			$db = \think\Db::connect();
			//更新数据表
			update_tables($db, config('prefix'));
		} else {
			//连接数据库
			$dbconfig = session('db_config');
			$db       = \think\Db::connect($dbconfig);
			//创建数据表
			create_tables($db, $dbconfig['prefix']);
			//更新网站信息
			update_webconfig($db, $dbconfig['prefix'], session('web_config'));
			//注册创始人帐号
			register_administrator($db, $dbconfig['prefix'], session('admin_info'));

			//创建配置文件
			$conf = write_config($dbconfig);
			session('config_file', $conf);
		}

		if (session('error')) {
			show_msg('失败');
		} else {
			echo '<script type="text/javascript">location.href = "'.url('install/index/complete').'";</script>';
		}

	}

	/**
	 * 完成
	 * @return [type] [description]
	 * @date   2017-09-07
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function complete() {
		$this->status['index']    = 'success';
		$this->status['check']    = 'success';
		$this->status['config']   = 'success';
		$this->status['sql']      = 'success';
		$this->status['complete'] = 'primary';
		$this->assign('status', $this->status);
		$this->assign('status', $this->status);
		return $this->fetch();
	}
}