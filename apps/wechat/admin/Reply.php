<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.eacoomall.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\wechat\admin;

use app\wechat\model\Reply as ReplyModel;
use app\wechat\model\Material as MaterialModel;

use app\admin\builder\Builder;

class Reply extends Base {
    
    protected $replyModel;
    protected $materialModel;

    function _initialize()
    {
        parent::_initialize();
        $this->replyModel     = new ReplyModel();
        $this->material_model = new MaterialModel();
        $this->tab_list = [
            'keyword' => array('title'=>'关键词回复','href'=>url('keyword')),
            'special' => array('title'=>'特殊回复','href'=>url('special')),
            'event'   => array('title'=>'事件回复','href'=>url('event'))
            ];
    }
    //关键字回复
    public function keyword($reply_type=0){
        $map['status'] = ['egt', '0']; // 禁用和正常状态
        if($reply_type) $map['reply_type']=$reply_type;
        $map['type'] ='keyword';
        $map['wxid'] =$this->wxid;
        list($data_list,$totalCount) = $this->replyModel->getListByPage($map,'id desc','*',20);
        foreach ($data_list as $key => $data) {
            $metarial_data=$this->material_model->find($data['material_id']);
            switch ($metarial_data['type']) {
                case 'text':
                    $data_list[$key]['reply_content']=cutStr($metarial_data['content'],160,0,0);
                    break;
                case 'image':
                    $data_list[$key]['reply_content']='<img class="cover" width="120" src="'.getThumbImageById($metarial_data['attachment_id'],'medium').'">';
                    break;
                case 'news':
                    $data_list[$key]['reply_content']='<div class="oh" style="background:#fff;border:1px dotted #ddd;padding:6px;border-radius:5px;width:100%;"><a href="'.$metarial_data['url'].'" target="_blank" class="color-6 f12"><h6 class="fb">'.$metarial_data['title'].'</h6><div class="col-md-3 pd0"><img class="cover" width="100" src="'.getThumbImageById($metarial_data['attachment_id'],'medium').'"></div><div class="col-md-8">'.cutStr($metarial_data['news_content'],80,0,0).'</div></a></div>';
                    break;
                default:
                    # code...
                    break;
            }
            
        }
        $optType=array(
                array('id'=>0,'value'=>'全部类型'),
                array('id'=>'text','value'=>'文本'),
                array('id'=>'image','value'=>'图片'),
                array('id'=>'news','value'=>'图文'),
            );
        $builder = new AdminListBuilder();
        $builder->setMetaTitle('关键词回复') // 设置页面标题
            ->setTabNav($this->tab_list,'keyword')  // 设置Tab按钮列表
            ->addTopBtn('addnew',array('title'=>'添加'))  // 添加新增按钮
            ->addTopBtn('resume')  // 添加启用按钮
            ->addTopBtn('forbid')  // 添加禁用按钮
            ->addTopBtn('delete') //添加删除按钮
            ->addSelect('类型','reply_type',$optType)//添加分类筛选
            ->keyListItem('keyword', '关键词')
            ->keyListItem('reply_type','回复类型', 'array',array('text'=>'<label class="label label-info">文本</label>','image'=>'<label class="label label-primary">图片</label>','news'=>'<label class="label label-success">图文</label>'))
            ->keyListItem('reply_content', '回复内容',null,null,'width="430"')
            ->keyListItem('right_button', '操作', 'btn')
            ->setListData($data_list)    // 数据列表
            ->setListPage($totalCount,20) // 数据列表分页
            ->addRightButton('edit')->addRightButton('delete')
            ->fetch();
    }

