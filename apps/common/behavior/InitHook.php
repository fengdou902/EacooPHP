<?php
// 初始化钩子行为
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\behavior;

use app\admin\model\Hooks as HooksModel;
use app\admin\model\HooksExtra as HooksExtraModel;
use app\admin\model\Plugins as PluginsModel;
use app\admin\model\Modules as ModulesModel;

use think\Hook;

class InitHook {

	public function run(&$request) {
		//未安装时不执行
		if (substr(request()->pathinfo(), 0, 7) != 'install' && is_file(APP_PATH . 'database.php') && is_file(APP_PATH . 'install.lock')) {

			//扩展插件
			\think\Loader::addNamespace('plugins', ROOT_PATH . '/plugins/');

			$this->setHook();

			//设置模型内容路由
			//$this->setRoute();
		}
	}

	/**
	 * 设置钩子行为
	 * @date   2017-09-20
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	protected function setHook() {
		$data = cache('hooks');
		if (!$data) {
			$hooks = HooksModel::where('status',1)->column('name','id');
			$hooksExtraModel = new HooksExtraModel();
			foreach ($hooks as $hook_id => $hook_name) {
				$hooks_class = [];
				$module_depends = $hooksExtraModel->where(['hook_id'=>$hook_id,'status'=>1,'depend_type'=>1])->order('sort asc,create_time desc')->column('depend_flag');
				//模块
				if ($module_depends) {
					$map = [
						'status'=>1,
						'name'=>['in',$module_depends],
					];
					$data  = ModulesModel::where($map)->column('name');
					if ($data) {
						$modules = array_intersect($module_depends, $data);
						foreach ($modules as $key => $module) {
							$hooks_class[] = "\\app\\" . $module . "\\widget\\Hooks";
						}
						
					}
				}

				$plugin_depends = $hooksExtraModel->where(['hook_id'=>$hook_id,'status'=>1,'depend_type'=>2])->order('sort asc,create_time desc')->column('depend_flag');

				//插件
				if (!empty($plugin_depends)) {
					$map = [
						'status'=>1,
						'name'=>['in',$plugin_depends],
					];
					$data          = PluginsModel::where($map)->column('name');
					if ($data) {
						$plugins = array_intersect($plugin_depends, $data);
						$hooks_class = array_merge($hooks_class,array_map('get_plugin_class', $plugins));
					}
				}
				Hook::add($hook_name, $hooks_class);

			}
			// if (config('develop_mode') == 0) {
			 	cache('hooks', Hook::get());
			// }
		} else {
			unset($data['app_init']);
			unset($data['app_begin']);
			unset($data['module_init']);
			unset($data['action_begin']);
			unset($data['app_end']);
			Hook::import($data, false);
		}
	}

	protected function setRoute() {
		$list = db('Rewrite')->where('status',1)->select();
		foreach ($list as $key => $value) {
			$route[$value['rule']] = $value['url'];
		}
		$map   = array(
			'status' => array('gt', 0),
			'extend' => array('gt', 0),
		);
		//$list = db('Module')->where($map)->field("name,id,title,'' as 'style'")->select();
		// foreach ($list as $key => $value) {
		// 	$route["admin/" . $value['name'] . "/index"]  = "admin/content/index?model_id=" . $value['id'];
		// 	$route["admin/" . $value['name'] . "/add"]    = "admin/content/add?model_id=" . $value['id'];
		// 	$route["admin/" . $value['name'] . "/edit"]   = "admin/content/edit?model_id=" . $value['id'];
		// 	$route["admin/" . $value['name'] . "/del"]    = "admin/content/del?model_id=" . $value['id'];
		// 	$route["admin/" . $value['name'] . "/status"] = "admin/content/status?model_id=" . $value['id'];
		// 	$route[$value['name'] . "/index"]             = "index/content/index?model=" . $value['name'];
		// 	$route[$value['name'] . "/list/:id"]          = "index/content/lists?model=" . $value['name'];
		// 	$route[$value['name'] . "/detail/:id"]        = "index/content/detail?model_id=" . $value['id'];
		// 	$route["/list/:id"]                           = "index/content/category";
		// 	$route["user/" . $value['name'] . "/index"]   = "user/content/index?model_id=" . $value['id'];
		// 	$route["user/" . $value['name'] . "/add"]     = "user/content/add?model_id=" . $value['id'];
		// 	$route["user/" . $value['name'] . "/edit"]    = "user/content/edit?model_id=" . $value['id'];
		// 	$route["user/" . $value['name'] . "/del"]     = "user/content/del?model_id=" . $value['id'];
		// 	$route["user/" . $value['name'] . "/status"]  = "user/content/status?model_id=" . $value['id'];
		// }
		// \think\Route::rule($route);
	}
}