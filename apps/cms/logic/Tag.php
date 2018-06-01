<?php
// 标签逻辑    
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\cms\logic;

class Tag extends Base {

    /**
     * 获取分类
     * @return [type] [description]
     * @date   2017-10-17
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getTags()
    {
        $map = [
            'taxonomy'=>'post_tag',
        ];
        $data_list = db('terms')->where($map)->field('term_id,name,slug,pid')->limit(8)->select();
        return $data_list;
    }
    
}