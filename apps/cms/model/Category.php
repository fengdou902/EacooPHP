<?php
// 分类模型       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2012-2014 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\cms\model;

use think\Model;

class Category extends Model {

    protected $pk = 'term_id';
    protected $name = 'terms';
    /**
     * 获取分类
     * @return [type] [description]
     * @date   2017-10-17
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getCategories()
    {
        $map = [
            'taxonomy'=>'post_category',
        ];
        $data_list = db('terms')->where($map)->field('term_id,name,slug,pid')->limit(8)->select();
        return $data_list;
    }
    
}