<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.eacoomall.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\wechat\admin;

use app\wechat\model\Material as MaterialModel;
use app\admin\builder\Builder;

class Material extends Base {

    protected $WechatObj;
    protected $access_token;
    protected $materialModel;

    function _initialize()
    {
        parent::_initialize();
        
        $this->WechatObj   = get_wechat_object($this->wxid);
        $this->access_token = $this->WechatObj->checkAuth();

        $this->materialModel = new MaterialModel();
        $this->tab_list = [
                'text'  =>['title'=>'文本素材','href'=>url('text')],
                'image' =>['title'=>'图片素材','href'=>url('image')],
                'voice' =>['title'=>'音频素材','href'=>url('voice')],
                'video' =>['title'=>'视频素材','href'=>url('video')],
                'news'  =>['title'=>'图文素材','href'=>url('news')]
            ];

    }

    //文本素材管理
    public function text(){
        $map['status'] = ['egt', '0']; // 禁用和正常状态
        $map['type']   ='text';
        $map['wxid']   = $this->wxid;
        list($data_list,$totalCount) = $this->materialModel->getListByPage($map,'create_time desc','*',20);
        foreach ($data_list as $key => $data) {
            $data_list[$key]['content'] = cutStr($data['content'],160,0,0);
        }
        $builder = new AdminListBuilder();

        $builder->setMetaTitle('文本素材') // 设置页面标题
            ->setTabNav($this->tab_list,'text')  // 设置Tab按钮列表
            ->addTopBtn('addnew',['title'=>'<i class="fa fa-plus"></i> 添加文本素材','href'=>url('edit',['type'=>'text'])])  // 添加新增按钮
            ->addTopBtn('delete') //添加删除按钮
            ->setSearch('请输入ID/名称',url('text'))
            ->keyListItem('id', 'ID')
            ->keyListItem('title', '标题')
            ->keyListItem('content', '素材内容',null,null,'width="430"')
            ->keyListItem('create_time','创建时间')
            ->keyListItem('status', '状态', 'status')
            ->keyListItem('right_button', '操作', 'btn')
            ->setListData($data_list)    // 数据列表
            ->setListPage($totalCount,20) // 数据列表分页
            ->addRightButton('edit')->addRightButton('delete')
            ->fetch();
    }

    //图片素材管理
    public function image(){
        $map['status'] = ['egt', '0']; // 禁用和正常状态
        $map['type']='image';
        $map['wxid']=$this->wxid;
        list($data_list,$page) = $this->materialModel->getListByPage($map,'create_time desc','*',20);
        $builder = new AdminListBuilder();

        $builder->setMetaTitle('图片素材') // 设置页面标题
                ->setTabNav($this->tab_list,'image')  // 设置Tab按钮列表
                ->addTopBtn('addnew',['title'=>'<i class="fa fa-plus"></i> 添加图片素材','href'=>url('edit',['type'=>'image'])])  // 添加新增按钮
                ->addTopBtn('self',['title'=>'一键上传素材到微信素材库','href'=>url('material_to_wechat',['type'=>'image']),'class'=>'btn btn-info btn-sm'])  // 添加素材库按钮
                ->addTopBtn('self',['title'=>'一键下载微信素材库到本地','href'=>url('material_from_wechat',['type'=>'image']),'class'=>'btn btn-info btn-sm'])  // 添加素材库按钮
                ->addTopBtn('delete') //添加删除按钮
                ->setSearch('请输入ID/名称',url('text'))
                ->keyListItem('id', 'ID')
                ->keyListItem('attachment_id', '素材图片','picture')
                ->keyListItem('create_time','创建时间')
                ->keyListItem('status', '状态', 'status')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListData($data_list)    // 数据列表
                ->setListPage($page) // 数据列表分页
                ->addRightButton('edit')->addRightButton('delete')
                ->fetch();
    }

