<?php
// cms前台
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.eacoo123.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\cms\controller;
use app\home\controller\Home;

use app\cms\model\Posts;
use app\cms\model\Category;
use app\cms\model\Tag;

class Index extends Home {

	function _initialize()
    {
        parent::_initialize();

        $this->assign('category_list',Category::getCategories());
        $this->assign('tag_list',Tag::getTags());
    }   

    /**
     * 首页
     */
    public function index() {

        $this->pageConfig('首页','index');
        
        $map = [
            'status' =>1,
            'type'   =>'post'
        ];
        //分类筛选
        $cat_id = input('param.cat_id');
        if ($cat_id>0) {
            $cat_post_ids = db('term_relationships')->where(['term_id'=>$cat_id])->column('object_id');
            if(!empty($cat_post_ids)) $map['id']=['in',$cat_post_ids];
        }
        //标签筛选
        $tag_id = input('param.tag_id');
        if ($tag_id>0) {
            $tag_post_ids = db('term_relationships')->where(['term_id'=>$tag_id])->column('object_id');
            if(!empty($tag_post_ids)){
                $map['id']=['in',$tag_post_ids];
            } elseif (empty($map['id'])) {
                $map['id']='';
            }
        }
        if (!empty($cat_post_ids) && !empty($tag_post_ids)) {
            $ids = array_unique(array_merge($cat_post_ids,$tag_post_ids));
            $map['id']=['in',$ids];
        }
        $post_list = Posts::where($map)->order('sort desc,create_time desc,id desc')->paginate(15);

        $this->assign('post_list',$post_list);

        return $this->fetch();
    }

    /**
     * 获取详情
     * @param  integer $id [description]
     * @return [type] [description]
     * @date   2017-10-16
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function detail($id=0)
    {
        $info = Posts::get($id);

        $this->pageConfig($info['title'],'detail');
        $this->assign('info',$info);

        return $this->fetch();
    }

    /**
     * 详情
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function about()
    {
        $id = 1;
        Posts::where('id',$id)->setInc('views', 1);//添加浏览次数
    	$info = Posts::get($id);
    	$this->pageConfig($info['title'],'about');

    	$this->assign('info',$info);
        return $this->fetch();
    }

}