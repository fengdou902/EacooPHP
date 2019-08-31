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
namespace app\user\admin;
use app\admin\controller\Admin;
use app\common\layout\Iframe;
use app\user\model\UserLevel as UserLevelModel;

class UserLevel extends Admin {

    function _initialize()
    {
        parent::_initialize();

        $this->userLevelModel = new UserLevelModel;
    }

    /**
     * 用户列表
     * @return [type] [description]
     * @date   2018-02-05
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index(){

        return (new Iframe())
                ->setMetaTitle('用户头衔')
                ->search([
                    ['name'=>'status','type'=>'select','title'=>'状态','options'=>[1=>'正常',0=>'禁用']],
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
        list($data_list,$total) = $this->userLevelModel->search($search_setting)->getListByPage($condition,true,'id desc');

        $reset_password = [
            'icon'         => 'fa fa-recycle',
            'title'        => '重置原始密码',
            'class'        => 'btn btn-default ajax-table-btn confirm btn-sm',
            'confirm-info' => '该操作会重置用户密码为123456，请谨慎操作',
            'href'         => url('resetPassword')
        ];

        return builder('list')
                ->setMetaTitle('用户头衔') // 设置页面标题
                ->addTopButton('addnew')  // 添加新增按钮
                ->addTopButton('resume')  // 添加启用按钮
                ->addTopButton('forbid')  // 添加禁用按钮
                ->addTopButton('delete')  // 添加删除按钮
                //->setSearch('custom','请输入ID/用户名/昵称')
                ->setActionUrl(url('grid')) //设置请求地址
                ->keyListItem('id', 'ID')
                ->keyListItem('title', '标题')
                ->keyListItem('description', '描述')
                ->keyListItem('status_text', '状态','status')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListData($data_list)    // 数据列表
                ->setListPage($total) // 数据列表分页
                ->addRightButton('edit')//->addRightButton('forbid')
                //->addRightButton('forbid')  // 添加编辑按钮
                ->fetch();
    }

    /**
     * 编辑用户
     */
    public function edit($uid = 0) {
        $title = $uid ? "编辑" : "新增";
        if (IS_POST) {
            $data = $this->request->param();
            
            // 提交数据
            //$data里包含主键id，则editData就会更新数据，否则是新增数据
            $result = $this->userLevelModel->editData($data);

            if ($result) {
                
                $this->success($title.'成功', url('index'));
            } else {
                $this->error($this->userLevelModel->getError());
            }
        } else {

            return (new Iframe())
                    ->setMetaTitle($title.'用户')
                    ->content($this->form($uid));

        }
    }

    /**
     * 表单构建
     * @param  integer $uid [description]
     * @return [type] [description]
     * @date   2018-10-03
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function form($id = 0)
    {
        $info = [
            'status'  =>1
        ];
        // 获取账号信息
        if ($id>0) {
            $info = $this->userLevelModel->get($id);
            unset($info['password']);
        }
        return builder('Form')
                    ->addFormItem('id', 'hidden', 'ID', '')
                    ->addFormItem('title', 'text', '标题', '填写一个标题','','require')
                    ->addFormItem('description', 'textarea', '个人说明', '请填写个人说明')
                    ->addFormItem('status', 'radio', '状态', '',[1=>'正常',0=>'禁用'])
                    ->setFormData($info)//->setAjaxSubmit(false)
                    ->addButton('submit')
                    ->addButton('back')    // 设置表单按钮
                    ->fetch();
    }
    
    /**
     * 构建模型搜索查询条件
     * @return [type] [description]
     * @date   2018-09-30
     * @author 心云间、凝听 <981248356@qq.com>
     */
    private function buildModelSearchSetting()
    {
        //自定义查询条件
        $search_setting = [
            'keyword_condition'=>'title',
        ];

        return $search_setting;
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
        if (!empty($ids)) {
            foreach ($ids as $key => $uid) {
                //清理缓存
                cache('User_info_'.$uid, null);
            }
        }
        parent::setStatus($model);
    }

}