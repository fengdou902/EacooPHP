<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.eacoomall.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\wechat\admin;

use app\wechat\model\WechatUser as WechatUserModel;
use app\admin\builder\Builder;

class WechatUser extends Base {
    protected $wechatUserModel;

    function _initialize()
    {
        parent::_initialize();
        $this->wechatUserModel = new WechatUserModel();
    }

    //微信用户列表
    public function index(){

        list($data_list,$page) = $this->wechatUserModel->getListByPage([],'subscribe_time desc','*',20);
        $builder = new AdminListBuilder();

        $builder->setMetaTitle('微信用户列表') // 设置页面标题
            ->addTopBtn('self',['title'=>'一键同步微信公众号粉丝','href'=>url('material_to_wechat',['type'=>'image']),'class'=>'btn btn-info btn-sm'])  // 添加素材库按钮
            ->addTopBtn('delete',['title'=>'<i class="fa fa-trash"></i> 删除']) //添加删除按钮
            ->keyListItem('headimgurl', '头像','avatar')
            ->keyListItem('nickname','昵称')
            ->keyListItem('openid1', 'OPENID')
            ->keyListItem('uid', 'UID')
            ->keyListItem('subscribe','是否关注', 'status')
            //->keyListItem('subscribe_time','关注时间', 'time')
            ->keyListItem('sex', '性别', 'array',[0=>'保密',1=>'男',2=>'女'])
            ->keyListItem('city', '城市')
            ->keyListItem('country', '国家')
            ->keyListItem('province', '省份')
            //->keyListItem('status', '状态', 'status')
            ->setListData($data_list)    // 数据列表
            ->setListPage($page) // 数据列表分页
            ->fetch();
    }

    //编辑公众号
    public function edit($id=0){
        $title=$id?"编辑":"添加";
        //修改
        if(IS_POST){
            $data=$this->param;
            $id = $data['id'];
            if($this->wechatUserModel->editData($data,$id)){
                $this ->success($title.'成功',url('index'));
            }else{
                $this ->error($this->wechatUserModel->getError());
            }
            return;
        }else{
            // 获取账号信息
            $info = [];
            if ($id!=0) {
                $info = $this->wechatUserModel->get($id);
            }

            // 使用FormBuilder快速建立表单页面。
            $builder = new AdminFormBuilder();
            $builder->setMetaTitle($title.'用户');  // 设置页面标题
            if ($id!=0) {
                $builder->addFormItem('uid', 'hidden', 'ID', '');
            }

            $builder->addFormItem('nickname', 'text', '昵称', '填写一个有个性的昵称吧','','required')
                    ->addFormItem('headimgurl', 'avatar', '头像', '用户头像默认随机分配','','required')
                    ->addFormItem('sex', 'radio', '性别', '',array(0=>'保密',1=>'男',2=>'女',))
                    ->addFormItem('country', 'text', '国家', '','','required')
                    ->addFormItem('province', 'text', '省份', '','','required')
                    ->addFormItem('city', 'text', '城市', '','','required')
                    ->setFormData($info)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->display();
        }
        
    }

}