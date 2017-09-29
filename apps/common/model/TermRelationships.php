<?php
// 分类关系模型       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\model;

class TermRelationships extends Base {

	// 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    protected $insert = ['status' => 1];
    

}