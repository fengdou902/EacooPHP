<?php
// 用户等级模型
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\user\model;
use app\common\model\Base;

class UserLevel extends Base
{
    // 设置数据表（不含前缀）
    protected $name = 'user_level';

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    //状态
    public function getStatusTextAttr($value,$data)
    {
        $status = [ 1 => '正常', -1 => '删除', 0 => '禁用'];
        return isset($status[$data['status']]) ? $status[$data['status']] : '未知';
    }

}