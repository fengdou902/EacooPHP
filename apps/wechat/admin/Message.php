<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.eacoomall.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\wechat\admin;

use app\wechat\model\Message as MessageModel;

use app\admin\builder\Builder;

class Message extends Base {

    protected $messageModel;
    function _initialize()
    {
        parent::_initialize();
        $this->wechatInfo = get_wechat_info($this->wxid);
        $this->messageModel = new MessageModel();
    }

    //消息管理
    public function index(){
        
        $map['ToUserName'] = $this->wechatInfo['origin_id'];
        $data_list         = $this->messageModel->alias('a')->join('__WECHAT_USER__ w','a.fromusername = w.openid1')->field('a.*,w.headimgurl,w.nickname')->paginate(20);

        $builder = new AdminListBuilder();
        $builder->setMetaTitle('消息列表') // 设置页面标题
            ->addTopBtn('delete',array('title'=>'<i class="fa fa-trash"></i> 删除消息')) //添加删除按钮
            ->keyListItem('MsgType', '消息类型')
            ->keyListItem('Content', '消息内容')
            ->keyListItem('CreateTime', '发送时间','time')
            ->keyListItem('headimgurl', '粉丝头像','avatar')
            ->keyListItem('nickname', '粉丝昵称')
            ->keyListItem('right_button', '操作', 'btn')
            ->setListData($data_list)    // 数据列表
            ->setListPage($data_list->render()) // 数据列表分页
            ->addRightButton('delete')
            ->fetch();
    }

    //编辑公众号
    public function edit($id=0){
        $title=$id ? "编辑" : "添加";
        //修改
        if(IS_POST){
            $data=$this->input('post.');

            $id= $data['id'];
            $result = $this->message_model->editData($data,$id);
            if($result){
                $this ->success($title.'成功',url('index'));
            }else{
                $this ->error($this->message_model->getError());
            }
            return;
        }else{
            if ($id!=0) {
                $info = $this->message_model->find($id);
            }
            // 使用FormBuilder快速建立表单页面。
            $builder = new AdminFormBuilder();
            $builder->setMetaTitle($title.'公众号');  // 设置页面标题
            $builder->addFormItem('id', 'hidden', 'ID', 'ID')
                    ->addFormItem('name', 'text', '公众号名称', '','','required','placeholder="填写公众号名称"')     
                    ->addFormItem('origin_id', 'text', '原始ID', '')
                    ->addFormItem('weixin_number', 'text', '微信号', '')
                    ->addFormItem('appid', 'text', 'APPID', '')
                    ->addFormItem('appsecret', 'text', 'APPSECRET', '')
                    ->addFormItem('mch_id', 'text', '商户ID', '')
                    ->addFormItem('headimg', 'picture', '头像', '')
                    ->addFormItem('qrcode', 'picture', '二维码', '')
                    ->addFormItem('status', 'radio', '状态', '',array(0=>'禁用',1=>'正常',2=>'审核中'),'required')
                    //->setExtraItems(array('type'=>3,'status'=>1))
                    ->setFormData($info)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
        
    }

}