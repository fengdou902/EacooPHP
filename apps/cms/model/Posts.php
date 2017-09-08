<?php
// 文章模型       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2012-2014 http://www.youpcmf.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\cms\model;

use app\common\model\Base;

class Posts extends Base{

    protected $insert     = ['status' => 1,'create_time'];
    protected $auto     = ['update_time'];

    protected function setCreateTimeAttr($value, $data)
    {
        return time();
    }
    
    protected function setUpdateTimeAttr($value, $data)
    {
        return time();
    }

}