<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.youdi365.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\widget;

use app\common\controller\Base;
use app\common\model\Attachment as AttachmentModel;

class Attachment extends Base {
    //附件管理
    protected $attachment_model;
    protected $image_extensions='gif,jpg,jpeg,bmp,png';
    protected $file_extensions='doc,docx,xls,xlsx,ppt,pptx,pdf,wps,txt,zip,rar,gz,bz2,7z';

    function _initialize()
    {
        $this->attachment_model = new AttachmentModel();
    }

    public function lists($data,$path_type='picture',$single_multi='',$param=[],$number=20)
    {
        $this->assign('field',$data);//配置相关属性
    	  $this->meta_title = isset($param['meta_title']) ? $param['meta_title']:'附件选择器';
        $this->assign('meta_title',$this->meta_title);
        $this->assign('param',$param);//赋值参数
        $this->assign('single_multi',$single_multi);//赋值参数
        $this->assign('mediaTypeList',[1=>'图像',2=>'视频',3=>'音频',4=>'文件']);//媒体类型列表
        $this->assign('path_type',$path_type);
        //根据类型筛选配置
        // if ($type=='image') {
        //     $map['ext'] = ['in',$this->image_extensions];
        // }elseif ($type=='file') {
        //     $map['ext'] = ['in',$this->file_extensions];
        // }
        $map['path_type'] = ['in',$path_type];
        $widget_show_type = config('attachment_options.widget_show_type');//附件选择器显示类型(0:所有，1:作者)
        if (intval($widget_show_type)==1) {
          $map['uid']=is_login();
        }
        $map['status']=1;
        list($file_list,$page_totalCount) = $this->attachment_model->getListByPage($map,'sort asc,create_time desc','*',$number);
        foreach ($file_list as $key => $val) {
            if ($path_type=='brand') {
                $file_list[$key]['thumb_src']=$val['src'];
            } else {
                $file_list[$key]['thumb_src']=getThumbImageById($val['id']);
            }
        }
        
        $this->assign('attachment_list_data',$file_list);//附件列表数据

        $media_cats = model('admin/terms')->getList(['taxonomy'=>'media_cat']);

        foreach ($media_cats as $key => $cat) {
            $media_cats[$key]['count'] = term_media_count($cat['term_id'],$path_type);
        }
        $this->assign('media_totalCount',$page_totalCount);//总数量
        $this->assign('media_cats',$media_cats);//获取分类数据
        $this->assign('media_data_page',$this->media_data_page($page_totalCount,$number));//分页

        $param['attachmentDaterangePicker']=1;
        $this->assign('attachmentDaterangePicker',$param['attachmentDaterangePicker']);//是否导入时间选择器
        return $this->fetch('common@widget/attachment');
    }

    /**
     * 附件分页
     * @param  int $page_totalCount 数据总数
     * @param  int $number 每页数量
     * @author 赵俊峰 <981248356@qq.com>
     */
    protected function media_data_page($page_totalCount,$number){
        $p_num=$page_totalCount/$number;

        if (($page_totalCount%$number)>0) {
            $p_num+=1;
        }
        $data_page='<ul class="pagination pagination-sm" id="ajax-more-attachment">';
        for ($i=1; $i <$p_num; $i++) { 
            $data_page.='<li><span page-id="'.$i.'">'.$i.'</span></li>';
        }
        $data_page.='<li class="media-next-page"><span class="next" page-id="2">>></span></li>';
        $data_page.='</ul>';
        return $data_page;
    }

    //分页专用工具（主要来源于其它模块调用,用户ajax，不单独使用。）
    public function paged_list($from_type,$paged=1,$cat=0,$path_type='picture',$number=20){

        if (!$from_type) return false;
        $map['path_type']=['in',$path_type];
        if ($cat>0) {
            $media_ids = db('term_relationships')->where(['term_id'=>$cat,'table'=>'attachment'])->select();
            if(count($media_ids)){
                $media_ids=array_column($media_ids,'object_id');
                //$post_ids=array_merge(array($post_ids),$post_ids);
                $map['id']=['in',$media_ids];
            }
        }
        $map['status']=1;
        $file_list = $this->attachment_model->where($map)->page($paged,$number)->select();
        $this->attachment_model->afterSelect($file_list);
        //$this->assign('attachment_list_data',$file_list);//附件列表数据
        if ($path_type=='brand') {
            $is_thumb=false;
        }else{
            $is_thumb=true;
        }
        $this->ajax_show($file_list,$is_thumb);

        // if ($path_type=='picture') {
        //   $this->paged_images($paged,$cat,$number);
        // }elseif($path_type=='attachment'){
        //   $this->paged_files($paged,$cat,$number);
        // }
    }

    // //分页专用工具（主要来源于其它模块调用,用户ajax，不单独使用。）
    // public function paged_images($paged=1,$cat=0,$number=20){
    //     $map['path_type']=['in','picture'];
    //     if ($cat>0) {
    //         $media_ids = db('term_relationships')->where(['term_id'=>$cat,'table'=>'attachment'])->select();
    //         if(count($media_ids)){
    //             $media_ids=array_column($media_ids,'object_id');
    //             //$post_ids=array_merge(array($post_ids),$post_ids);
    //             $map['id']=['in',$media_ids];
    //         }
    //     }
    //     $map['status']=1;
    //     $file_list = $this->attachment_model->where($map)->page($paged,$number)->select();
    //     $this->attachment_model->afterSelect($file_list);
    //     //$this->assign('attachment_list_data',$file_list);//附件列表数据
    //     $this->ajax_show($file_list);
    // }
    // //分页专用工具（主要来源于其它模块调用,用户ajax，不单独使用。）
    // private function paged_files($paged=1,$number=20){
    //     $map['ext']       =['in',$this->file_extensions];
    //     $map['path_type'] =['in','attachment'];
    //     $map['status']=1;
    //     $file_list = $this->attachment_model->where($map)->page($paged,$number)->select();
    //     $this->attachment_model->afterSelect($file_list);//添加扩展
    //     //$this->assign('attachment_list_data',$file_list);//附件列表数据
    //     $this->ajax_show($file_list);
    // }
    //显示数据格式
    private function ajax_show($file_list,$is_thumb=true){

        foreach ($file_list as $key => $val) {
            $thumb_src=$val['src'];
            if($is_thumb) $thumb_src = getThumbImageById($val['id']);
            echo '<li class="col-sm-6 col-md-3 col-lg-3 mb-10" data-id="'.$val['id'].'" data-src="'.$val['src'].'">
            <div class="box-style media-li">
              <div class="tc media-thumb">     
                  <img src="'.$thumb_src.'" alt="'.$val['alt'].'" style="width:100%;max-height:100%;">
              </div>
              <div class="f13 mt-5 showAttachmentInfo" data-id="'.$val['id'].'">
              <a href="" class="color-6" data-toggle="modal" data-target="#attachmentInfoModal">
                 <span class="w75 disline oh nowarp">'.$val['name'].'</span>
                    <span class="right color-icon show-media-info">
                       <i class="fa fa-info color-6"></i>
                    </span>   
                 </a>
              </div>
            </div>
            <div class="cover cancelSelectImage" data-id="'.$val['id'].'"></div>
          </li>';
        }
    }
} 