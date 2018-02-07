<?php
// 行为日志模型       
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\model;

use app\admin\model\Action;

use think\Request;

class ActionLog extends Base {
    
    protected $updateTime = false;

    //获取用户昵称
    public function getNicknameAttr($value,$data)
    {
    	$nickname = '';
    	if ($data['uid']>0) {
    		$nickname = User::where('uid',$data['uid'])->value('nickname');
    	}

        return $nickname;
    }

    //获取行为标识
    public function getActionNameAttr($value,$data)
    {
    	$name = '';
    	if ($data['action_id']>0) {
    		$name = Action::where('id',$data['action_id'])->value('name');
    	}

        return $name;
    }
}
