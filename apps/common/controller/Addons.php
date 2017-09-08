<?php
// 插件控制器       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2016 http://www.eacoomall.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author: 心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\controller;

class Addons extends Base {

	public $info             = [];
	public $addon_path       = '';
	public $config_file      = '';
	public $custom_config    = '';
	public $admin_list       = [];
	public $custom_adminlist = '';
	public $access_url       = [];

	public function _initialize() {
		$mc = $this->getAddonsName();

		$this->addon_path = ROOT_PATH . "addons/{$mc}/";
		if (is_file($this->addon_path . 'config.php')) {
			$this->config_file = $this->addon_path . 'config.php';
		}
	}

	/**
	 * 插件模版
	 * @param  [type] $template [description]
	 * @return [type] [description]
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function template($template) {
		$mc                         = $this->getAddonsName();
		$ac                         = input('ac', '', 'trim,strtolower');
		$parse_str                  = \think\Config::get('parse_str');
		$parse_str['__ADDONROOT__'] = ROOT_PATH . "/addons/{$mc}";
		\think\Config::set('parse_str', $parse_str);

		if ($template) {
			$template = $template;
		} else {
			$template = $mc . "/" . $ac;
		}

		$this->view->engine(
			array('view_path' => "addons/" . $mc . "/view/")
		);
		echo $this->fetch($template);
	}

	final public function getAddonsName() {
		$mc = input('mc', '', 'trim');
		if ($mc) {
			return $mc;
		} else {
			$class = get_class($this);
			return substr($class, strrpos($class, '\\') + 1);
		}
	}

	final public function checkInfo() {
		$info_check_keys = array('name', 'title', 'description', 'status', 'author', 'version');
		foreach ($info_check_keys as $value) {
			if (!array_key_exists($value, $this->info)) {
				return false;
			}

		}
		return true;
	}

	/**
	 * 获取插件配置
	 * @return [type] [description]
	 * @date   2017-08-30
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function getConfig() {

		static $_config = [];
		if (empty($name)) {
			$name = $this->getAddonsName();
		}
		if (isset($_config[$name])) {
			return $_config[$name];
		}
		$config        = [];
		$map['name']   = $name;
		$map['status'] = 1;
		$config        = db('Addons')->where($map)->value('config');
		if ($config) {
			$config = json_decode($config, true);
		} else {
			$temp_arr = include $this->config_file;
			foreach ($temp_arr as $key => $value) {
				if ($value['type'] == 'group') {
					foreach ($value['options'] as $gkey => $gvalue) {
						foreach ($gvalue['options'] as $ikey => $ivalue) {
							$config[$ikey] = $ivalue['value'];
						}
					}
				} else {
					$config[$key] = $temp_arr[$key]['value'];
				}
			}
		}
		$_config[$name] = $config;
		return $config;
	}

	/**
	 * 获取插件所需的钩子是否存在，没有则新增
	 * @param string $hook_name  钩子名称
	 * @param string $addon_name  插件名称
	 * @param string $description  插件描述
	 */
	public function existHook($hook_name, $addon_name, $description = '') {
		$hook_mod      = db('Hooks');
		$where['name'] = $hook_name;
		$gethook       = $hook_mod->where($where)->find();
		$gethook       = $gethook->toArray();
		if (!$gethook || empty($gethook) || !is_array($gethook)) {
			$data['name']        = $hook_name;
			$data['description'] = $description;
			$data['type']        = 1;
			$data['create_time'] = time();
			$data['update_time'] = time();
			$data['addons']      = $addon_name;
			$hook_mod->insert($data);
		}
	}

	/**
	 * 删除钩子
	 * @param string $hook  钩子名称
	 */
	public function deleteHook($hook) {
		$model     = db('hooks');
		$map = array(
			'name' => $hook,
		);
		$model->where($map)->delete();
	}
}