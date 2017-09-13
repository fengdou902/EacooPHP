<?php
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

/**
 * 更新用户角色信息
 * @param int $uid
 * @return int
 * @author 赵俊峰<981248356@qq.com>
 */
function set_user_role($uid=0,$role=0)
{   
    if ($uid==0||$role==0) return false;
    model('users')->where(array('uid'=>$uid))->setField('role_id',$role);
    model('admin/AuthGroup')->addToGroup($uid,$role);//添加授权组
    return true;
}

/*获取角色类型
*$unids 排除的角色id
*/
function role_type($unids=false){
    $role_type = [];
    $role_map['status']=array('egt','0');
    if($unids) $role_map['id']=array('not in',$unids);
    $auth_role= model('AuthGroup')->where($role_map)->field('id,title')->select();
    foreach ($auth_role as $key => $role) {
        $role_type[$role['id']]=$role['title'];
    }
    return $role_type;
}