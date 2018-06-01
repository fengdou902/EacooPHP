<?php
// CMS逻辑层基类
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\cms\logic;

use think\Model;

class Base extends Model {

	protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 获取后台TabList
     * @return [type] [description]
     * @date   2018-02-22
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getBuilderTab($from='document')
    {
    	if ($from=='document') {
    		$tab_list= [
	            'index'         =>['title'=>'文档管理','href'=>url('Document/index')],
	            'post_category' =>['title'=>'文档分类','href'=>url('Category/index')],
	            'post_tag'      =>['title'=>'标签','href'=>url('Category/index',['taxonomy'=>'post_tag'])],
	        ];
    	} elseif($from=='post'){
    		$tab_list= [
	            'index'         =>['title'=>'文章管理','href'=>url('Posts/index')],
	            'post_category' =>['title'=>'文章分类','href'=>url('Category/index')],
	            'post_tag'      =>['title'=>'标签','href'=>url('Category/index',['taxonomy'=>'post_tag'])],
	        ];
    	}
    	
        return $tab_list;
    }
}