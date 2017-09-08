<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.eacoo123.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace Cms\Admin;
use Admin\Controller\AdminController;

use Admin\Builder\AdminFormBuilder;
use Admin\Builder\AdminListBuilder;
class TagAdmin extends AdminController {
    protected $postModel;
    protected $termsModel;
    function _initialize()
    {
        parent::_initialize();
        $this->postModel = D('Posts');
        $this->termsModel = D('Admin/terms');
        //实例化terms
        $this->post_category=$this->termsModel->getList(array('taxonomy'=>'post_category'));
        $this->post_tags=$this->termsModel->getList(array('taxonomy'=>'post_tag'));
    }
    
     /**
     * 标签搜索
     * @param  int $id id
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function search(){
        
        $tags=array();
        $tags[]=array('id'=>12,'title'=>'标签1');
        $tags[]=array('id'=>13,'title'=>'标签2');
        $this->ajaxReturn($tags);
    }   
    /**
     * 标签添加
     * @param  int $id id
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function add(){
        if (IS_POST) {
            $tag_name=I('post.title');
            $tags=array();
            $tags=array('id'=>15,'title'=>$tag_name);
            $this->ajaxReturn($tags);
        }
        
    } 
    /**
     * 设置一条或者多条数据的状态
     */
    public function setStatus($model = 'Posts') {
        $ids    = I('request.ids');
        $status = I('request.status');
        if (empty($ids)) {
            $this->error('请选择要操作的数据');
        }
        $map['id'] = array('in',$ids);
        switch ($status) {
            case 'delete' :  // 删除条目
                $map['status'] = -1;
                $exist = $this->postModel->where($map)->find();
                if ($exist) {
                    $result = $this->postModel->delete($ids);
                } else {
                    $result = true;
                }
                if ($result) {
                    foreach ($ids as $key => $id) {
                        $term_id=get_term_info($id,'term_id')['term_id'];
                        delete_post_term($id,$term_id);//删除分类
                    }       
                    $this->success('彻底删除成功');

                } else {
                    $this->error('删除失败');
                }
                break;
            default :
                parent::setStatus($model);
                break;
        }
    }
}