    //语音素材管理
    public function voice(){
        $map['status'] =['egt', '0']; // 禁用和正常状态
        $map['type']   ='voice';
        $map['wxid']   =$this->wxid;
        list($data_list,$page) = $this->materialModel->getListByPage($map,'create_time desc','*',20);
        $builder = new AdminListBuilder();

        $builder->setMetaTitle('语音素材') // 设置页面标题
            ->setTabNav($this->tab_list,'voice')  // 设置Tab按钮列表
            ->addTopBtn('addnew',array('title'=>'<i class="fa fa-plus"></i> 添加语音素材','href'=>url('edit',array('type'=>'voice'))))  // 添加新增按钮
            ->addTopBtn('self',array('title'=>'一键上传素材到微信素材库','href'=>url('material_to_wechat',array('type'=>'voice')),'class'=>'btn btn-info btn-sm'))  // 添加素材库按钮
            ->addTopBtn('self',array('title'=>'一键下载微信素材库到本地','href'=>url('material_from_wechat',array('type'=>'voice')),'class'=>'btn btn-info btn-sm'))  // 添加素材库按钮
            ->addTopBtn('delete') //添加删除按钮
            ->setSearch('请输入ID/名称',url('text'))
            ->keyListItem('id', 'ID')
            ->keyListItem('attachment_id', '素材文件')
            ->keyListItem('create_time','创建时间', 'time')
            ->keyListItem('status', '状态', 'status')
            ->keyListItem('right_button', '操作', 'btn')
            ->setListData($data_list)    // 数据列表
            ->setListPage($page) // 数据列表分页
            ->addRightButton('edit')->addRightButton('delete')
            ->fetch();
    }

    //视频素材管理
    public function video(){
        $map['status'] =array('egt', '0'); // 禁用和正常状态
        $map['type']='video';
        $map['wxid']=$this->wxid;
        list($data_list,$page) = $this->materialModel->getListByPage($map,'create_time desc','*',20);
        $builder = new AdminListBuilder();

        $builder->setMetaTitle('视频素材') // 设置页面标题
            ->setTabNav($this->tab_list,'video')  // 设置Tab按钮列表
            ->addTopBtn('addnew',array('title'=>'<i class="fa fa-plus"></i> 添加视频素材','href'=>url('edit',array('type'=>'video'))))  // 添加新增按钮
            ->addTopBtn('self',array('title'=>'一键上传素材到微信素材库','href'=>url('material_to_wechat',array('type'=>'video')),'class'=>'btn btn-info btn-sm'))  // 添加素材库按钮
            ->addTopBtn('self',array('title'=>'一键下载微信素材库到本地','href'=>url('material_from_wechat',array('type'=>'video')),'class'=>'btn btn-info btn-sm'))  // 添加素材库按钮
            ->addTopBtn('delete') //添加删除按钮
            ->setSearch('请输入ID/名称',url('text'))
            ->keyListItem('id', 'ID')
            ->keyListItem('attachment_id', '素材图片','picture')
            ->keyListItem('create_time','创建时间', 'time')
            ->keyListItem('status', '状态', 'status')
            ->keyListItem('right_button', '操作', 'btn')
            ->setListData($data_list)    // 数据列表
            ->setListPage($page) // 数据列表分页
            ->addRightButton('edit')->addRightButton('delete')
            ->fetch();
    }

