<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------

/**
 * 获取当前用户登录的角色的标识
 * @return int 角色id
  * @return status 用户角色审核状态  1：通过，2：待审核，0：审核失败
 */
function get_login_role($field='id')
{
    $user = session('user_login_auth');
    if (empty($user)) {
        return 0;
    } else {
        if ($field=='id') {
            return session('activation_auth_sign') == data_auth_sign($user) ? $user['auth_group'] : 0;
        } else{
            $role_info = [];
            if (session('activation_auth_sign') == data_auth_sign($user)) {
                if (!empty($user['auth_group'])) {
                    foreach ($user['auth_group'] as $key => $val) {
                        $role_info[] = db('AuthGroup')->where(['id'=>$key])->field($field)->find();
                    }
                }
                
                
            }
            
        }
        
    }
    return $role_info;
}

/**
 * 根据用户uid获取角色信息
 * @param int $uid
 * @return int
 * @author 郑钟良<zzl@ourstu.com>
 */
function get_role_info($uid=0,$field=true)
{
    !$uid && $uid = is_login();
    if($uid == is_login()){//自身
        $role_info = get_login_role($field);
    } else{//不是当前登录者
        $auth_role= db('AuthGroupAccess')->where(['uid'=>$uid])->value('group_id');//获取角色ID

        if ($field!='group_id') {
            $role_info[] = db('AuthGroup')->where(['id'=>$auth_role])->field($field)->find();
        } else{
            $role_info[] = $auth_role;
        }
        
    }
    return $role_info;
}

