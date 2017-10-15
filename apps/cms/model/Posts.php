<?php
// 内容模型       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2012-2014 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\cms\model;

use app\common\model\Base;

class Posts extends Base {

    protected $insert   = ['status' => 1];
    protected $auto     = ['publish_time'];

    protected $type       = [
        'publish_time' => 'timestamp:Y-m-d H:i',
    ];
}