    //图文素材管理
    public function news(){
        $map['status'] = ['egt', '0']; // 禁用和正常状态
        $map['type']   = 'news';
        $map['wxid']   = $this->wxid;
        list($data_list,$page) = $this->materialModel->getListByPage($map,'create_time desc','*',20);
        foreach ($data_list as $key => $data) {
            $data_list[$key]['title']='<a href="'.$data['url'].'" target="_blank">'.$data['title'].'</a>';
        }
        $builder = new AdminListBuilder();

        $builder->setMetaTitle('图文素材') // 设置页面标题
            ->setTabNav($this->tab_list,'news')  // 设置Tab按钮列表
            ->addTopBtn('addnew',array('title'=>'<i class="fa fa-plus"></i> 添加图文素材','href'=>url('news_edit',array('type'=>'news'))))  // 添加新增按钮
            ->addTopBtn('self',array('title'=>'一键上传素材到微信素材库','href'=>url('material_to_wechat',array('type'=>'news')),'class'=>'btn btn-info btn-sm'))  // 添加素材库按钮
            ->addTopBtn('self',array('title'=>'一键下载微信素材库到本地','href'=>url('material_from_wechat',array('type'=>'news')),'class'=>'btn btn-info btn-sm'))  // 添加素材库按钮
            ->addTopBtn('resume')  // 添加启用按钮
            ->addTopBtn('forbid')  // 添加禁用按钮
            ->addTopBtn('delete') //添加删除按钮
            ->setSearch('请输入ID/名称',url('text'))
            ->keyListItem('id', 'ID')
            ->keyListItem('title', '图文标题')
            ->keyListItem('attachment_id', '图文封面','picture',null,'width="130"')
            ->keyListItem('description', '图文内容',null,null,'width="320"')
            ->keyListItem('create_time','创建时间')
            ->keyListItem('status', '状态', 'status')
            ->keyListItem('right_button', '操作', 'btn')
            ->setListData($data_list)    // 数据列表
            ->setListPage($page) // 数据列表分页
            ->addRightButton('edit',['href'=>url('news_edit',['id'=>'__data_id__'])])->addRightButton('delete')
            ->fetch();
    }

