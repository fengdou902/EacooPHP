<?php
// 授权组逻辑层      
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\logic;

class AuthGroup extends AdminLogic
{
    const TYPE_ADMIN                = 1;                   // 管理员用户组类型标识
    const MEMBER                    = 'users';
    const AUTH_GROUP_ACCESS         = 'auth_group_access'; // 关系表表名
    const AUTH_EXTEND               = 'auth_extend';       // 动态权限扩展信息表
    const AUTH_GROUP                = 'auth_group';        // 用户组表名
    const AUTH_EXTEND_CATEGORY_TYPE = 1;              // 分类权限标识
    const AUTH_EXTEND_MODEL_TYPE    = 2; //分类权限标识
    

    /**
     * 返回用户组列表
     * 默认返回正常状态的管理员用户组列表
     * @param array $where   查询条件,供where()方法使用
     *
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function getGroups($where=array()){
        $map = array('status'=>1);
        $map = array_merge($map,$where);
        return $this->where($map)->select();
    }

    /**
     * 把用户添加到用户组,支持批量添加用户到用户组
     * @author 朱亚杰 <zhuyajie@topthink.net>
     * 
     * 示例: 把uid=1的用户添加到group_id为1,2的组 `AuthGroupModel->addToGroup(1,'1,2');`
     */
    public function addToGroup($uid, $gid){
        try {
            $uid = is_array($uid)? implode(',',$uid) : trim($uid,',');
            $gid = is_array($gid)? $gid:explode( ',',trim($gid,',') );

            $Access = model(self::AUTH_GROUP_ACCESS);
            $del = true;
            if( isset($_REQUEST['batch']) ){
                //为单个用户批量添加用户组时,先删除旧数据
                $del = $Access->where(['uid'=>['in',$uid]])->delete();
            }

            $uid_arr = explode(',',$uid);
            $uid_arr = array_diff($uid_arr,get_administrators());
            $add = [];
            if( $del!==false ){
                foreach ($uid_arr as $u){
                    foreach ($gid as $g){
                        if( is_numeric($u) && is_numeric($g) ){
                            //防止重复添加
                            if (!$Access->where(['group_id'=>$g,'uid'=>$u])->count()) {
                                $add[] = ['group_id'=>$g,'uid'=>$u];
                            }
                            
                        }
                    }
                    // $user_auth_role = db('users')->where(array('uid'=>$u))->value('auth_groups');
                    // if ($user_auth_role) {
                    //     $user_auth_role = explode(',', $user_auth_role);
                    //     $user_auth_role = array_merge($user_auth_role,$gid);
                    // } else{
                    //     $user_auth_role = $gid;
                    // }
                    // db('users')->where(array('uid'=>$u))->update(['auth_groups',implode(',',$user_auth_role)]);//同时将用户角色关联（16/07/06新增）
                    
                }

                if (!empty($add) && is_array($add)) {
                    $Access->saveAll($add);
                } else{
                    throw new \Exception("添加失败，可能有重复添加操作",0);
                    
                }
                
            }
            if ($Access->getError()) {
                if( count($uid_arr)==1 && count($gid)==1 ){
                    //单个添加时定制错误提示
                    throw new \Exception("不能重复添加",0);
                }
                throw new \Exception($Access->getError(),0);
            } 
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
            
        }
        

    }

    /**
     * 返回用户所属用户组信息
     * @param  int    $uid 用户id
     * @return array  用户所属的用户组 array(
     *                                         array('uid'=>'用户id','group_id'=>'用户组id','title'=>'用户组名称','rules'=>'用户组拥有的规则id,多个,号隔开'),
     *                                         ...)   
     */
    static public function getUserGroup($uid){
        static $groups = array();
        if (isset($groups[$uid]))
            return $groups[$uid];
        $prefix   = config('database.prefix');
        $user_groups = model()
            ->field('uid,group_id,title,description,rules')
            ->table($prefix.self::AUTH_GROUP_ACCESS.' a')
            ->join ($prefix.self::AUTH_GROUP." g on a.group_id=g.id")
            ->where("a.uid='$uid' and g.status='1'")
            ->select();
        $groups[$uid]=$user_groups?$user_groups:array();
        return $groups[$uid];
    }
    
    /**
     * 将用户从用户组中移除
     * @param int|string|array $gid   用户组id
     * @param int|string|array $cid   分类id
     * @author 朱亚杰 <xcoolcc@gmail.com>
     */
    public function removeFromGroup($uid,$gid){
        $del_result = model(self::AUTH_GROUP_ACCESS)->where( array( 'uid'=>$uid,'group_id'=>$gid) )->delete();
        // if ($del_result) {
        //     $user_auth_role = db('users')->where(array('uid'=>$uid))->value('auth_groups');
        //     if ($user_auth_role) {
        //         $user_auth_role=array_merge(array_diff(explode(',', $user_auth_role), array($gid)));
        //         model('user')->where(array('uid'=>$uid))->setField('auth_groups',$user_auth_role);//同时将用户角色关联删除
        //     }
            
        // }
        return $del_result;
    }
        /**
     * 获取某个用户组的用户列表
     *
     * @param int $group_id   用户组id
     * 
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    static public function userInGroup($group_id){
        $prefix   = config('database.prefix');
        $l_table  = $prefix.self::MEMBER;
        $r_table  = $prefix.self::AUTH_GROUP_ACCESS;
        $list     = model() ->field('m.uid,u.username,m.last_login_time,m.last_login_ip,m.status')
                       ->table($l_table.' m')
                       ->join($r_table.' a ON m.uid=a.uid')
                       ->where(array('a.group_id'=>$group_id))
                       ->select();
        return $list;
    }

    /**
     * 检查id是否全部存在
     * @param array|string $gid  用户组id列表
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function checkId($modelname,$mid,$msg = '以下id不存在:'){
        if(is_array($mid)){
            $count = count($mid);
            $ids   = implode(',',$mid);
        }else{
            $mid   = explode(',',$mid);
            $count = count($mid);
            $ids   = $mid;
        }

        $s = model($modelname)->where(array('id'=>array('in',$ids)))->column('id');
        if(count($s)===$count){
            return true;
        }else{
            $diff = implode(',',array_diff($mid,$s));
            $this->error = $msg.$diff;
            return false;
        }
    }
        /**
     * 检查用户组是否全部存在
     * @param array|string $gid  用户组id列表
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function checkGroupId($gid){
        return $this->checkId('AuthGroup',$gid, '以下用户组id不存在:');
    }
}
