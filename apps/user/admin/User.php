<?php
// 用户管理控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\user\admin;
use app\admin\controller\Admin;
use app\common\model\User as UserModel;

class User extends Admin {

    function _initialize()
    {
        parent::_initialize();

        $this->userModel = model('common/User');
    }

    /**
     * 用户列表
     * @return [type] [description]
     * @date   2018-02-05
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index(){
        // 获取所有用户
        $map['status'] = ['egt', '0']; // 禁用和正常状态
        list($data_list,$total) = $this->userModel->search('uid|username|nickname')->getListByPage($map,true,'reg_time desc');

        $reset_password = [
            'icon'=> 'fa fa-recycle',
            'title'=>'重置原始密码',
            'class'=>'btn btn-default ajax-table-btn confirm btn-sm',
            'confirm-info'=>'该操作会重置用户密码为123456，请谨慎操作',
            'href'=>url('resetPassword')
        ];

        return builder('list')
                ->setMetaTitle('用户列表') // 设置页面标题
                ->addTopButton('addnew')  // 添加新增按钮
                ->addTopButton('resume')  // 添加启用按钮
                ->addTopButton('forbid')  // 添加禁用按钮
                ->addTopButton('delete')  // 添加删除按钮
                ->addTopButton('self',$reset_password)  // 添加重置按钮
                ->setSearch('basic','请输入ID/用户名/昵称')
                ->keyListItem('uid', 'UID')
                ->keyListItem('avatar', '头像', 'avatar')
                ->keyListItem('nickname', '昵称')
                ->keyListItem('username', '用户名')
                ->keyListItem('email', '邮箱')
                ->keyListItem('mobile', '手机号')
                ->keyListItem('reg_time', '注册时间','time')
                ->keyListItem('allow_admin', '允许进入后台','status')
                ->keyListItem('status', '状态', 'array',[0=>'禁用',1=>'正常',2=>'待验证'])
                ->keyListItem('right_button', '操作', 'btn')
                ->setListPrimaryKey('uid')
                ->setListData($data_list)    // 数据列表
                ->setListPage($total) // 数据列表分页
                ->addRightButton('edit')//->addRightButton('forbid')
                ->addRightButton('delete')  // 添加编辑按钮
                ->fetch();
    }

    /**
     * 编辑用户
     */
    public function edit($uid = 0) {
        $title = $uid ? "编辑" : "新增";
        if (IS_POST) {
            $data = input('param.');
            // 密码为空表示不修改密码
            if ($data['password'] === '') {
                unset($data['password']);
            }

            $this->validate($data,'User.edit');

            $uid  = isset($data['uid']) && $data['uid']>0 ? intval($data['uid']) : false;
            if (!$uid) {
                $data['reg_time'] = time();
            }
            // 提交数据
            $result = $this->userModel->editData($data,$uid,'uid');

            if ($result) {
                if ($uid==is_login()) {//如果是编辑状态下
                    logic('common/User')->updateLoginSession($uid);
                }
                $this->success($title.'成功', url('index'));
            } else {
                $this->error($this->userModel->getError());
            }
            
        } else {
            $info=['sex'=>0,'allow_admin'=>1,'sex'=>0,'status'=>1];
            // 获取账号信息
            if ($uid!=0) {
                $info = $this->userModel->get($uid);
                unset($info['password']);
            }

            $builder = builder('Form');
            $builder->setMetaTitle($title.'用户')  // 设置页面标题
                    ->addFormItem('uid', 'hidden', 'UID', '')
                    ->addFormItem('nickname', 'text', '昵称', '填写一个有个性的昵称吧','','require')
                    ->addFormItem('username', 'text', '用户名', '登录账户所用名称','','require')
                    ->addFormItem('password', 'password', '密码', '','','','placeholder="留空则不修改密码"')
                    ->addFormItem('email', 'email', '邮箱', '','','data-rule="email" data-tip="请填写一个邮箱地址"')
                    ->addFormItem('mobile', 'left_icon_number', '手机号', '',['icon'=>'<i class="fa fa-phone"></i>'],'','placeholder="填写手机号"')
                    ->addFormItem('sex', 'radio', '性别', '',[0=>'保密',1=>'男',2=>'女'])
                    ->addFormItem('allow_admin', 'select', '是否允许访问后台', '',[0=>'不允许',1=>'允许'])
                    ->addFormItem('description', 'textarea', '个人说明', '请填写个人说明');
            if ($uid>0) {
                $builder->addFormItem('avatar', 'avatar', '头像', '用户头像默认随机分配',['uid'=>$info['uid']],'require');
            }
            return $builder
                    ->addFormItem('status', 'select', '状态', '',[0=>'禁用',1=>'正常',2=>'待验证'])
                    ->setFormData($info)//->setAjaxSubmit(false)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
    }
    
    /**
     * 个人资料
     * @param  integer $uid [description]
     * @return [type] [description]
     * @date   2017-12-28
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function profile($uid = 0) {
        
        if (IS_POST) {
            // 密码为空表示不修改密码
            // if ($_POST['password'] === '') {
            //     unset($_POST['password']);
            // }
            $data = $this->request->param();
            
            // 提交数据
            $result = $this->userModel->editData($data,$uid,'uid');
            if ($result) {
                if ($uid==is_login()) {//如果是编辑状态下
                    $this->userModel->updateLoginSession($uid);
                }

                $this->success('提交成功', url('profile',['uid'=>$uid]));
            } else {

                $this->error($this->userModel->getError());
            }
        } else {
            $this->assign('meta_title','个人资料');
            $this->assign('page_config',['disable_panel'=>true]);
            // 获取账号信息
            if ($uid>0) {
                $user_info = get_user_info($uid);
                unset($user_info['password']);
                unset($user_info['auth_group']['max']);
            }
            $this->assign('user_info',$user_info);
            return $this->fetch();
        }
    }

    /**
     * 个人资料修改密码
     * @return [type] [description]
     * @date   2018-02-19
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function resetPassword(){
        if (IS_POST) {
            $params = $this->request->param();
            $result = $this->validate($params,[
                ['uid','number|>=:1','用户ID格式不正确|用户ID格式不正确'],
                ['newpassword','min:6','重置密码长度不能少于6位'],
                ['repassword','min:6|confirm:newpassword','重复密码不正确|重复密码不一致'],
            ]);
            if(true !== $result){
                // 验证失败 输出错误信息
                $this->error($result);
            }
            if (!isset($params['ids']) && !isset($params['uid'])) {
                $this->error('操作用户不存在');
            }
            $map = [];
            if (isset($params['ids'])) {
                $map['uid'] = ['in',$params['ids']];
                $newpassword = 123456;
            } elseif (isset($params['uid'])) {
                if (!isset($params['newpassword']) || !isset($params['repassword']) ||!$params['newpassword']) {
                    $this->error('请填写一个合适的密码');
                }
                $map['uid'] = $params['uid'];
                $newpassword = $params['newpassword'];
                $repassword  = $params['repassword'];
            }
            //$oldpassword=input('param.oldpassword',false);
            $new_password = encrypt($newpassword);
            $res = UserModel::where($map)->setField('password',$new_password);
            if ($res) {
                if (isset($params['uid']) && $params['uid']==is_login()) {
                    session(null);
                    $this->success('已重置密码成功，新密码：'.$newpassword, url('admin/login/index'));
                } else{
                    $this->success('已重置密码成功，新密码：'.$newpassword);
                }
                
            } else{
                $this->error('密码重置失败');
            }
        } else {
            // 获取账号信息
            $info = $this->userModel->get(is_login());

            return builder('form')
                    ->setMetaTitle('重置密码') // 设置页面标题
                    ->addFormItem('uid', 'hidden', 'UID', '')
                    //->addFormItem('oldpassword', 'password', '原密码', '','','','placeholder="填写旧密码"')
                    ->addFormItem('newpassword', 'password', '新密码', '','','placeholder="填写新密码"')
                    ->addFormItem('repassword', 'password', '重复密码', '','','placeholder="填写重复密码"')
                    ->setFormData($info)
                    //->setAjaxSubmit(false)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
    }

    /**
     * 设置用户的状态
     */
    public function setStatus($model = CONTROLLER_NAME,$script=false){
        $ids = input('param.ids/a');
        if (is_array($ids)) {
            if(in_array('1', $ids)) {
                $this->error('超级管理员不允许操作');
            }
        }else{
            if($ids === '1') {
                $this->error('超级管理员不允许操作');
            }
        }
        parent::setStatus($model);
    }

}