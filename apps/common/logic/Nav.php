<?php
// 导航逻辑    
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\logic;

use eacoo\Tree;
use think\Request;

class Nav extends Base {

	/**
	 * 获取前台导航
	 * @param  string $position 位置
	 * @return [type] [description]
	 * @date   2018-01-18
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public static function getNavigationMenus($position='header')
	{
		//先从缓存中获取
		$menus = cache("front_{$position}_navs");
		if (!$menus) {
			$menus = self::where('position',$position)->field('id,title,value,pid,icon,target,depend_type,depend_flag')->order('sort asc')->select();
			if (!empty($menus)) {
				$menus = collection($menus)->toArray();
	            $tree_obj = new Tree;
	            $menus = $tree_obj->listToTree($menus);
	            cache("front_{$position}_navs",$menus,3600);
	        }
		}
		return $menus;
	}
}