    //编辑素材
    public function edit($id = 0){
        $title = $id ? "编辑" : "添加";
        if ($id!=0) {
            $info = $this->materialModel->get($id);
        } else{
            $info['type']=$this->input('get.type');
        }
        //修改
        if(IS_POST){
            $data = $this->input('post.');
            $id = isset($data['id']) && $data['id']>0 ? $data['id']:false;

            if(isset($data['content']))     $data['content']       = htmlspecialchars_decode(($data['content']));
            if(isset($data['news_content'])) $data['news_content'] = htmlspecialchars_decode($data['news_content']);
            $data['wxid'] = $this->wxid;

            if($this->materialModel->editData($data,$id)){
                $this->success($title.'成功',url($data['type']));
            } else{
                $this->error($this->materialModel->getError());
            }

        } else{

            $extra_html = $this->selectExtraHtml();
            $reply_type =[
                    'text'  =>['title'=>'文本素材','data-type'=>'1'],
                    'image' =>['title'=>'图片素材','data-type'=>'2'],
                    'news'  =>['title'=>'图文素材','data-type'=>'3']
                    ];
            // 使用FormBuilder快速建立表单页面。
            $builder = new AdminFormBuilder();
            $builder->setMetaTitle($title.'素材');  // 设置页面标题
            $builder->addFormItem('id', 'hidden', 'ID', 'ID')
                    ->setTabNav($this->tab_list,$info['type'])  // 设置Tab按钮列表
                    ->addFormItem('title', 'text', '标题', '')
                    ->addFormItem('type', 'select', '素材类型','',$reply_type)   
                    ->addFormItem('content', 'textarea', '文本素材内容', '')
                    ->addFormItem('attachment_id', 'picture', '图片', '')
                    ->addFormItem('url', 'text', '链接', '')
                    ->addFormItem('description', 'textarea', '摘要', '')
                    ->addFormItem('news_content', 'wangeditor', '内容', '',array('config'=>'all','height'=>'360px'))
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
    $('.item_type').hide();
    var type = $('#type').find("option:selected").attr("data-type");
    switch_form_item(type);
    $('#type').on('change',function(){
        var type = $('#type').find("option:selected").attr("data-type");
        switch_form_item(type);
    });
})
//事件方法
function switch_form_item(type){
        type=parseInt(type);
    if(type == 1){
        $('.item_content,.item_title').show();
        $('.item_attachment_id,.item_description,.item_attachment_id,.item_url,.item_news_content').hide();
    }else if(type ==2){
        $('.item_attachment_id').show();
        $('.item_content,.item_description,.item_title,.item_url,.item_news_content').hide();
    }else if(type == 3){
        $('.item_title,.item_description,.item_attachment_id,.item_url,.item_news_content').show();
        $('.item_content,.item_attachment_id').hide();
    }else{
        $('.item_content,.item_description,.item_attachment_id,.item_title,.item_url,.item_attachment_id,.item_news_content').hide();
    }
}
</script>
EOF;
    }
    
    //编辑图文素材
    public function news_edit($id=0){
        $title = $id ? "编辑":"添加";
        //$this->hide_panel =true;//隐藏base模板面板
        $this->assign('meta_title',$title.'图文');
        
        //修改
        if(IS_POST){
            $data                  = [];
            $data['id']            = $id ? $id : $this->input('post.id',false,'intval');
            $data['title']         = $this->input('post.title');
            $data['news_content']  = htmlspecialchars_decode($this->input('post.news_content'));
            $data['description']   = $this->input('post.description',cutStr($data['news_content'],54,0,0));
            $data['attachment_id'] = $this->input('post.attachment_id');
            $data['url']           = $this->input('post.url');
            $data['group_id']      = $this->input('post.group_id');
            $data['type']          = 'news';
            $data['wxid']          = $this->wxid;
            $data['create_time']   = time();
            $data['status']        = 1;

            $result = $this->materialModel->editData($data,$data['id']);
            if($result){
                if (!$data['group_id']) {
                    $this->materialModel->where('id',$result)->setField('group_id',$result);
                    
                }
                if(!$data['id']){
                    $callback_id=$result;
                }else{
                    $callback_id=$data['id'];
                }
                $callback_url = url('news_edit',array('id'=>$callback_id));
                $this->success($title.'成功',$callback_url);
            }else{
                $this->error($this->materialModel->getError());
            }
            return;
        } else{
            $sub_news ='';
            $news['news_content']=$news['attachment_id']='';
            
            if ($id!=0) {
                $news = $this->materialModel->find($id);
                //获取子图文
                $sub_news=$this->materialModel->getList(['group_id'=>$news['group_id'],'type'=>'news'],'id,title,type,description,attachment_id,url,group_id','create_time asc');
                
            }
            $this->assign('news',$news);
            $this->assign('sub_news',$sub_news);
            $this->assign('id',$id);
            $this->assign('tab_list',$this->tab_list);
            return $this->fetch();
        }
        
    }
    //同步本地素材到微信素材库
    public function material_to_wechat($type=null){
        // 上传本地素材
        $map ['wx_media_id'] = 0;
        $map ['wxid']        = $this->wxid;
        $map ['type']        = $type;
        
        $field = '*';
        $list =$this->materialModel->limit( 10 )->where ( $map )->field ( $field . ',count(id) as count' )->group ( 'group_id' )->order ( 'group_id desc' )->select();
        if (empty ( $list )) {
            $this->success ( '上传素材完成', url($type) );
            exit ();
        }
        //图片上传
        if ($type=='image'||$type=='voice') {
            foreach ( $list as $vo ) {
                if ($type=='image') {
                    $mediaId = $this->_image_media_id ($vo ['attachment_id'],'image');
                }else{
                    $mediaId=$this->_get_file_media_id($vo ['attachment_id'],'voice');
                }
                
                if ($mediaId) {
                    $save ['wx_media_id'] = $mediaId;
                    $this->materialModel->where ( array (
                            'id' => $vo ['id'] 
                    ) )->save ( $save );
                }
            }
        } elseif ($type=='news') {
            //dump($list);exit;
            foreach ( $list as $vo ) {
                $ids [] = $vo ['id'];
                $gids [] = $vo ['group_id'];
            }
            $map2 ['id'] = array (
                    'not in',
                    $ids 
            );
            $map2 ['group_id'] = array (
                    'in',
                    $gids 
            );
            $child = $this->materialModel->where ( $map2 )->field ( $field )->select ();
            empty ( $child ) || $list = array_merge ( $list, $child );

            foreach ( $list as $vo ) {
                $data ['title']                 = $vo ['title'];
                $data ['thumb_media_id']        = $this->_image_media_id ( $vo ['attachment_id'],'thumb');
                //$data ['author']              = $vo ['author'];
                $data ['digest']                = $vo ['description'];
                $data ['show_cover_pic']        = 1;
                $data ['content']               = str_replace ( '"', '\'', $vo ['news_content'] );
                $data ['content_source_url']    = url('news_detail', array ('id' => $vo ['id']));
                
                $articles [$vo ['group_id']] [] = $data;
            }
            $url = 'https://api.weixin.qq.com/cgi-bin/material/add_news?access_token='.$this->access_token;
    //      dump($url);
            foreach ( $articles as $group_id => $art ) {
                $param ['articles'] = $art;
                //dump(JSON($param));exit;
                $res = post_data ( $url, $param );
                if ($res ['errcode'] != 0) {
                    $this->error ( error_msg ( $res ) );
                } else {
                    $map3 ['group_id'] = $group_id;
                    $map3 ['type']=$type;
                    $this->materialModel->where($map3)->setField('wx_media_id',$res['media_id']);
                    $newsUrl = $this->_news_url($res ['media_id'] );
                    foreach ( $art as $a ) {
                        $map4['group_id']=$group_id;
                        $map4 ['type']=$type;
                        $map4 ['title'] = $a ['title'];
                        $this->materialModel->where ( $map4 )->setField ( 'url', $newsUrl [$a ['wx_url']] );
                    }
                }
            }
        }
        
        $url = url ($type);
        $this->success ( '上传本地素材到微信中，请勿关闭', $url );
    }
        // 获取图文素材url
    function _news_url($media_id) {
        $url = 'https://api.weixin.qq.com/cgi-bin/material/get_material?access_token=' .$this->access_token;
        $param ['media_id'] = $media_id;
        $news = post_data ( $url, $param );
        if (isset ( $news ['errcode'] ) && $news ['errcode'] != 0) {
            $this->error ( error_msg ( $news ) );
        }
        foreach ( $news ['news_item'] as $vo ) {
            $newsUrl [$vo ['wx_url']] = $vo ['url'];
        }
        return $newsUrl;
    }
    //图片
    function _image_media_id($cover_id,$type='thumb') {
        $attachment_info = get_attachment_info ($cover_id);
        $path = $attachment_info['path'];

        $file_driver =config('upload_config.rootPath');//文件保存路径
        $file_driver = check_driver_is_exist($file_driver);
        if ($file_driver != 'local' && ! file_exists ( PUBLIC_PATH . $path)) { // 先把图片下载到本地
            
            $pathinfo = pathinfo ( PUBLIC_PATH . $path);
            mkdirs ( $pathinfo ['dirname'] );
            
            $content = wp_file_get_contents ( path_to_url($path));
            $res = file_put_contents ( PUBLIC_PATH . $path, $content );
            if (! $res) {
                $this->error ( '远程图片下载失败' );
            }
        }
        
        if (! $path) {
            $this->error ( '获取文章封面失败，请确认是否增加封面' );
        }
        
        $param ['type'] = $type;
        $param ['media'] = '@' . realpath ( PUBLIC_PATH . $path );
        $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$this->access_token;

        $res = post_data ( $url, $param, true );
        
        if (isset ( $res ['errcode'] ) && $res ['errcode'] != 0) {
            $this->error ( error_msg ( $res, '图片上传' ) );
        }
        if ($type=='thumb') {
            $map ['attachment_id'] = $cover_id;
            $map ['type'] = 'image';
            $map ['wxid'] = $this->wxid;
            $this->materialModel->where ( $map )->setField ( 'wx_media_id', $res ['media_id'] );
        }
        
        return $res ['media_id'];
    }

    //上传视频、语音素材
    function _get_file_media_id($file_id,$type='voice',$title='',$introduction=''){
        $fileInfo= model('attachment')->get($file_id);
        if ($fileInfo){
            $path=$fileInfo['path'];
            if (! $path) {
                $this->error ('获取素材失败' );
                exit ();
            }
            $param ['type'] = $type;
            $param ['media'] = '@' . realpath ( ROOT_PATH . $path );
            if ($type=='video'){
                $param['description']['title']=$title;
                $param['description']['introduction']=$introduction;
            }
            $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$this->access_token;
            $res = post_data ( $url, $param);
            if (!$res){
                $this->error('同步失败');
            }
            if (isset ( $res ['errcode'] ) && $res ['errcode'] != 0) {
                $this->error ( error_msg ( $res, '素材上传' ) );
                exit ();
            }
        }
        return $res ['media_id'];
    }

    //下载微信素材库到本地
    public function material_from_wechat($type = null){
        if (!$type||!in_array($type, array('text','image','voice','video','news'))) {
            return false;
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.$this->access_token;
        $param ['type'] = $type;
        $param ['offset'] = input( 'offset', 0, 'intval' );
        $param ['count'] = 20;
        $list = post_data ( $url, $param );
        if (isset ( $list ['errcode'] ) && $list ['errcode'] != 0) {
            $this->error ( error_msg ( $list ) );
        }
        if (empty ( $list ['item'] )) {
            $this->success ( '下载素材完成', url ($type) );
            exit ();
        }
        $res = $this->save_material_info($type,$list);//保存素材信息到数据库
        $url = url ( 'material_from_wechat', ['type'=>$type,'offset' => $param ['offset'] + $list ['item_count']]);
        $this->success ( '下载微信素材中，请勿关闭', $url );
    }
    
    //保存微信库素材信息到本地
    private function save_material_info($type,$list){
        if (!$type||!$list) {
            return false;
        }
        $map ['wx_media_id'] = array ('in', getSubByKey( $list ['item'], 'media_id' ) 
        );
        //$map['token']=get_token();
        //$map['manager_id']=$this->mid;
        $has = $this->materialModel->where($map )->field ( 'DISTINCT wx_media_id,id' )->find();
        foreach ( $list ['item'] as $item ) {
            $media_id = $item ['media_id'];
            if (isset ( $has [$media_id] ))
                continue;
            
            $ids = $data = $meta_data= [];
            if ($type=='news') {
                foreach ( $item ['content'] ['news_item'] as $vo ) {
                        $meta_data ['author']         = $vo ['author'];
                        $meta_data ['thumb_media_id'] = $vo ['thumb_media_id'];
                        $data ['fields']              = json_encode($meta_data);
                        $data ['description']         = $vo ['digest'];
                        $data ['title']               = $vo ['title'];
                        $data ['news_content']             = $vo ['content'];
                        $data ['attachment_id']       = $this->_download_material_file('image',$meta_data['thumb_media_id']);
                        $data ['url']                 = $vo ['url'];       
                        $data ['fields']              = json_encode($meta_data);
                        $data ['type']                = $type;
                        $data ['wx_media_id']         = $media_id;
                        //$data ['create_time']         = time();
                        $data ['wxid']                = $this->wxid;
                                  
                }
            } elseif ($type=='image') {
                if ($item ['url']) {
                    $meta_data=$item;
                    $data ['attachment_id'] = $this->_download_material_file ('image',$media_id, $item ['url'] );
                    $data ['url'] = $item['url'];
                }
            } elseif($type=='voice'){
                $data ['title'] = $item['name'];
                $data ['attachment_id'] = $this->_download_material_file ('voice',$media_id, $item ['url'] );
            } elseif($type=='video'){//视频下载暂未实现
                $video                  = $this->_download_material_file ('video',$media_id);
                $data ['title']         = $video['title'];
                $data ['url']           = $video['down_url'];
                $data['description']    = $video['description'];
                $data ['attachment_id'] = $this->_download_material_file ('video',0, $data ['url'] );
            }
            if ($type!='news') {
                $data ['fields']      = json_encode($meta_data);
                $data ['type']        = $type;
                $data ['wx_media_id'] = $media_id;
                //$data ['create_time'] = time();
                $data ['wxid']        = $this->wxid;
            }
            $this->materialModel->isUpdate(false)->data($data)->save();
            $ids [] = $this->materialModel->id;
            
            if (! empty ( $ids )&&is_array($ids)&&$type=='news') {
                $map2 ['id'] = array ('in',$ids);
                $this->materialModel->where ( $map2 )->setField ( 'group_id', $ids [0] );
            }
        }
        return true;
    }

    //下载素材文件
    function _download_material_file($type='image',$media_id, $fileUrl = '') {
        if ($type=='image') {
            $save_dir='picture';
            $ext='jpg';
        } elseif($type=='voice'){
            $save_dir='attachment';
            $ext='mp3';
        } elseif ($type=='video') {
            $save_dir='attachment';
            $ext='mp4';
        }
        $savePath1='./uploads/'.$save_dir.'/' . time_format ( NOW_TIME, 'Y-m-d' );
        $savePath = $savePath1; 
        mkdirs ( $savePath );
        if (empty ( $fileUrl )) {
            // 获取图片URL
            $url = 'https://api.weixin.qq.com/cgi-bin/material/get_material?access_token='.$this->access_token;
            $param ['media_id'] = $media_id;
            $fileContent = post_data ( $url, $param, false, false );
            //$fileContent=http_post($url, JSON($param));
            $filejson = json_decode ( $fileContent, true );
            if (isset ( $filejson ['errcode'] ) && $filejson ['errcode'] != 0) {
                $this->error ( error_msg ( $filejson, '下载图片' ) );
                exit ();
            }
             //dump($fileContent);
             //dump($filejson);dump($res);exit;
            //if ($fileContent){
            if ($type!='video') {
                $picName = 'wx'.$this->wxid.$type.'_'.time().mt_rand(100,9999). '.'.$ext;
                $picPath = $savePath . '/' . $picName;
                $res = file_put_contents ( $picPath, $fileContent );
            }else{
                return $filejson;
            }
                
             //}
        } else {
            $content = wp_file_get_contents ( $fileUrl );
            // 获取图片扩展名
            $fileExt = substr ( $fileUrl, strrpos ( $fileUrl, '=' ) + 1 );
            // $fileExt=='jpeg'
            if ($type=='image') {
                if (empty ( $fileExt ) || $fileExt == 'jpeg') {
                    $fileExt = $ext;
                }
            }else{
                if (empty ( $fileExt ) ) {
                    $fileExt = $ext;
                }
            }
            $picName = 'wx'.$this->wxid.$type.'_'.time().mt_rand(100,9999).'.'.$fileExt;
            $picPath = $savePath . '/' . $picName;
            $res = file_put_contents ( $picPath, $content );
            if (! $res) {
                $this->error ( '远程素材下载失败' );
                exit ();
            }
        }
        $cover_id = 0;
        if ($res) {
            $file_data = [];
            // 保存记录，添加到picture表里，获取coverid
            //$url = U ( 'Attachment/uploadPicture', array('session_id' => session_id()));
            /*$_FILES ['download'] = array (
                    'name' => $picName,
                    'type' => 'application/octet-stream',
                    'tmp_name' => $picPath,
                    'size' => $res,
                    'error' => 0 
            );*/
            $file_data['uid']         = is_login();
            $file_data['name']        = str_replace('.'.$ext,'',$picName);
            $file_data['path']        = $savePath1.'/'.$picName;
            $file_data['location']    = 'local';
            $file_data['size']        = $res;
            $file_data['ext']         = $ext;
            $file_data['create_time'] = time();
            $file_data['status']      = 1;
            model('attachment')->isUpdate(false)->data($file_data)->save();
            $cover_id = model('attachment')->id;
            //unlink ( $picPath );
            unset($media_id);
        }
        return $cover_id;
    }
}