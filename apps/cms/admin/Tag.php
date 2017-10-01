<?php
// 分类控制器      
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.eacoo123.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
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