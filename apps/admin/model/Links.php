<?php
// 链接模型       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use app\common\model\Base;

class Links extends Base {
    
    protected $insert = ['status'=>1];
    /**
     * 链接类型
     */
    public function link_type($id) {
        $list['1'] = '友情链接';
        $list['2'] = '合作伙伴';
        return $id ? $list[$id] : $list;
    }

}
