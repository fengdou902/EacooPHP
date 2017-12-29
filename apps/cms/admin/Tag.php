<?php
// 标签控制器      
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\cms\admin;
use app\admin\controller\Admin;
use app\admin\controller\Terms as TermsController;

use app\common\model\Terms;
use app\common\model\TermRelationships;

use app\admin\builder\Builder;

class Tag extends Admin {

    function _initialize()
    {
        parent::_initialize();
        
    }

    /**
     * 标签搜索
     * @return [type] [description]
     * @date   2017-10-01
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function search()
    {
        $data_list = Terms::all(function($query){
            $query->where('taxonomy','post_tag')->select();
        }); 
        return json($data_list);
    }    

}