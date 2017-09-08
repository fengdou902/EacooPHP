<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.eacoomall.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\home\controller;

use app\common\model\User;
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
        $this->pageConfig('首页','home','index');
        
    	return $this->fetch();
    }
    
}