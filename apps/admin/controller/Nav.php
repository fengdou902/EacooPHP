<?php
// 链接控制器
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\common\model\Nav as NavModel;
use app\admin\builder\Builder;

class Nav extends Admin{

    function _initialize()
    {
        parent::_initialize();

    }

    /**
     * 管理
     * @return [type] [description]
     * @date   2017-10-01
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index()
    {
        $this->assign('meta_title','导航管理');
        return $this->fetch();
    }

}