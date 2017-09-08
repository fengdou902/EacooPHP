<?php
// 行为模型       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use app\common\model\Base;

class Action extends Base {
    
    protected $insert = ['status'=>1];
    
    protected function getStatusTextAttr($value, $data){
		$status = array(-1=>'删除',0=>'禁用',1=>'正常',2=>'待审核');
		return $status[$data['status']];
	}

	protected function getActionTypeTextAttr($value, $data){
		//执行类型。1自定义操作，2记录操作
		$text = [1=>'自定义操作',2=>'记录操作'];
		return $text[$data['action_type']];
	}

}
