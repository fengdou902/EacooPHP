<?php
// 用户管理控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use app\common\layout\Iframe;
use app\admin\model\AdminUser as AdminUserModel;

class AdminUser extends Admin {
    protected $adminUserModel;
    protected $groupIds;

    function _initialize()
    {
        parent::_initialize();

        $this->adminUserModel = model('AdminUser');
        $this->groupIds = model('admin/AuthGroup')->where('status',1)->column('title','id');
    }

    /**
     * 管理员列表
     * @return [type] [description]
     * @date   2018-02-05
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index(){

        return (new Iframe())
                ->setMetaTitle('管理员列表')
                ->search([
                    ['name'=>'status','type'=>'select','title'=>'状态','options'=>[1=>'正常',2=>'待审核']],
                    ['name'=>'sex','type'=>'select','title'=>'性别','options'=>[0=>'未知',1=>'男',2=>'女']],
                    ['name'=>'group_id','type'=>'select','title'=>'角色组','options'=>$this->groupIds],
                    ['name'=>'create_time_range','type'=>'daterange','extra_attr'=>'placeholder="注册时间"'],
                    ['name'=>'keyword','type'=>'text','extra_attr'=>'placeholder="请输入查询关键字"'],
                ])
                ->content($this->grid());
    }

    /**
     * Make a grid builder.
     * @return [type] [description]
     * @date   2018-09-08
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function grid()
    {
        $search_setting = $this->buildModelSearchSetting();
        // 获取所有用户
        $condition['status'] = ['egt', '0']; // 禁用和正常状态
        list($data_list,$total) = $this->adminUserModel->search($search_setting)->getListByPage($condition,true,'create_time desc');
        foreach ($data_list as $key => &$data) {
            $auth_groups = model('admin/auth_group_access')->userGroupInfo($data['uid']);
            $auth_groups_label = '';
            foreach ($auth_groups as $gkey => $val) {
                $auth_groups_label.='<label class="label label-info">'.$val.'</label>';
            }
            $data['auth_groups'] = $auth_groups_label;
        }
        $reset_password = [
            'icon'         => 'fa fa-recycle',
            'title'        => '重置原始密码',
            'class'        => 'btn btn-default ajax-table-btn confirm btn-sm',
            'confirm-info' => '该操作会重置用户密码为123456，请谨慎操作',
            'href'         => url('resetPassword')
        ];

        return builder('list')
                ->setMetaTitle('用户列表') // 设置页面标题
                ->addTopButton('addnew')  // 添加新增按钮
                ->addTopButton('resume')  // 添加启用按钮
                ->addTopButton('forbid')  // 添加禁用按钮
                ->addTopButton('delete')  // 添加删除按钮
                ->addTopButton('self',$reset_password)  // 添加重置按钮
                //->setSearch('custom','请输入ID/用户名/昵称')
                ->setActionUrl(url('grid')) //设置请求地址
                ->keyListItem('uid', 'UID')
                ->keyListItem('avatar', '头像', 'avatar')
                ->keyListItem('nickname', '昵称')
                ->keyListItem('auth_groups', '角色组')
                ->keyListItem('sex', '性别')
                ->keyListItem('username', '用户名')
                ->keyListItem('email', '邮箱')
                ->keyListItem('mobile', '手机号')
                ->keyListItem('bind_uid', '绑定会员UID')
                ->keyListItem('create_time', '创建时间')
                ->keyListItem('status_text', '状态')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListPrimaryKey('uid')
                ->setListData($data_list)    // 数据列表
                ->setListPage($total) // 数据列表分页
                ->addRightButton('edit')
                ->addRightButton('forbid')
                //->addRightButton('forbid')  // 添加编辑按钮
                ->fetch();
    }

    /**
     * 编辑用户
     */
    public function edit($uid = 0) {
        $title = $uid ? "编辑" : "新增";
        if (IS_POST) {
            $data = input('param.');
            
            $uid  = isset($data['uid']) && $data['uid']>0 ? intval($data['uid']) : false;
            if ($uid>0) {
                // 密码为空表示不修改密码
                if ($data['password'] === '') {
                    unset($data['password']);
                }
                $this->validateData($data,'User.edit');
            } else{
                // 密码为空表示不修改密码
                if ($data['password'] === '') {
                    $data['password']=123456;
                }
                $this->validateData($data,'User.add');
            }
            
            if (!empty($data['password'])) {
                $data['password'] = encrypt($data['password']);
            }
            // 提交数据
            //$data里包含主键id，则editData就会更新数据，否则是新增数据
            $result = $this->adminUserModel->editData($data);

            if ($result) {
                
                if ($uid==is_admin_login()) {//如果是编辑状态下
                    logic('admin/AdminUser')->updateLoginSession($uid);
                } else{
                    $uid = $this->adminUserModel->uid;
                }
                $gid = $data['group_id'];
                // 修改分组前删除当前所属分组
                model('auth_group_access')->where(['uid'=>$uid])->delete();
                //将用户添加到用户组
                logic('admin/AuthGroup')->addToGroup($uid,$gid);
                $this->success($title.'成功', url('index'));
            } else {
                $this->error($this->adminUserModel->getError());
            }
        } else {
            //设置默认值
            $info = [
                'sex'=>0,
                'bind_uid'=>0,
                'group_id'=>3,
                'sex'=>0,
                'status'=>1
            ];
            // 获取账号信息
            if ($uid>0) {
                $info = $this->adminUserModel->get($uid);
                //查询该用户当前拥有的分组
                $group_ids=model('auth_group_access')->where(['uid'=>$uid])->column('group_id');
                $info['group_id']=$group_ids;
                unset($info['password']);
            }
            $builder = builder('Form')
                        ->addFormItem('uid', 'hidden', 'UID', '')
                        ->addFormItem('nickname', 'text', '昵称', '填写一个有个性的昵称吧','','require')
                        ->addFormItem('username', 'text', '用户名', '登录账户所用名称','','require')
                        ->addFormItem('password', 'password', '密码', '新增默认密码123456','','placeholder="留空则不修改密码"')
                        ->addFormItem('email', 'email', '邮箱', '','','data-rule="email" data-tip="请填写一个邮箱地址"')
                        ->addFormItem('mobile', 'left_icon_number', '手机号', '',['icon'=>'<i class="fa fa-phone"></i>'],'placeholder="填写手机号"')
                        ->addFormItem('sex', 'radio', '性别', '',[0=>'保密',1=>'男',2=>'女'])
                        ->addFormItem('group_id', 'checkbox', '所属用户组', '',$this->groupIds)
                        ->addFormItem('bind_uid', 'number', '绑定会员UID', '绑定用户表的UID')
                        ->addFormItem('description', 'textarea', '个人说明', '请填写个人说明')
                        ->addFormItem('status', 'select', '状态', '',[0=>'禁用',1=>'正常',2=>'待验证'])
                        ->setFormData($info)//->setAjaxSubmit(false)
                        ->addButton('submit')
                        ->addButton('back')    // 设置表单按钮
                        ->fetch();

            return (new Iframe())
                    ->setMetaTitle($title.'用户')
                    ->content($builder);

        }
    }

