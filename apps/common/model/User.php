<?php
// 用户模型
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\model;

class User extends Base
{
	// 设置数据表（不含前缀）
    protected $name = 'users';

    // 定义时间戳字段名
    protected $createTime = 'reg_time';
    protected $updateTime = '';
    // 自动完成
    protected $auto       = ['last_login_ip'];
    protected $insert     = ['register_ip','password'];
    //protected $update     = ['password'];

    public function setRegisterIpAttr($value)
    {
        return request()->ip();
        
    }

    public function setPasswordAttr($value)
    {
        if (!empty($value)) {
            return encrypt($value);
        } else{
            return $value;
        }
        
    }

    public function getStatusTextAttr($value,$data)
    {
        $status = [ 1 => '正常', -1 => '删除', 0 => '禁用', 2 => '待审核', 3 => '草稿'];
        return isset($status[$data['status']]) ? $status[$data['status']] : '未知';
    }

    public function getSexTextAttr($value,$data)
    {
        $sex = [ 0 => '保密', 1 => '男', 2 => '女'];
        return isset($sex[$data['sex']]) ? $sex[$data['sex']] : '未知';
    }

    /**
     * 获取注册的时间戳
     * @param  [type] $value [description]
     * @param  [type] $data [description]
     * @return [type] [description]
     * @date   2018-02-28
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getRegTimestampAttr($value,$data)
    {
        $timestamp = $data['reg_time'];
        //判断是否是时间戳
        // if(strtotime(date('m-d-Y H:i:s',$timestamp)) != $timestamp) {
        //     $timestamp = strtotime($timestamp);
        // }
        
        return $timestamp;
    }

}