<?php
// 用户
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
use app\common\model\User;
use app\admin\model\Action;
use app\admin\model\AuthGroupAccess;
use app\common\model\ActionLog;

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 * @author 心云间、凝听 <981248356@qq.com>
 */
function is_login() {
	return User::isLogin();
}

/**
 * 检测用户是否为管理员
 * @return boolean true-管理员，false-非管理员
 * @author 心云间、凝听 <981248356@qq.com>
 */
function is_administrator($uid = null) {
	$uid = is_null($uid) ? is_login() : $uid;
    if ($uid==1) {
        return true;
    } elseif ($uid>1) {
        if (in_array($uid, get_administrators())) {
            return true;
        }
    }
    return false;
}

/**
 * 获取超级管理员用户
 * @return [type] [description]
 * @date   2017-10-17
 * @author 心云间、凝听 <981248356@qq.com>
 */
function get_administrators()
{
    return AuthGroupAccess::groupUserUids(1);
}

/**
 * 获取用户组信息
 * @param  string $uid [description]
 * @return [type] [description]
 * @date   2017-10-17
 * @author 心云间、凝听 <981248356@qq.com>
 */
function get_user_groups($uid='')
{
    if ($uid>0) {
        $auth = new \org\util\Auth();
        return $auth->getGroups($uid);
    }
    return false;
}

/**
 * 根据用户ID获取用户信息
 * @param  integer $id 用户ID
 * @return array  用户信息
 */
function get_user_info($uid) {
    if ($uid>0) {
        return User::info($uid);
    }
    return false;
    
}

/**
 * 获取用户名
 * @param  integer $uid [description]
 * @return [type] [description]
 * @date   2017-09-25
 * @author 心云间、凝听 <981248356@qq.com>
 */
function get_nickname($uid=0)
{
    if ($uid>0) {
        return User::where('uid',$uid)->value('nickname');
    }
    return false;
}

/**
 * 数据签名认证
 * @param  array $data 被认证的数据
 * @return string       签名
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function data_auth_sign($data)
{
    //数据类型检测
    if (!is_array($data)) {
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

/**
 * 行为日志记录
 * @param  integer $uid 用户ID
 * @param  array $data 数据
 * @param  string $remark 备注
 * @return [type] [description]
 * @date   2017-10-03
 * @author 心云间、凝听 <981248356@qq.com>
 */
function action_log($action_id = 0, $uid = 0, $data = [], $remark = '')
{
    if ($uid >0 ) {
        $action_log_model = new ActionLog;
        if (is_array($data)) {
            $data = json_encode($data);
        }
        // 保存日志
        return $res = $action_log_model->record($action_id ,$uid,$data,$remark);
    }
}