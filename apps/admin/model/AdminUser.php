<?php
// 后台用户模型
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\model;
use app\common\model\Base;

class AdminUser extends Base
{
    // 设置数据表（不含前缀）
    protected $name = 'admin';

    // 自动完成
    protected $auto       = ['last_login_ip'];
    // protected $insert     = ['password'];
    // //protected $update     = ['password'];

    // public function setPasswordAttr($value)
    // {
    //     if (!empty($value)) {
    //         return encrypt($value);
    //     } else{
    //         return $value;
    //     }
        
    // }

    public function setLastLoginIpAttr($value)
    {
        return request()->ip();
        
    }

    public function getStatusTextAttr($value,$data)
    {
        $status = [ 1 => '正常', -1 => '删除', 0 => '禁用', 2 => '待审核', 3 => '草稿'];
        return isset($status[$data['status']]) ? $status[$data['status']] : '未知';
    }

    public function getSexAttr($value,$data)
    {
        $sex = [ 0 => '保密', 1 => '男', 2 => '女'];
        return isset($sex[$data['sex']]) ? $sex[$data['sex']] : '未知';
    }

}