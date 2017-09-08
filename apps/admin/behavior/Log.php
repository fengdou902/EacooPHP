<?php
namespace app\admin\behavior;

use app\admin\model\Action;
class Log {

	public function run(&$request) {
		// 获取行为
		$list = Action::all(function($query){
		    $query->where(['action_type'=>2,'status'=>1])->field('module,name');
		});
		$c_request = request();
		$current_action_name = strtolower($c_request->controller().'_'.$c_request->action());
		if ($list) {
			$uid = is_login();
			foreach ($list as $key => $val) {
				if ($current_action_name==$val['name']) {
					action_log($val['name'], '', $uid, $uid,2);
				}
				
			}
		}
	}

}