    //编辑回复
    public function edit($id=0){
        $title = $id ? "编辑" : "添加";
        //修改
        if(IS_POST){
            $material_id =$this->input('post.material_id',false);
            $reply_type  =$this->input('reply_type',false);
            $from        =$this->input('from','add');
            if($material_id) $material_data['id']=$material_id;
                $material_data['wxid']=$this->wxid;
                $material_data['type']=$reply_type;
            if($reply_type=='text') {
                $material_data['title']='关键字回复';
                $material_data['content']=$this->input('post.reply_textarea','','htmlspecialchars_decode');
            } elseif ($reply_type=='image') {
                $material_data['image']=$this->input('post.reply_img');
            } elseif ($reply_type=='news') {
                $material_data['news_title']   = $this->input('post.news_title');
                $material_data['news_img']     = $this->input('post.news_img');
                $material_data['news_url']     = $this->input('post.news_url');
                $material_data['news_content'] = $this->input('post.news_content');
            }

            if ($from=='create') {
                $material_data = $this->material_model->create($material_data);
                $res_id        = $this->material_model->editData($material_data);
            }
        
            $reply_id=$this->input('post.id',false);
            if ($reply_id) {
                $data['id']=$reply_id;
            }
            $data['material_id'] = $material_id ? $material_id:$res_id;//素材ID
            $data['wxid']        = $this->wxid;
            $data['type']        = 'keyword';
            $data['reply_type']  = $reply_type;
            $data['keyword']     = $this->input('post.keyword');

            $data=$this->replyModel->create($data);
            
            if($data){
                $result = $this->replyModel->editData($data);
                if($result){
                    $this->success($title.'成功',U('keyword'));
                }else{
                    if (!$result&&$res_id) {//只更新素材内容
                        $this->success('回复素材更新成功');
                    }
                    $this->error($title.'失败');
                }
            }else{
                $this->error($this->replyModel->getError());
            }
            return;
        } else{
            if ($id!=0){
                $info = $this->replyModel->find($id);
                $metarial_data=$this->material_model->find($info['material_id']);
                if ($metarial_data) {
                    $info['reply_textarea'] = $metarial_data['content'];
                    $info['reply_img']      = $metarial_data['image'];
                    
                    $info['news_title']     = $metarial_data['news_title'];
                    $info['news_img']       = $metarial_data['news_img'];
                    $info['news_url']       = $metarial_data['news_url'];
                    $info['news_content']   = $metarial_data['news_content'];
                }
                $info['from']='add';
            }else{
                    $info['reply_type']='text';
                    $info['from']='add';
                    $info['status']=1;
                }

            $extra_html=$this->selectExtraHtml();

            $reply_type=array('text'=>array('title'=>'文本','data-type'=>'1'),'image'=>array('title'=>'图片','data-type'=>'2'),'news'=>array('title'=>'图文','data-type'=>'3'));
            // 使用FormBuilder快速建立表单页面。
            $builder = new AdminFormBuilder();
            $builder->setMetaTitle($title.'自动回复');  // 设置页面标题
            $builder->addFormItem('id', 'hidden', 'ID', 'ID')
                    ->addFormItem('keyword', 'text', '关键词', '','','required','placeholder="填写关键词"')
                    ->addFormItem('from', 'radio', '添加方式','',array('add'=>'素材ID','create'=>'创建素材')) 
                    ->addFormItem('material_id', 'hidden','素材ID')   
                    ->addFormItem('reply_type', 'select', '类型','',$reply_type)
                    ->addFormItem('reply_textarea', 'textarea', '文本内容', '')
                    ->addFormItem('reply_img', 'picture', '回复图片', '')
                    ->addFormItem('news_title', 'text', '图文标题', '')
                    ->addFormItem('news_img', 'picture', '图文封面', '')
                    ->addFormItem('news_url', 'text', '图文链接', '')
                    ->addFormItem('news_content', 'wangeditor', '图文内容', '',array('config'=>'all','height'=>'360px'))
                    ->addFormItem('status', 'radio', '状态', '',array(0=>'禁用',1=>'正常',2=>'审核中'),'required')
                    //->setExtraItems(array('type'=>3,'status'=>1))
                    ->setFormData($info)
                    ->setExtraHtml($extra_html)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
        
    }
    //编辑回复
    public function edit_big($id=0){
        $title=$id?"编辑":"添加";
        //修改
        if(IS_POST){
            $material_id=$this->input('post.material_id',false);
            $reply_type=$this->input('reply_type',false);

            if($material_id) $material_data['id']=$material_id;
            $material_data['wxid']=$this->wxid;
            $material_data['type']=$reply_type;
            if($reply_type=='text') {
                $material_data['title']='关键字回复';
                $material_data['content']=$this->input('post.reply_textarea','','htmlspecialchars_decode');
            }elseif ($reply_type=='image') {
                $material_data['image']=$this->input('post.reply_img');
            }elseif ($reply_type=='news') {
                $material_data['news_title']   =$this->input('post.news_title');
                $material_data['news_img']     =$this->input('post.news_img');
                $material_data['news_url']     =$this->input('post.news_url');
                $material_data['news_content'] =$this->input('post.news_content');
            }
            $material_data =$this->material_model->create($material_data);
            $res_id        =$this->material_model->editData($material_data);
            
            $reply_id=$this->input('post.id',false);
            if ($reply_id) {
                $data['id']=$reply_id;
            }
            $data['material_id'] =$material_id ? $material_id:$res_id;//素材ID
            $data['wxid']        =$this->wxid;
            $data['type']        ='keyword';
            $data['reply_type']  =$reply_type;
            $data['keyword']     =$this->input('post.keyword');

            $data=$this->replyModel->create($data);
            
            if($data){
                $result = $this->replyModel->editData($data);
                if($result){
                    $this->success($title.'成功',url('keyword'));
                }else{
                    if (!$result&&$res_id) {//只更新素材内容
                        $this->success('回复素材更新成功');
                    }
                    $this->error($title.'失败');
                }
            }else{
                $this->error($this->replyModel->getError());
            }
            return;
        } else{
            if ($id!=0){
                $info = $this->replyModel->find($id);
                $metarial_data=$this->material_model->find($info['material_id']);
                if ($metarial_data) {
                    $info['reply_textarea'] =$metarial_data['content'];
                    $info['reply_img']      =$metarial_data['image'];
                    
                    $info['news_title']     =$metarial_data['news_title'];
                    $info['news_img']       =$metarial_data['news_img'];
                    $info['news_url']       =$metarial_data['news_url'];
                    $info['news_content']   =$metarial_data['news_content'];
                }
            }else{
                    $info['reply_type']='text';
                }

            $extra_html=$this->selectExtraHtml();

            $reply_type=array('text'=>array('title'=>'文本','data-type'=>'1'),'image'=>array('title'=>'图片','data-type'=>'2'),'news'=>array('title'=>'图文','data-type'=>'3'));
            // 使用FormBuilder快速建立表单页面。
            $builder = new AdminFormBuilder();
            $builder->setMetaTitle($title.'自动回复');  // 设置页面标题
            $builder->addFormItem('id', 'hidden', 'ID', 'ID')
                    ->addFormItem('material_id', 'hidden')
                    ->addFormItem('keyword', 'text', '关键词', '','','required','placeholder="填写关键词"')     
                    ->addFormItem('reply_type', 'select', '类型','',$reply_type)
                    ->addFormItem('reply_textarea', 'textarea', '文本内容', '')
                    ->addFormItem('reply_img', 'picture', '回复图片', '')
                    ->addFormItem('news_title', 'text', '图文标题', '')
                    ->addFormItem('news_img', 'picture', '图文封面', '')
                    ->addFormItem('news_url', 'text', '图文链接', '')
                    ->addFormItem('news_content', 'wangeditor', '图文内容', '',array('config'=>'all','height'=>'360px'))
                    ->addFormItem('status', 'radio', '状态', '',array(0=>'禁用',1=>'正常',2=>'审核中'),'required')
                    //->setExtraItems(array('type'=>3,'status'=>1))
                    ->setFormData($info)
                    ->setExtraHtml($extra_html)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
        
    }
    //表单额外代码
    public function selectExtraHtml(){
        return <<<EOF
<script type="text/javascript">
 $(function(){
    var type = $('#reply_type').find("option:selected").attr("data-type");
    switch_form_item(type);
    $("#material_id").attr('type','number');
    $(".item_material_id").show();
    $('.item_reply_type,.item_reply_textarea,.item_reply_img,.item_news_title,.item_news_url,.item_news_img,.item_news_content').hide();

    $('#reply_type').on('change',function(){
        var type = $('#reply_type').find("option:selected").attr("data-type");
        switch_form_item(type);
    });
    $('.item_from label').on('click',function(){
        from_type();
    });

})
//显示
function from_type(){
    if($("#fromadd").is(":checked")){
        $("#material_id").attr('type','hidden');
        $(".item_material_id").hide();
        $('.item_reply_type').show();
    }else{
        $("#material_id").attr('type','number');
        $(".item_material_id").show();
        $('.item_reply_type,.item_reply_textarea,.item_reply_img,.item_news_title,.item_news_url,.item_news_img,.item_news_content').hide();
    }
}
//事件方法
function switch_form_item(type){
        type=parseInt(type);
    if(type == 1){
        $('.item_reply_textarea').show();
        $('.item_reply_img,.item_news_title,.item_news_img,.item_news_url,.item_news_content').hide();
    }else if(type ==2){
        $('.item_reply_img').show();
        $('.item_reply_textarea,.item_news_title,.item_news_img,.item_news_url,.item_news_content').hide();
    }else if(type == 3){
        $('.item_news_title,.item_news_img,.item_news_url,.item_news_content').show();
        $('.item_reply_textarea,.item_reply_img').hide();
    }else{
        $('.item_reply_textarea,.item_reply_img,.item_news_title,.item_news_url,.item_news_img,.item_news_content').hide();
    }
}
</script>
EOF;
    }
    //特殊回复
    public function special(){
        if (IS_POST) {
            // 批量添加数据
            $dataList[] = array('type'=>'image','material_id'=>$this->input('post.image'));
            $dataList[] = array('type'=>'voice','material_id'=>$this->input('post.voice'));
            $dataList[] = array('type'=>'shortvideo','material_id'=>$this->input('post.shortvideo'));
            $dataList[] = array('type'=>'location','material_id'=>$this->input('post.location'));
            $dataList[] = array('type'=>'link','material_id'=>$this->input('post.link'));
            $dataList[] = array('type'=>'default','material_id'=>$this->input('post.default'));
            //$data=$this->replyModel->addAll($dataList);
            foreach ($dataList as $key => $data) {
                $res = $this->replyModel->where(['type'=>$data['type']])->find();
                if ($res) {
                    $result = $this->replyModel->where(['id'=>$res['id']])->setField('material_id',$data['material_id']);
                } else{
                    $data['wxid'] = $this->wxid;
                    $result       = $this->replyModel->isUpdate(false)->allowField(true)->data($data)->save();
                }
            }
            $this->success('更新成功',url('special'));

        }else{
            $map=[];
            $map['type']=array('in','image,voice,shortvideo,location,link,default');
            $map['wxid']=$this->wxid;
            $results = $this->replyModel->where($map)->select();
            $info=[];
            foreach ($results as $key => $row) {
                $info[$row['type']]=$row['material_id'];
            }
            $reply_type = [
                        'text'  =>['title'=>'文本','data-type'=>'1'],
                        'image' =>['title'=>'图片','data-type'=>'2'],
                        'news'  =>['title'=>'图文','data-type'=>'3']
                    ];
            // 使用FormBuilder快速建立表单页面。
            $builder = new AdminFormBuilder();
            $builder->setMetaTitle('特殊回复');  // 设置页面标题
            $builder->addFormItem('id', 'hidden', 'ID', 'ID')
                    ->setTabNav($this->tab_list,'special')  // 设置Tab按钮列表
                    ->addFormItem('news_img', 'info', '提示', '特殊回复内容需要绑定一个素材ID，<a href="'.url('Weixin/Material/text').'" target="_blank">查找素材</a>')
                    ->addFormItem('image', 'number', '图片消息', '留空或0表示不处理','','','placeholder="素材ID"')
                    ->addFormItem('voice', 'number', '语音消息', '留空或0表示不处理','','','placeholder="素材ID"')
                    ->addFormItem('shortvideo', 'number', '短视频消息', '留空或0表示不处理','','','placeholder="素材ID"')
                    ->addFormItem('location', 'number', '位置消息', '留空或0表示不处理','','','placeholder="素材ID"')
                    ->addFormItem('link', 'number', '链接消息', '留空或0表示不处理','','','placeholder="素材ID"')
                    ->addFormItem('default', 'number', '默认回复', '当用户发送的消息没有触发任何回复规则时回复的消息','','','placeholder="素材ID"')
                    ->setFormData($info)
                    //->setAjaxSubmit(false)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
    }
    //事件回复
    public function event(){
        if (IS_POST) {
            $dataList = [];
            // 批量添加数据
            $dataList[] = array('type'=>'subscribe','material_id'=>$this->input('post.subscribe'));
            $dataList[] = array('type'=>'unsubscribe','material_id'=>$this->input('post.unsubscribe'));
            $dataList[] = array('type'=>'scan','material_id'=>$this->input('post.scan'));
            $dataList[] = array('type'=>'report_location','material_id'=>$this->input('post.report_location'));
            $dataList[] = array('type'=>'click','material_id'=>$this->input('post.click'));
            //$data=$this->replyModel->addAll($dataList);
            foreach ($dataList as $key => $data) {
                $res=$this->replyModel->where(array('type'=>$data['type']))->find();
                if ($res) {
                    $result = $this->replyModel->where(array('id'=>$res['id']))->setField('material_id',$data['material_id']);
                }else{
                    $data['wxid']=$this->wxid;
                    $result = $this->replyModel->add($data);
                }
            }
            $this->success('操作成功',url('event'));
        } else{
            $map=[];
            $map['type']=array('in','subscribe,unsubscribe,scan,report_location,click');
            $map['wxid']=$this->wxid;
            $results = $this->replyModel->where($map)->select();
            $info=[];
            foreach ($results as $key => $row) {
                $info[$row['type']]=$row['material_id'];
            }

            $reply_type=array(
                'text'=>array('title'=>'文本','data-type'=>'1'),
                'image'=>array('title'=>'图片','data-type'=>'2'),
                'news'=>array('title'=>'图文','data-type'=>'3')
                );
            // 使用FormBuilder快速建立表单页面。
            $builder = new AdminFormBuilder();
            $builder->setMetaTitle('事件回复');  // 设置页面标题
            $builder->addFormItem('id', 'hidden', 'ID', 'ID')
                    ->setTabNav($this->tab_list,'event')  // 设置Tab按钮列表
                    ->addFormItem('news_img', 'info', '提示', '事件回复内容需要绑定一个素材ID，留空或0表示不处理，<a href="'.url('Weixin/Material/text').'" target="_blank">查找素材</a>')
                    ->addFormItem('subscribe', 'number', '关注', '关注欢迎语','','','placeholder="素材ID"')
                    ->addFormItem('unsubscribe', 'number', '取消关注', '用户取消关注进行事件回复','','','placeholder="素材ID"')
                    ->addFormItem('scan', 'number', '扫描二维码', '绑定关键词进行处理','','','placeholder="素材ID"')
                    ->addFormItem('report_location', 'number', '上报地理位置', '','','','placeholder="素材ID"')
                    ->addFormItem('click', 'number', '点击菜单拉取消息', '','','','placeholder="素材ID"')
                    ->setFormData($info)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
    }
}