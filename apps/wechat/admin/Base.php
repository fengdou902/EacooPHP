<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.eacoomall.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\wechat\admin;
use app\admin\controller\Admin;

class Base extends Admin {

    protected $wxid;

    function _initialize()
    {
        parent::_initialize();
        $this->wxid = get_wxid(2);
        
    }

}