<?php
// 用户
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
use app\admin\logic\AdminUser as AdminUserLogic;
use app\admin\logic\AuthGroupAccess as AuthGroupAccessLogic;
use app\common\logic\Action as ActionLogic;

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 * @author 心云间、凝听 <981248356@qq.com>
 */
function is_admin_login() {
    return AdminUserLogic::isLogin();
}

/**
 * 检测用户是否为超级管理员
 * @return boolean true-管理员，false-非管理员
 * @author 心云间、凝听 <981248356@qq.com>
 */
function is_administrator($uid = null) {
    $uid = is_null($uid) ? is_admin_login() : $uid;
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
    return AuthGroupAccessLogic::groupUserUids(1);
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
function get_adminuser_info($uid) {
    if ($uid>0) {
        return AdminUserLogic::info($uid);
    }
    return false;
    
}
