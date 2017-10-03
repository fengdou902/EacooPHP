<?php
//记录行为日志
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
			$data = [
				'param'=>$request->param(),
			];
			if (is_array($data)) {
	            $data = json_encode($data);
	        }
			$uid    = is_login();
			$remark = $info['title'];
	        // 保存日志
	        return $res = $action_log_model->record($info['id'],$uid,$data,$remark);
		}
		
	}

}