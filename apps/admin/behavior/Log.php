<?php
// 记录行为日志
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\behavior;

use app\admin\model\Action;
use app\common\model\ActionLog;
use think\Request;

class Log {

	public function run(&$param) {
		$request = Request::instance();
		// 获取行为
		$module_name = $request->module();
		$current_action_name = strtolower($request->controller().'_'.$request->action());
		$info = Action::get(function($query) use($module_name,$current_action_name){
		    $query->where([
				'depend_type' => 1,
				'depend_flag' => $module_name,
				'name'        => $current_action_name,
				'status'      => 1
		    ])->field('id,title,action_type');
		});
		if ($info) {
			$action_log_model = new ActionLog;
			$params = [
				'param'=>$request->get(),//只记录get的参数。因为post的参数带有敏感数据
			];
			if (is_array($params)) {
	            $params = json_encode($params);
	        }
			$uid    = is_login();
			$remark = $info['title'];
	        // 保存日志
	        return $res = $action_log_model->record($info['id'],$uid,$params,$remark);
		}
		
	}

}