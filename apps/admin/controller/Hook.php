<?php
// 钩子后台管理控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\Plugins as PluginsModel;
use app\admin\model\Hooks as HooksModel;

class Hook extends Admin {

    protected $pluginModel;
    protected $hooksModel;

    function _initialize()
    {
        parent::_initialize();
        
        $this->pluginModel = new PluginsModel();
        $this->hooksModel  = new HooksModel();
    }

    /**
     * 钩子列表
     */
    public function index(){
        $this->assign('page_config',['back'=>true]);

        list($data_list,$total) = $this->hooksModel->search('name|description')->getListByPage([],true,'create_time desc',20);
        $return = builder('List')
                ->addTopButton('addnew',array('href'=>url('edit'),'title'=>'新增钩子','class'=>'btn bg-purple btn-sm margin'))    // 添加新增按钮
                ->keyListItem('id', 'ID')
                ->keyListItem('name', '名称')
                ->keyListItem('description', '描述')
                ->keyListItem('type', '类型', 'array', config('hooks_type'))
                ->keyListItem('right_button', '操作', 'btn')
                ->setListData($data_list)     // 数据列表
                ->setListPage($total,20)  // 数据列表分页
                ->addRightButton('edit')           // 添加编辑按钮
                ->addRightButton('delete',['href'=>url('del',['id'=>'__data_id__']),'model'=>'Hooks'])  // 添加删除按钮
                ->fetch();

        return Iframe()
                ->setMetaTitle('钩子列表')  // 设置页面标题
                ->setPageTips('钩子是基于行为实现，通过监听行为，可以对钩子挂的功能进行触发调用。')
                ->content($return);
    }

    /**
     * 钩子出编辑挂载插件页面
     * @param  integer $id [description]
     * @return [type] [description]
     * @date   2017-09-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function edit($id=0){
        $title=$id ? "编辑" : "新增";
        if (IS_POST) {
            $data = input('param.');
            //验证数据
            $this->validateData($data,'Hook.edit');

            //验证插件名是否存在
            /*$valiname = $this->hooksModel->where('name',$data['name'])->find();
            if($data['id'] != $valiname['id']){
                $this->error('插件名已存在');
            }*/
            //$data里包含主键id，则editData就会更新数据，否则是新增数据
            if ($this->hooksModel->editData($data)) {
                $this->success($title.'成功', url('index'));
            } else {
                $this->error($this->hooksModel->getError());
            }
            
        } else {
            $info = [];
            if ($id!=0) {
                $info = HooksModel::get($id);
            }
            $builder = builder('Form')
                        ->setPageTips('钩子是基于行为实现，通过监听行为，可以对钩子挂的功能进行触发调用。');
            if ($id>0) {
                $builder->addFormItem('id', 'hidden', 'ID', '');
            }
            $return = $builder->addFormItem('name', 'text', '名称', '需要在程序中先添加钩子，否则无效')
                    ->addFormItem('description', 'textarea', '描述', '钩子的描述信息')
                    ->addFormItem('type', 'radio', '类型', '钩子类型',config('hooks_type'))
                    ->setFormData($info)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();

            return Iframe()
                    ->setMetaTitle($title.'钩子') // 设置页面标题
                    ->content($return);
        }
    }
    
    /**
     * 检测钩子是否存在
     * @param  [type] $name [description]
     * @param  [type] $data [description]
     * @return [type] [description]
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function existHook($name, $data){
        return $this->hooksModel->existHook($name, $data);
    }
    
    /**
     * 超级管理员删除钩子
     * @param  [type] $id [description]
     * @return [type] [description]
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function del($id){
        if(HooksModel::destroy($id)){
            $this->success('删除成功');
        } else{
            $this->error('删除失败');
        }
    }


}
