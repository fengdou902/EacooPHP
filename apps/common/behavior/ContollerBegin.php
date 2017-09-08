<?php
namespace app\common\behavior;

use think\Hook;
class ControllerBegin {

	public function run(&$request) {
		dump($request);
	}

}