<?php
// 附件逻辑
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\logic;

use think\File;
class Attachment extends AdminLogic {

	/**
	 * 获取tab_list
	 * @param  string $current [description]
	 * @return [type] [description]
	 * @date   2018-03-06
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public static function getTabList($current='')
	{
		$tab_list = [
                'index'    =>['title'=>'附件管理','href'=>url('index')],
                'category' =>['title'=>'附件分类','href'=>url('category')],
                'setting'  =>['title'=>'设置','href'=>url('setting')]
            ];
        return $tab_list;
	}
}
