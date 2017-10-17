<?php
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

class Page extends Home {

	function _initialize()
    {
        parent::_initialize();

    }
    
    /**
     * 首页
     */
    public function index() {

        $this->pageConfig('首页','index');
        
        $map = [
            'status'=>1,
            'type'=>'post'
        ];
        $post_list = Posts::where($map)->order('sort desc,create_time desc,id desc')->paginate(15);

        $this->assign('post_list',$post_list);
        return $this->fetch();
    }
}