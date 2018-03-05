<?php
// 安装
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------

namespace app\install\controller;
use think\Controller;
use think\Db;

error_reporting(0);

class Index extends Controller {

	protected $status;

	public function _initialize() {
		$this->status = [
			'index'    => 'info',
			'check'    => 'info',
			'config'   => 'info',
			'sql'      => 'info',
			'complete' => 'info',
		];

		if ($this->request->action() != 'complete' && is_file(APP_PATH . 'database.php') && is_file(APP_PATH . 'install.lock')) {
			return $this->redirect('admin/login/index');
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
		if ($this->request->isPost()) {
			try {
				if (session('error')) {
					throw new \Exception("环境检测没有通过，请调整环境后重试！", 0);
				}
			} catch (\Exception $e) {
				$this->error($e->getMessage());
			}
			$this->success('恭喜您环境检测通过', url('config'));
		} else{
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
		if ($this->request->isPost()) {
			//$admin = $this->request->param('admin');
			//检测数据库配置
			$result = $this->validate($db,'InstallConfig.db_config');
            if(true !== $result){
                $this->error($result);
            }
            //检测网站配置信息
            $result = $this->validate($webconfig,'InstallConfig.web_config');
            if(true !== $result){
                $this->error($result);
            }

            $result = $this->validate($admin,'InstallConfig.admin_info');
            if(true !== $result){
                $this->error($result);
            }

			//缓存管理员信息
			$admin_info = [
				'username'   => $admin['admin_username'],
				'password'   => $admin['admin_password'],
				'repassword' => $admin['admin_repassword'],
				'email'      => $admin['admin_email'],
			];
			session('admin_info', $admin_info);
			//缓存管理员信息
			session('web_config', $webconfig);
			//缓存数据库配置
			session('db_config', $db);

			//创建数据库
			$dbname = $db['database'];
			unset($db['database']);
			$db_obj  = \think\Db::connect($db);
			$sql = "CREATE DATABASE IF NOT EXISTS `{$dbname}` DEFAULT CHARACTER SET utf8";
			if (!$db_obj->execute($sql)) {
				return $this->error($db_obj->getError());
			} else {
				$this->redirect('install/index/sql');
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
		return $this->fetch();
	}
}