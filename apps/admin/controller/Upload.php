<?php
// 上传
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\common\logic\Upload as UploadLogic;
use app\common\model\Attachment as AttachmentModel;
use app\common\model\TermRelationships as TermRelationshipsModel;

class Upload extends Admin {

    protected $attachmentModel;
    
    function _initialize()
    {
        parent::_initialize();
        $this->attachmentModel  = new AttachmentModel();
    }

    /* 文件上传 */
    public function upload() {
        $controller = new UploadLogic;
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
        $controller = new UploadLogic;
        $return = $controller->uploadRemoteFile();
        return json($return);
    }

    /**
     * 上传头像
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function uploadAvatar(){

        $uid = input('param.uid',0,'intval');

        $controller = new UploadLogic;
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

    //附件Widget分页列表(必须有Widget扩展)
    public function attachmentWidgetList($from_type = null, $paged = 1, $cat = 0, $path_type = 'picture'){
        widget('common/Attachment/paged_list',[$from_type,$paged,$cat,$path_type]); 
    }   
    
    /**
     * 获取builder多图上传列表
     * @param  [type] $ids [description]
     * @param  boolean $nolayout [description]
     * @param  string $path_type [description]
     * @return [type] [description]
     * @date   2018-02-28
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getViewAttachmentHtml($ids,$nolayout=false,$path_type='picture'){
        $map['id']     = ['in',$ids];
        $map['status'] = 1;
        $file_list = $this->attachmentModel->getList($map);
        foreach ($file_list as $key => $data) {
            $data['url'] = cdn_img_url($data['path']);
            if ($nolayout==1) {
                echo '<img class="" src="'.$data['url'].'" data-id="'.$data['id'].'">';
            } else{
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
        $data = $this->request->param();
        $path_type = !empty($data['path_type']) ? $data['path_type'] : 'picture';

        $map['path_type'] = ['in',$path_type];

        $widget_show_type = config('attachment_options.widget_show_type');//附件选择器显示类型(0:所有，1:作者)
        if (intval($widget_show_type)==1) {
          $map['uid'] = is_login();
        }
        //分类
        if (!empty($data['cat']) && $data['cat']>0) {
            $media_ids = TermRelationshipsModel::where(['term_id'=>$data['cat'],'table'=>'attachment'])->select();
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
        $this->assign('gettype',$data['gettype']);//赋值参数
        $this->assign('mediaTypeList',[1=>'图像',2=>'视频',3=>'音频',4=>'文件']);//媒体类型列表
        $this->assign('path_type',$path_type);

        $param['attachmentDaterangePicker']=1;
        $this->assign('attachmentDaterangePicker',$param['attachmentDaterangePicker']);//是否导入时间选择器
        $this->assign('meta_title','附件选择器');
        return $this->fetch();
    }

    /**
     * 下载文件
     * @param  [type] $id [description]
     * @return [type] [description]
     * @date   2018-02-18
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function download($id = null){
        if(empty($id) || !is_numeric($id)){
            $this->error('参数错误');
        }

        if(!$this->attachmentModel->download($id)){
            $this->error($logic->getError());
        }

    }
    
    
}