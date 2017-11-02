<?php
// 导航控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
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