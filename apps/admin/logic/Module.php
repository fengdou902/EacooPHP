<?php
// 后台模块处理逻辑      
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\logic;

class Module extends Base
{
    /**
     * 获取所有安装的模块
     * @return [type] [description]
     * @date   2018-02-18
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getModules()
    {
        $default_module = [ 
                        'admin'   =>'后台模块',
                        'home'    =>'前台模块',
                        ];
        $data_list = db('modules')->where('status',1)->column('title','name');                
        $data_list = $default_module+$data_list;
        return $data_list;
    }
}