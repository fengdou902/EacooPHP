<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.eacoomall.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\wechat\admin;
use app\admin\controller\Admin;

use app\wechat\model\Wechat as WechatModel;

use app\admin\builder\Builder;

class Wechat extends Admin {

    protected $wechatModel;
    protected $categoryModel;

    public function _initialize()
    {
        parent::_initialize();
        $this->wechatModel = new WechatModel();
        $this->weixinType = $this->wechatModel->weixinType();
    }
    
    //公众号管理
    public function index(){
        $map['status'] =['egt', '0']; // 禁用和正常状态
        list($data_list,$page) = $this->wechatModel->getListByPage($map,'create_time desc','*',20);

        Builder::run('List')
            ->setMetaTitle('公众号列表') // 设置页面标题
            ->addTopBtn('addnew')  // 添加新增按钮
            ->addTopBtn('resume')  // 添加启用按钮
            ->addTopBtn('forbid')  // 添加禁用按钮
            ->addTopBtn('delete') //添加删除按钮
            ->keyListItem('name', '公众号名称')
            ->keyListItem('type', '类型','array',$this->weixinType)
            ->keyListItem('create_time','创建时间')
            ->keyListItem('status', '状态', 'status')
            ->keyListItem('right_button', '操作', 'btn')
            ->setListData($data_list)    // 数据列表
            ->setListPage($page) // 数据列表分页
            ->addRightButton('self',['title'=>'查看','class'=>'btn btn-info btn-xs','href'=>url('detail',['id'=>'__data_id__'])])->addRightButton('edit')->addRightButton('delete')
            ->fetch();
    }

    //编辑公众号
    public function edit($id=0){
        $title = $id>0 ? "编辑":"添加";
        //修改
        if(IS_POST){
            $data =$this->request->param();
            $id = isset($data['id']) && $data['id']>0 ? $data['id']:false;
            if($this->wechatModel->editData($data,$id)){
                $this ->success($title.'成功',url('index'));
            } else{
                $this ->error($this->wechatModel->getError());
            }

        } else{

            $info = [];
            if ($id>0) $info = WechatModel::get($id);

            Builder::run('Form')
                    ->setMetaTitle($title.'公众号') // 设置页面标题
                    ->addFormItem('id', 'hidden', 'ID', 'ID')
                    ->addFormItem('name', 'text', '公众号名称', '','','required','placeholder="填写公众号名称"')
                    ->addFormItem('type', 'radio', '公众号类型', '',$this->weixinType,'required')         
                    ->addFormItem('origin_id', 'text', '原始ID', '')
                    ->addFormItem('weixin_number', 'text', '微信号', '')
                    ->addFormItem('appid', 'text', 'APPID', '')
                    ->addFormItem('appsecret', 'password', 'APPSECRET', '')
                    ->addFormItem('mch_id', 'text', '商户号', '')
                    ->addFormItem('mch_key', 'password', '商户密钥', '')
                    ->addFormItem('headimg', 'picture', '头像', '')
                    ->addFormItem('qrcode', 'picture', '二维码', '')
                    ->addFormItem('status', 'radio', '状态', '',[0=>'禁用',1=>'正常',2=>'审核中'],'required')
                    ->setFormData($info)
                    ->addButton('submit')
                    ->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
        
    }
    
    /**
     * 显示公众号详情
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function detail($id=0){
        if ($id!=0) $weixin = WechatModel::get($id);

        $this->assign('meta_title',$weixin['name']."详情");
        $weixin['url'] = $this->request->domain().'/WxInterface/'.$weixin['id'].'.html';
        $this->assign('weixin',$weixin);
        return $this->fetch();
    }

}