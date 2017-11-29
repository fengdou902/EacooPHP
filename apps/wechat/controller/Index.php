<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.eacoomall.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace Weixin\Controller;
use Weixin\Controller\HomeController;

class IndexController extends HomeController {
	protected $materialModel;
    function _initialize()
    {
        parent::_initialize();
        $this->materialModel = D('Material');
    }
    /**
     * 默认方法
     */
    public function index($action='all') {
        
        $this->display();
    }
    /**
     * 详情内容
     */
    public function news_detail($id=0) {
        $news=$this->materialModel->find($id);
        $this->assign('news',$news);
        $this->display();
    }

}