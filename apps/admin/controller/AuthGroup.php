<?php
// 授权组控制器       
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\model\AuthRule as AuthRuleModel;
use app\admin\model\AuthGroup as AuthGroupModel;
use app\admin\model\AuthGroupAccess as AuthGroupAccessModel;
use app\admin\model\AdminUser as AdminUserModel;

class AuthGroup extends Admin {

    protected $authRuleModel;
    protected $authGroupModel;
    protected $userModel;

    function _initialize()
    {
        parent::_initialize();

        $this->authRuleModel  = new AuthRuleModel();
        $this->authGroupModel = new AuthGroupModel();
        $this->userModel     = new AdminUserModel;

    }

   /**
     * 角色管理
     * @return [type] [description]
     * @date   2018-02-07
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index(){
        // 获取所有角色
        list($data_list,$total) = $this->authGroupModel
                                    ->search() //添加搜索框
                                    ->getListByPage([],true,'id asc');

        $return = builder('List')
                ->setPageTips('角色组是对后台管理员进行权限组划分，可以对角色组进行授权，也可以添加后台用户到角色组')
                ->addTopButton('addnew')  // 添加新增按钮
                ->addTopButton('resume',['model'=>'AuthGroup'])  // 添加启用按钮
                ->addTopButton('forbid',['model'=>'AuthGroup'])  // 添加禁用按钮
                ->addTopButton('delete',['model'=>'AuthGroup'])  // 添加删除按钮
                ->setSearch()
                ->keyListItem('id', 'ID')
                ->keyListItem('title', '角色名')
                ->keyListItem('description', '描述')
                ->keyListItem('status', '状态','status')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListData($data_list)    // 数据列表
                ->setListPage($total) // 数据列表分页
                ->addRightButton('edit',['href'=>url('edit',['group_id'=>'__data_id__']),'class'=>'btn btn-success btn-xs']) 
                ->addRightButton('self',['title'=>'权限分配','href'=>url('access',['group_id'=>'__data_id__']),'class'=>'btn btn-info btn-xs'])  
                ->addRightButton('self',array('title'=>'成员授权','href'=>url('accessUser',array('group_id'=>'__data_id__'))))    
                ->fetch();

        return Iframe()
                ->setMetaTitle('角色管理') // 设置页面标题
                ->content($return);
    }
    
    //角色编辑
    public function edit($group_id=0){
        $title = $group_id ? '编辑':'新增';
    
         $info =$this->authGroupModel->find($group_id);
         if (IS_POST) {
            $data = $this->request->param();
            $this->validateData($data,  
                                [
                                    ['title','require|chsAlpha','用户组名称不能为空|用户组名称只能是汉字和字母'],
                                    ['description','chsAlphaNum','描述只能是汉字字母数字']
                                ]
                            );
            //$data里包含主键，则editData就会更新数据，否则是新增数据
            if ($this->authGroupModel->editData($data)) {
                $this->success($title.'成功', url('index'));
            } else {
                $this->error($this->authGroupModel->getError());
            }

        } else {
            if ($group_id!=0) {
                $this->assign('group_id',$group_id);
            }
            $this->assign('meta_title',$title.'角色');
            $this->assign('info',$info);
            return $this->fetch();
        }
    }

    /**
     * 权限分配
     * @param  integer $group_id 组ID
     * @return [type] [description]
     * @date   2017-08-27
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function access($group_id=0){
        
        $title='权限分配'; 
        if (IS_POST && $group_id>0) {
            $data['id']    = $group_id;
            $menu_auth     = input('param.menu_auth/a','');//获取所有授权菜单
            $data['rules'] = implode(',',$menu_auth);

            //开发过程中先关闭这个限制
            //if($group_id==1){
                //$this->error('不能修改超级管理员'.$title);
           // }else{
                //$data里包含主键id，则editData就会更新数据，否则是新增数据
                if ($this->authGroupModel->editData($data)) {
                    cache('admin_sidebar_menus_'.$this->currentUser['uid'],null);
                    $this->success($title.'成功', url('index'));
                }else{
                    $this->error($this->authGroupModel->getError());
                }
                
            //}

        } else{
            if ($group_id>0) {
                $this->assign('group_id',$group_id);
            }
            $this->assign('meta_title',$title);
            $role_auth_rule = $this->authGroupModel->where(['id'=>intval($group_id)])->value('rules');
            $this->assign('menu_auth_rules',explode(',',$role_auth_rule));//获取指定获取到的权限
            $rule = db('auth_rule')->select();
            $tree_obj = new \eacoo\Tree;
            $rule = $tree_obj->listToTree($rule);
            $this->assign('auth_rules_list',$rule);//所以规则
            return $this->fetch();
        }
    
    }

    /**
     * 用户组授权用户列表
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function accessUser($group_id=0)
    {
        if ($group_id!=0) {
            $this->assign('group_id',$group_id);
        }

        $auth_group = $this->authGroupModel->where(['status'=>['egt','0']])->field('id,title,rules')->select();
        foreach ($auth_group as $key => $row) {
            $authGroup[$row['id']]=$row;
        }
        //$list = $this->lists($model,array('a.group_id'=>$group_id,'m.status'=>array('egt',0)),'m.uid asc',null,'m.uid,m.nickname,m.last_login_time,m.last_login_ip,m.status');
        $list= $this->userModel->alias('m')->join ('__AUTH_GROUP_ACCESS__ a','m.uid=a.uid' )->where(['a.group_id'=>$group_id,'m.status'=>['egt',0]])->order('m.uid asc')->field('m.uid,m.nickname,m.last_login_time,m.last_login_ip,m.status')->paginate(20);

        $this->assign( '_list',     $list );
        $this->assign( 'page',     $list->render());
        $this->assign('auth_group', $authGroup);
        $this->assign('this_group', $authGroup[(int)$group_id]);
        $this->assign('meta_title','成员授权');
        return $this->fetch();
    }

    /**
     * 将用户添加到用户组,入参uid,group_id
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function addToGroup(){
        try {
            $uids = input('uids',false);//新增批量用户
            if ($uids) {
                $uid = explode(',',$uids);
            }else{
                $uid = input('uid');
            }
            
            $gid = input('param.group_id');
            if( empty($uid) ){
                throw new \Exception("参数有误", 0);
                
            }
            if(is_numeric($uid)){
                if ( is_administrator($uid) ) {
                    throw new \Exception("该用户为超级管理员", 0);
                }
                if( !$this->userModel->where(['uid'=>$uid])->find() ){
                    throw new \Exception("用户不存在", 0);
                }
            }

            if( $gid && !$this->authGroupModel->checkGroupId($gid)){
                $this->error($this->authGroupModel->error);
            }
            if ( !logic('AuthGroup')->addToGroup($uid,$gid) ){
                $this->error($this->authGroupModel->getError());
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('操作成功');
        
    }

    /**
     * 将用户从用户组中移除  入参:uid,group_id
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function removeFromGroup(){
        $uid = input('param.uid');
        $gid = input('param.group_id');
        if( $uid==is_admin_login() ){
            $this->error('不允许解除自身授权');
        }
        if( empty($uid) || empty($gid) ){
            $this->error('参数有误');
        }
        if( !$this->authGroupModel->find($gid)){
            $this->error('用户组不存在');
        }
        if ( logic('AuthGroup')->removeFromGroup($uid,$gid) ){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }
}