    /**
     * 构建模型搜索查询条件
     * @return [type] [description]
     * @date   2018-09-30
     * @author 心云间、凝听 <981248356@qq.com>
     */
    private function buildModelSearchSetting()
    {
        $params = $this->request->param();
        //时间范围
        
        $extend_conditions = [];
        if(!empty($params['create_time_range'])){
            $timegap = $params['create_time_range'];
            $gap = explode('—', $timegap);
            $reg_begin = $gap[0];
            $reg_end = $gap[1];

            $extend_conditions =[
                'create_time'=>['between',[$reg_begin.' 00:00:00',$reg_end.' 23:59:59']]
            ];
        }
        //过滤用户组
        if (!empty($params['group_id'])) {
            $uids = model('admin/auth_group_access')->where('group_id',$params['group_id'])->column('uid');
            $extend_conditions['uid']=['in',$uids];
        }
        //自定义查询条件
        $search_setting = [
            'keyword_condition'=>'uid|username|nickname|email',
            //忽略数据库不存在的字段
            'ignore_keys' => ['create_time_range','group_id'],
            //扩展的查询条件
            'extend_conditions'=>$extend_conditions
        ];

        return $search_setting;
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

            $data = $this->request->param();
            // 提交数据
            $result = $this->adminUserModel->editData($data);
            if ($result) {
                $uid = $data['uid'];
                if ($uid==is_admin_login()) {//如果是编辑状态下
                    logic('AdminUser')->updateLoginSession($uid);
                }

                $this->success('提交成功', url('profile',['uid'=>$uid]));
            } else {
                $msg = $this->adminUserModel->getError();
                if (!$msg) {
                    $msg = '操作失败';
                }
                $this->error($msg);
            }
        } else {
            $this->assign('meta_title','个人资料');
            $this->assign('page_config',['disable_panel'=>true]);
            // 获取账号信息
            if ($uid>0) {
                $user_info = get_adminuser_info($uid);
                unset($user_info['password']);
                //unset($user_info['auth_group']['max']);
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
            $res = AdminUserModel::where($map)->setField('password',$new_password);
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
            $info = $this->adminUserModel->get(is_admin_login());

            $content = builder('form')
                    ->addFormItem('uid', 'hidden', 'UID', '')
                    //->addFormItem('oldpassword', 'password', '原密码', '','','','placeholder="填写旧密码"')
                    ->addFormItem('newpassword', 'password', '新密码', '','','placeholder="填写新密码"')
                    ->addFormItem('repassword', 'password', '重复密码', '','','placeholder="填写重复密码"')
                    ->setFormData($info)
                    //->setAjaxSubmit(false)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();

            return (new Iframe())
                    ->setMetaTitle('重置密码') // 设置页面标题
                    ->content($content);
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
