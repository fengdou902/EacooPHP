<?php
// 插件控制器       
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\controller;
use app\admin\model\Hooks;
use app\admin\model\Plugins;
use app\admin\controller\Extension;

class Plugin extends Base {
	
	public $name             = '';
	public $info             = [];
	public $hooks            = [];
	public $pluginPath       = '';
	//public $optionsFile    = '';
	public $custom_config    = '';
	public $admin_list       = [];
	public $custom_adminlist = '';
	public $access_url       = [];

	public function _initialize() {
		parent::_initialize();
		
		$class = get_class($this);
		$path = strstr($class,substr($class, strrpos($class, '\\') + 1),true);
		$this->pluginPath = ROOT_PATH.str_replace('\\','/',$path);

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
		$plugin_name = input('param.plugin_name');

        if ($plugin_name != '') {
            $plugin = $plugin_name;
            $action = 'index';
        } else {
            $plugin = input('param._plugin',$this->name);
            $action = input('param._action');
        }
        $template = $template == '' ? $action : $template;
        if (MODULE_MARK === 'admin') {
        	$template = 'admin/'.$template;
        }
        if (!is_file($template)) {
        	if (MODULE_MARK != 'admin') {
        		// 获取当前主题的名称
	            $current_theme_path = THEME_PATH.CURRENT_THEME.'/'; //默认主题设为当前主题
	        	$theme_plugin_path = $current_theme_path.'plugins/'.$plugin.'/'; //当前主题插件文件夹路径
	        	$theme_template = $theme_plugin_path.$template . '.' .config('template.view_suffix');
        	} else{
        		$theme_template = $template;
        	}
        	
        	if (!is_file($theme_template)) {
        		$template = $this->pluginPath. 'view/'. $template . '.' .config('template.view_suffix');
	            if (!is_file($template)) {
	                throw new \Exception('模板不存在：'.$template, 5001);
	            }
        	} else{
        		$template = $theme_template;
        	}
            
            
        }

        echo $this->view->fetch($template, $vars, $replace, $config, $render);
	}

	/**
	 * 获取插件名
	 * @return [type] [description]
	 * @date   2017-09-18
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	final public function getPluginInfo() {
		$name = input('param._plugin', '', 'trim');

		if ($name) {
			
			$extensionObj = new Extension;
			$extensionObj->initInfo('plugin',$name);
			$this->pluginPath = $extensionObj->appExtensionPath;
			return $extensionObj->getInfoByFile();
		} else {
			$info_file = $this->pluginPath.'install/info.json';
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
		$config = [];
		$map = [
			'name'   =>$name,
			'status' =>1,
		];
		$config  = Plugins::where($map)->value('config');
		if ($config) {
			$config = json_decode($config, true);
		} else {
			$extensionObj = new Extension;
			$config = $extensionObj->getDefaultConfig($name);
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