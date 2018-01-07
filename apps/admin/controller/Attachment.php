<?php
// 附件管理控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use app\admin\builder\Builder;

use app\common\controller\Upload;
use app\common\model\Attachment as AttachmentModel;
use app\common\model\TermRelationships;
use app\common\model\Config;

class Attachment extends Admin {

    protected $attachmentModel;
    
    function _initialize()
    {
        parent::_initialize();
        $this->attachmentModel  = new AttachmentModel();
    }

    //附件首页
    public function index($term_id=0){

        $this->assign('meta_title','附件管理');
        $this->assign('page_config',['self'=>'来源：<div class="btn-group mr-20">
                  <button type="button" onclick="javascript:location.href=\''.url('admin/Attachment/index').'\'" class="btn btn-default btn-flat">默认</button>
                  <button type="button" class="btn btn-default btn-flat dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="'.url('admin/Attachment/index',['path_type'=>0]).'">默认</a></li>
                    
                  </ul>
                </div>']);
        // 搜索
        $keyword = input('get.keyword');
        if ($keyword) {
            $this->attachmentModel->where('id|name','like','%'.$keyword.'%');
        }

        $attachment_options = config('attachment_options');//附件配置选项（来自附件设置）
        $this->assign('mediaTypeList',[
                1=>'图像',
                2=>'音频',
                3=>'视频',
                4=>'文件',
            ]);//媒体类型列表

        $path_type = input('param.path_type',false);//路径类型
        if ($path_type) {
            $map['path_type'] = $path_type;
        } else {
            $map['path_type'] = ['in','picture,file,wechat'];
        }
        //筛选start
        if ($term_id>0) {
            //$media_ids = TermRelationships::where(['term_id'=>$term_id,'table'=>'attachment'])->select();
            $media_ids = TermRelationships::where(['term_id'=>$term_id,'table'=>'attachment'])->column('object_id');
            if(count($media_ids)){
                //$media_ids = array_column($media_ids,'object_id');
                $map['id'] = ['in',$media_ids];
            } else{
                $map['id']  = 0;
            }
        }

        $media_type = input('param.media_type',false,'intval');
        if ($media_type>0) {
            switch ($media_type) {
                case '1':
                    $map['ext']=array('in','jpg,jpeg,png,gif');
                    break;
                case '2':
                    $map['ext']=array('in','mp3,wav,wma,ogg');
                    break;
                case '3':
                    $map['ext']=array('in','mp4,rm,rmvb,wmv,avi,3gp,mkv');
                    break;
                case '4':
                    $map['ext']=array('in','doc,docx,xls,xlsx,ppt,pptx,pdf,wps,txt,zip,rar,gz,7z,b2z');
                    break;
                default:
                    # code...
                    break;
            }
        }
        $choice_date_range = input('param.choice_date_range',false);
        if (!empty($choice_date_range)) {//日期筛选
            $this->assign('choice_date_range',$choice_date_range);
            $choice_date_range                 = explode('—', $choice_date_range);
            $choice_from_date                  = strtotime(str_replace('/','-', $choice_date_range[0]).' 00:00:00');
            $choice_to_date                    = strtotime(str_replace('/','-', $choice_date_range[1]).' 24:00:00');
            $map['create_time']                = [['gt',$choice_from_date],['lt',$choice_to_date]];
            $attachment_options['page_number'] = 1000;//防止分页
        }
        //筛选end

        $map['status'] = 1;
        $page_number = $attachment_options['page_number']? $attachment_options['page_number']:24;
        $file_list = $this->attachmentModel->where($map)->order('sort asc,create_time desc,update_time desc')->paginate($page_number);

        $this->assign('attachment_list_data',$file_list);//附件列表数据

        $media_cats = model('terms')->getList(['taxonomy'=>'media_cat']);
        $this->assign('media_cats',$media_cats);//获取分类数据
        $this->assign('table_data_page',$file_list->render());

        $this->assign('path_type',$path_type);
    	return $this->fetch();
    }

    /**
     * 获取附件信息
     * @param  int $id id
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function attachmentInfo($id){
        $return = get_attachment_info($id);//附件信息 
        return json($return);
    }

    /**
     * 编辑附件信息
     * @param  int $id id
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function editAttachment($data=[]){
        if (IS_POST) {
            $id          = input('post.id',0,'intval');
            $data['alt'] = input('post.alt','');
            $term_id     = input('post.term_id',false,'intval');
            $result      = $this->attachmentModel->save($data,['id'=>$id]);
            if ($result) {
                update_media_term($id,$term_id);
                cache('Attachment_'.$id,null);
                $this->success('更新成功',url('index'));
            } else{
                $this->error($this->attachmentModel->getError());
            }
        }

    }

    /**
     * 移动分类
     */
    public function moveCategory() {
        if (IS_POST) {
            $ids      = input('post.ids');
            $from_cid = input('post.from_cid');
            $to_cid   = input('post.to_cid');
            if ($from_cid === $to_cid) {
                $this->error('目标分类与当前分类相同');
            }
            if ($to_cid) {
                $ids=explode(',',$ids);
                if (count($ids)>0) {
                    foreach ($ids as $key => $id) {
                        update_media_term($id,$to_cid);
                        cache('Attachment_'.$id,null);
                    }
                }else{
                    update_media_term($ids,$to_cid);
                }

                $this->success('移动成功');

            } else {
                $this->error('请选择目标分类');
            }
        }
    }

    /**
     * 删除附件
     * @param  int $id id
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function delAttachment($id = 0,$is_return=true){ 
        $return = get_attachment_info($id);//附件信息 
    
        cache('Attachment_'.$id,null);//删除缓存信息
        if ($return['location']=='local') {
            //$realpath = realpath('.'.getRootUrl().$return['path']);
            $realpath = $return['real_path'];

            $imgInfo = pathinfo($realpath);//图片信息
            if (unlink($realpath)===false) {//本地文件已经不存在了
                $this->error('删除失败！');
            } elseif(in_array($return['ext'],array('jpg','jpeg','png','gif'))){
               $attachment_options = config('attachment_options');//获取附件配置值
               //删除缩略图
               @unlink($imgInfo['dirname'].'/thumb_'.$attachment_options['small_size']['width'].'_'.$attachment_options['small_size']['height'].'_'.$imgInfo["basename"]);//删除缩略图
               @unlink($imgInfo['dirname'].'/thumb_'.$attachment_options['medium_size']['width'].'_'.$attachment_options['medium_size']['height'].'_'.$imgInfo["basename"]);//删除中等尺寸
               @unlink($imgInfo['dirname'].'/thumb_'.$attachment_options['large_size']['width'].'_'.$attachment_options['large_size']['height'].'_'.$imgInfo["basename"]);//删除大尺寸
            }
            $resut =$this->attachmentModel->destroy($id);
            delete_media_term($id,$return['term_id']);//删除关联的分类
            if ($is_return==true) $this->success('删除成功！不可恢复');
            
        } else {//属于远程url文件
            $resut = $this->attachmentModel->destroy($id);
            delete_media_term($id,$return['term_id']);//删除关联的分类
            if ($is_return==true) $this->success('删除成功！不可恢复');
        }
    }

    /**
     * 附件分类
     * @param  int $id id
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function category(){
        $tab_list = [
                'index'    =>['title'=>'媒体文件','href'=>url('index'),'extra_attr'=>'data-pjax=false'],
                'category' =>['title'=>'附件分类','href'=>url('category')],
                'setting'  =>['title'=>'设置','href'=>url('setting')]
            ];
        $tab_obj=[
            'tab_list'=>$tab_list,
            'current'=>'category'
            ];
        \think\Loader::action('Terms/index',['media_cat','attachment',$tab_obj,url('mediaCatEdit',['term_id'=>'__data_id__'])]);

    }

    /**
     * 附件分类编辑
     * @param  int $term_id term_id
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function mediaCatEdit($term_id = 0){
        $tab_list=[
                'index'    =>['title'=>'媒体文件','href'=>url('index'),'extra_attr'=>'data-pjax=false'],
                'category' =>['title'=>'附件分类','href'=>url('category')],
                'setting'  =>['title'=>'设置','href'=>url('setting')]
            ];
        $tab_obj=[
            'tab_list'=>$tab_list,
            'current'=>'category'
            ];
        \think\Loader::action('admin/Terms/edit',[$term_id,'media_cat',$tab_obj]);

    }

    /**
     * 附件设置
     * @return [type] [description]
     * @date   2017-10-13
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function setting(){
        
        $tab_list=[
            'index'=>['title'=>'媒体文件','href'=>url('index'),'extra_attr'=>'data-pjax=false'],
            'category'=>['title'=>'附件分类','href'=>url('category')],
            'attachment_option'=>['title'=>'设置','href'=>url('setting')]
        ];
        \think\Loader::action('admin/Config/attachmentOption',[$tab_list]);
    }

    //附件Widget分页列表(必须有Widget扩展)
    public function attachmentWidgetList($from_type = null, $paged = 1, $cat = 0, $path_type = 'picture'){
        widget('common/Attachment/paged_list',[$from_type,$paged,$cat,$path_type]); 
    }   
    //获取builder多图上传列表
    public function builder_multiple_attachmentlist($ids,$nolayout=false,$path_type='picture'){
        $map['id']     = array('in',$ids);
        $map['status'] = 1;
        $file_list=$this->attachmentModel->getList($map);
        foreach ($file_list as $key => $data) {
            if ($data['location']!='link') {
                $data['url']='http://'.$_SERVER['HTTP_HOST'].getImgSrcByExt($data['ext'],$data['path'],true);
            }else{
                $data['url']=$data['path'];
            }
            if ($nolayout==true) {
                echo '<img class="" src="'.$data['url'].'" data-id="'.$data['id'].'">';
            }else{
                echo '<div class="col-md-3"><div class="thumbnail"><i class="fa fa-times-circle remove-attachment"></i><img class="img" src="'.$data['url'].'" data-id="'.$data['id'].'"></div></div>';
            }
            
        }
    } 

    /**
     * 附件弹窗
     * @return [type] [description]
     */
    public function attachmentLayer()
    {
        $data = input('param.');
        $path_type = !empty($data['path_type']) ? $data['path_type'] : 'picture';
        $from = !empty($data['from']) ? $data['from'] : '';

        $map['path_type'] = ['in',$path_type];

        $widget_show_type = config('attachment_options.widget_show_type');//附件选择器显示类型(0:所有，1:作者)
        if (intval($widget_show_type)==1) {
          $map['uid'] = is_login();
        }
        //分类
        if (!empty($data['cat']) && $data['cat']>0) {
            $media_ids = TermRelationships::where(['term_id'=>$data['cat'],'table'=>'attachment'])->select();
            if(count($media_ids)){
                $media_ids = array_column($media_ids,'object_id');
                //$post_ids=array_merge(array($post_ids),$post_ids);
                $map['id']=['in',$media_ids];
            } else{
                $map['id']=0;
            }
        }
        $map['status'] = 1;
        $data_list = AttachmentModel::where($map)->order('sort asc,create_time desc,update_time desc')->paginate(20);
        $this->assign('data_list',$data_list);//附件列表数据

        $page_totalCount = AttachmentModel::where(['status'=>1])->count();
        $this->assign('media_totalCount',$page_totalCount);//总数量

        $media_cats = model('admin/terms')->getList(['taxonomy'=>'media_cat']);

        foreach ($media_cats as $key => $cat) {
            $media_cats[$key]['count'] = term_media_count($cat['term_id'],$path_type);
        }
        
        $this->assign('media_cats',$media_cats);//获取分类数据
        $this->assign('data_page',$data_list->render());//分页

        $this->assign('input_id_name',$data['input_id_name']);//输入框ID
        $this->assign('select_type',$data['select_type']);//赋值参数
        $this->assign('mediaTypeList',[1=>'图像',2=>'视频',3=>'音频',4=>'文件']);//媒体类型列表
        $this->assign('path_type',$path_type);
        $this->assign('from',$from);

        $param['attachmentDaterangePicker']=1;
        $this->assign('attachmentDaterangePicker',$param['attachmentDaterangePicker']);//是否导入时间选择器

        return $this->fetch();
    }

    /**************************************上传相关****************************************/
    /* 文件上传 */
    public function upload() {
        $controller = new Upload;
        $return = $controller->upload();
        return json($return);
    }
    
    /**
     * 上传远程文件
     * @param  string  $url            远程文件地址
     * @param  boolean $download_local 是否同时下载到本地
     * @return [type]                  [description]
     */
    public function uploadRemoteFile($url='',$download_local=false){
        $controller = new Upload;
        $return = $controller->uploadRemoteFile();
        return json($return);
    }


    /**用于兼容UM编辑器的图片上传方法
     * @auth 陈一枭
     */
    // public function uploadPictureUM()
    // {
    //     header("Content-Type:text/html;charset=utf-8");
    //     //TODO: 用户登录检测
    //     /* 返回标准数据 */
    //     $return = array('status' => 1, 'info' => '上传成功', 'data' => '');

    //     //实际有用的数据只有name和state，这边伪造一堆数据保证格式正确
    //     $originalName = 'u=2830036734,2219770442&fm=21&gp=0.jpg';
    //     $newFilename = '14035912861705.jpg';
    //     $filePath = 'upload\/20140624\/14035912861705.jpg';
    //     $size = '7446';
    //     $type = '.jpg';
    //     $status = 'success';
    //     $rs = array(
    //         "originalName" => $originalName,
    //         'name' => $newFilename,
    //         'url' => $filePath,
    //         'size' => $size,
    //         'type' => $type,
    //         'state' => $status,
    //         'original' => $_FILES['upfile']['name']
    //     );

    //     $setting = config('editor_upload');
    //     $setting['rootPath']='./Uploads/Editor/Picture/';

    //     //$driver = modC('PICTURE_UPLOAD_DRIVER','local','config');
    //     $driver ='./Uploads/Editor/Picture/';//图片保存路径
    //     $driver = check_driver_is_exist($driver);
    //     $uploadConfig = get_upload_config($driver);

    //     $info = $this->attachmentModel->upload(
    //         $_FILES,
    //         $setting,
    //         $driver,
    //         $uploadConfig
    //     ); //TODO:上传到远程服务器

    //     /* 记录图片信息 */
    //     if ($info) {
    //         $return['status'] = 1;
    //         if ($info['Filedata']) {
    //             $return = array_merge($info['Filedata'], $return);
    //         }
    //         if ($info['download']) {
    //             $return = array_merge($info['download'], $return);
    //         }
    //         $rs['state'] = 'SUCCESS';
    //         $rs['url'] = path_to_url($info['path']);
    //         if ($type == 'ajax') {
    //             echo json_encode($rs);
    //             exit;
    //         } else {
    //             echo json_encode($rs);
    //             exit;
    //         }

    //     } else {
    //         $return['state'] = 0;
    //         $return['info'] = $this->attachmentModel->getError();
    //     }

    //     /* 返回JSON数据 */
    //     return json($return);
    // }


    // public function uploadFileUE(){
    //     $return = ['status' => 1, 'info' =>'上传成功', 'data' => ''];

    //     //实际有用的数据只有name和state，这边伪造一堆数据保证格式正确
    //     $originalName = 'u=2830036734,2219770442&fm=21&gp=0.jpg';
    //     $newFilename = '14035912861705.jpg';
    //     $filePath = 'upload\/20140624\/14035912861705.jpg';
    //     $size = '7446';
    //     $type = '.jpg';
    //     $status = 'success';
    //     $rs = [
    //         'name' => $newFilename,
    //         'url' => $filePath,
    //         'size' => $size,
    //         'type' => $type,
    //         'state' => $status
    //     ];

    //     /* 调用文件上传组件上传文件 */
    //     $File = model('File');

    //    // $driver = modC('DOWNLOAD_UPLOAD_DRIVER','local','config');
    //     $driver ='./Uploads/Editor/File/';//图片保存路径
    //     $driver = check_driver_is_exist($driver);
    //     $uploadConfig = get_upload_config($driver);

    //     $setting = config('editor_upload');
    //     $setting['rootPath']='./Uploads/Editor/File/';


    //     $setting['exts'] = 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml,xlsx,xls,ppt,pptx,pdf';
    //     $info = $File->upload(
    //         $_FILES,
    //         $setting,
    //         $driver,
    //         $uploadConfig
    //     );

    //     /* 记录附件信息 */
    //     if ($info) {
    //         $return['data'] = $info;

    //         $rs['original'] = $info['name'];
    //         $rs['state'] = 'SUCCESS';
    //         $rs['url'] =  strpos($info['savepath'], 'http://') === false ?  __ROOT__.$info['savepath'].$info['savename']:$info['savepath'];
    //         $rs['size'] = $info['size'];
    //         $rs['title'] = $info['savename'];


    //         if ($type == 'ajax') {
    //             echo json_encode($rs);
    //             exit;
    //         } else {
    //             echo json_encode($rs);
    //             exit;
    //         }

    //     } else {
    //         $return['status'] = 0;
    //         $return['info'] = $File->getError();
    //     }

    //     /* 返回JSON数据 */
    //     return json($return);
    // }

    /**
     * 上传头像
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function uploadAvatar(){

        $uid = input('param.uid',0,'intval');

        $controller = controller('common/Upload');
        $return = $controller->uploadAvatar($uid);

        return json($return);
    }

    /**
     * Base64方式上传
     * @param  string $post_field [description]
     * @return [type]             [description]
     */
    public function uploadPictureBase64($post_field='data')
    {
        $upload_type = 'picture';
        $path_type   = 'picture';
        $controller = new Upload;
        $return = $controller->uploadRemoteFile($post_field,$upload_type,$path_type);
        return json($return);

    }

    /* 下载文件 */
    public function download($id = null){
        if(empty($id) || !is_numeric($id)){
            $this->error('参数错误');
        }

        if(!$this->attachmentModel->download($id)){
            $this->error($logic->getError());
        }

    }
    // //实现php文件安全下载！
    // public function downloads($name){
    //     $name_tmp = explode("_",$name);
    //     $type = $name_tmp[0];
    //     $file_time = explode(".",$name_tmp[3]);
    //     $file_time = $file_time[0];
    //     $file_date = date("Y/md",$file_time);
    //     $file_dir = SITE_PATH."/data/uploads/$type/$file_date/";    

    //     if (!file_exists($file_dir.$name)){
    //         header("Content-type: text/html; charset=utf-8");
    //         echo "File not found!";
    //         exit; 
    //     } else {
    //         $file = fopen($file_dir.$name,"r"); 
    //         Header("Content-type: application/octet-stream");
    //         Header("Accept-Ranges: bytes");
    //         Header("Accept-Length: ".filesize($file_dir . $name));
    //         Header("Content-Disposition: attachment; filename=".$name);
    //         echo fread($file, filesize($file_dir.$name));
    //         fclose($file);
    //     }
    // }
    
    /**
     * 设置附件的状态
     */
    public function setStatus($model ='attachment', $script = false){
        $ids    = input('request.ids/a');
        $status = input('request.status');
        if (empty($ids)) {
            $this->error('请选择要操作的数据');
        }
        switch ($status) {
            case 'delete' :  // 删除条目
                    foreach ($ids as $key => $id) {
                        $this->delAttachment($id,false);
                    }       
                    $this->success('删除成功，不可恢复');
                break;
            default :
                parent::setStatus($model);
                break;
        }
    }
}