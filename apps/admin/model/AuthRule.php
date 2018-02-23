<?php
// 权限规则模型       
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

class AuthRule extends Base
{
	// 设置完整的数据表（包含前缀）
    // protected $table = 'think_access';

    // 设置数据表（不含前缀）
    // protected $name = 'auth_rule';

	// 设置birthday为时间戳类型（整型）
    // protected $type       = [
    //     'birthday' => 'timestamp',
    // ];
    
    //获取显示位置
    protected function getPositionTextAttr($value, $data){
        $text = ['left'=>'侧边栏','top'=>'头部'];
        return $text[$data['position']];
    }

    //获取父级菜单
    protected function getParentMenuAttr($value, $data){
        $parent_menu = $this->where(['id'=>$data['pid']])->value('title');
        return $parent_menu;
    }

}