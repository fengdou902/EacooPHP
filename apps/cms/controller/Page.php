<?php
// 页面
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\cms\controller;
use app\home\controller\Home;

use app\cms\model\Posts as PostsModel;

class Page extends Home {

	function _initialize()
    {
        parent::_initialize();

    }
    
    /**
     * 首页
     */
    public function index() {

        $this->pageInfo('首页','index');
        
        $map = [
            'status'=>1,
            'type'=>'page'
        ];
        $post_list = PostsModel::where($map)->order('sort desc,create_time desc,id desc')->paginate(15);

        $this->assign('post_list',$post_list);
        return $this->fetch();
    }

    /**
     * 关于
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function about()
    {
        $id = 1;
        PostsModel::where('id',$id)->setInc('views', 1);//添加浏览次数
        $info = PostsModel::get($id);
        $this->pageInfo($info['title'],'about');

        $this->assign('info',$info);
        return $this->fetch();
    }
}