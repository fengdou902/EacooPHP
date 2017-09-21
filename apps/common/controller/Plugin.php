<?php
// 插件控制器       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2016 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author: 心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\controller;
use app\admin\model\Hooks;
use app\admin\model\Plugins;

class Plugin extends Base {
	
	public $name = '';
	public $info             = [];
	public $hooks             = [];
	public $pluginPath       = '';
	//public $optionsFile    = '';
	public $custom_config    = '';
	public $admin_list       = [];
	public $custom_adminlist = '';
	public $access_url       = [];

	public function _initialize() {
		parent::_initialize();

		$this->info = $this->getPluginInfo();
		$this->name = $this->info['name'];

		$this->assign('_admin_public_base_', APP_PATH.'/admin/view/public/base.html');  // 页面公共继承模版
        $this->assign('_admin_public_iframe_base_', APP_PATH.'/admin/view/public/iframe_base.html');  // 页面公共继承模版
	}

	/**
     * 插件模版输出
     * @param  string $templateFile 模板文件名
     * @param  array  $vars         模板输出变量
     * @param  array  $replace      模板替换
     * @param  array  $config       模板参数
     * @param  array  $render       是否渲染内容
     * @return [type]               [description]
     */
	public function fetch($template='', $vars = [], $replace = [], $config = [] ,$render=false) {

		$name                                = $this->name;
		$ac                                  = input('ac', '', 'trim,strtolower');
		$view_replace_str                    = config('view_replace_str');
		$view_replace_str['__PLUGIN_STATIC__'] = config('view_replace_str.__STATIC__').'/plugins/'.$name;
		config('view_replace_str', $view_replace_str);
		if ($template != '') {
            if (!is_file($template)) {
                $template = $this->pluginPath. 'view/'. $template . '.' .config('template.view_suffix');
                if (!is_file($template)) {
                    throw new \Exception('模板不存在：'.$template, 5001);
                }
            }

            echo $this->view->fetch($template, $vars, $replace, $config, $render);
        }
	}

	/**
	 * 获取插件名
	 * @return [type] [description]
	 * @date   2017-09-18
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	final public function getPluginInfo() {
		$name = input('mc', '', 'trim');
		if ($name) {
			return Plugins::getInfoByFile($name);
		} else {
			$class = get_class($this);
			$path = strstr($class,substr($class, strrpos($class, '\\') + 1),true);
			$path = ROOT_PATH.str_replace('\\','/',$path);
			$this->pluginPath = $path;
			$info_file = $path.'install/info.json';
			if (is_file($info_file)) {
				 $module_info = file_get_contents($info_file);
	             $module_info = json_decode($module_info,true);
	             return $module_info;
			} else{
				return [];
			}
		}
	}

	/**
	 * 检测信息
	 * @return [type] [description]
	 * @date   2017-09-18
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	final public function checkInfo() {
		$info_check_keys = ['name', 'title', 'description', 'status', 'author', 'version'];
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
		$name = $this->name;
		if (isset($_config[$name])) {
			return $_config[$name];
		}
		$config        = [];
		$map = [
			'name'   =>$name,
			'status' =>1,
		];
		$config        = Plugins::where($map)->value('config');
		if ($config) {
			$config = json_decode($config, true);
		} else {
			$config = Plugins::getDefaultConfig($name);
		}
		$_config[$name] = $config;
		return $config;
	}

	/**
	 * 获取插件所需的钩子是否存在，没有则新增
	 * @param string $hook_name  钩子名称
	 * @param string $plugin_name  插件名称
	 * @param string $description  插件描述
	 */
	public function existHook($hook_name, $plugin_name, $description = '') {
		$map = [
			'name'=>$hook_name,
		];
		$gethook       = Hooks::where($map)->find();
		$gethook       = $gethook->toArray();
		if (!$gethook || empty($gethook) || !is_array($gethook)) {
			$data = [
				'name'        => $hook_name,
				'description' => $description,
				'type'        => 1,
				'plugins'     => $plugin_name,
				'create_time' => time(),
				'update_time' => time(),
			];
			Hooks::insert($data);
		}
	}

	/**
	 * 删除钩子
	 * @param string $hook  钩子名称
	 */
	public function deleteHook($hook) {
		$map = array(
			'name' => $hook,
		);
		$res = Hooks::destroy($map);
		return $res;
	}
}