<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\home\controller;

class Index extends Home {

    function _initialize()
    {
        parent::_initialize();

    }
    
    /**
     * 首页
     * @return [type] [description]
     */
    public function index()
    {
<<<<<<< HEAD
        $this->pageConfig('首页','home');
=======
        $this->pageInfo('首页','home');
        
>>>>>>> 5f0e7b99aaa6e7046af478f4e5c34ba44065d304
    	return $this->fetch();
    }
    
}