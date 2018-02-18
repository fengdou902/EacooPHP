<?php
// 行为模型       
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use app\common\model\Base;

class Action extends Base {
    
    /**
     * 获取状态显示
     * @param  [type] $value [description]
     * @param  [type] $data [description]
     * @return [type] [description]
     * @date   2018-02-08
     * @author 心云间、凝听 <981248356@qq.com>
     */
    protected function getStatusTextAttr($value, $data){
		$status = array(-1=>'删除',0=>'禁用',1=>'正常');
		return $status[$data['status']];
	}

	protected function getActionTypeTextAttr($value, $data){
		//执行类型。1自定义操作，2记录操作
		$text = [1=>'自定义操作',2=>'记录操作'];
		return $text[$data['action_type']];
	}

}
