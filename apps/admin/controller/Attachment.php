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

use app\common\model\Attachment as AttachmentModel;
use app\admin\logic\Attachment as AttachmentLogic;//引入逻辑层
use app\common\model\TermRelationships as TermRelationshipsModel;

class Attachment extends Admin {

    protected $attachmentModel;
    
    function _initialize()
    {
        parent::_initialize();
        $this->attachmentModel  = new AttachmentModel();
    }

    /**
     * 附件首页
     * @param  integer $term_id [description]
     * @return [type] [description]
     * @date   2017-06-11
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index($term_id=0){

        $this->assign(['meta_title'=>'附件管理','show_box_header'=>1]);
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
        $keyword = input('param.keyword');
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
            $media_ids = TermRelationshipsModel::where(['term_id'=>$term_id,'table'=>'attachment'])->column('object_id');
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
        $this->assign('table_data_page',$file_list->render());
        $this->assign('path_type',$path_type);

        //获取分类数据
        $media_cats = model('terms')->getList(['taxonomy'=>'media_cat']);
        $this->assign('media_cats',$media_cats);

        //设置tab_nav
        $tab_list = AttachmentLogic::getTabList();
        $this->assign('tab_nav',['tab_list'=>$tab_list,'current'=>'index']);
    	return $this->fetch();
    }

    /**
     * 获取附件信息
     * @param  int $id id
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function info($id=0){
        try {
            if ($id>0) {
                $info = get_attachment_info($id);//附件信息
                $this->assign('info',$info);

                //获取分类数据
                $media_cats = model('terms')->getList(['taxonomy'=>'media_cat']);
                $this->assign('media_cats',$media_cats);
                return $this->fetch();
            } else{
                throw new \Exception("参数不合法", 0);
                
            }
            
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 编辑附件信息
     * @param  int $id id
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function edit(){
        if (IS_POST) {
            $id          = input('post.id',0,'intval');
            $term_id     = input('post.term_id',false,'intval');
            $data = [
                'alt'=>input('post.alt','')
            ];
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
     * @return [type] [description]
     * @date   2018-01-11
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function moveCategory() {
        if (IS_POST) {
            $ids      = input('param.ids');
            $from_cid = input('param.from_cid');
            $to_cid   = input('param.to_cid');
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
    public function del($id=0,$is_return=true){ 
        $return = get_attachment_info($id);//附件信息 
        cache('Attachment_'.$id,null);//删除缓存信息
        if ($return['location']=='local') {
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
        
        $map =[
            'taxonomy'=>'media_cat'
        ];
        
        list($data_list,$total) = model('common/Terms')->search('name,slug')->getListByPage($map,true,'sort desc,create_time desc',15);
        if (!empty($data_list)) {
            foreach ($data_list as $key => &$row) {
                $row['object_count'] = logic('common/Terms')->termRelationCount($row['term_id'],'attachment');
            }
        }

        //获取tab_list
        $tab_list = AttachmentLogic::getTabList();
        return builder('List')
                    ->setMetaTitle('附件分类')
                    ->setTabNav($tab_list,'category')  // 设置页面Tab导航
                    ->addTopButton('addnew',['href'=>url('categoryEdit')])  // 添加新增按钮
                    ->addTopButton('resume')  // 添加启用按钮
                    ->addTopButton('forbid')  // 添加禁用按钮
                    ->addTopButton('recycle') //添加回收按钮
                    ->setSearch()
                    ->keyListItem('term_id', 'ID')
                    ->keyListItem('name', '名称','link',url('index',['term_id'=>'__data_id__']))//约定分类对象
                    ->keyListItem('slug', '别名')
                    ->keyListItem('parent', '父分类')
                    ->keyListItem('seo_description', '描述')
                    ->keyListItem('object_count', '对象数')
                    ->keyListItem('status', '状态', 'status')
                    ->keyListItem('right_button', '操作', 'btn')
                    ->setListPrimaryKey('term_id')
                    ->setListData($data_list)    // 数据列表
                    ->setListPage($total,15) // 数据列表分页
                    ->addRightButton('edit',['href'=>url('categoryEdit',['term_id'=>'__data_id__'])])// 添加编辑按钮
                    ->addRightButton('recycle')// 添加删除按钮
                    ->fetch();

    }

    /**
     * 附件分类编辑
     * @param  int $term_id term_id
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function CategoryEdit($term_id = 0){
        
        $tab_obj=[
            'tab_list'=>AttachmentLogic::getTabList(),
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
        
        $tab_list = AttachmentLogic::getTabList();
        $tab_list['attachment_option']=['title'=>'设置','href'=>url('setting')];
        unset($tab_list['setting']);
        \think\Loader::action('admin/Config/attachmentOption',[$tab_list]);
    }

    /**
     * 设置附件的状态
     * @param  string $model [description]
     * @param  boolean $script [description]
     * @date   2018-01-11
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function setStatus($model ='attachment', $script = false){
        $ids    = input('param.ids/a');
        $status = input('param.status');
        if (empty($ids)) {
            $this->error('请选择要操作的数据');
        }
        switch ($status) {
            case 'delete' :  // 删除条目
                    foreach ($ids as $key => $id) {
                        $this->del($id,false);
                    }       
                    $this->success('删除成功，不可恢复');
                break;
            default :
                parent::setStatus($model);
                break;
        }
    }
}