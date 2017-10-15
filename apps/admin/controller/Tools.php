<?php
// 链接控制器
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

class Tools extends Admin{

    function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 版本处理
     * @param  string $value [description]
     * @return [type] [description]
     * @date   2017-10-12
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function version()
    {
        $return = [
            'code'=>1,
            'msg'=>'',
            'data'=>[]
        ];
        return json($return);
    }

    /**
     * 图标选择器
     * @param  string $value [description]
     * @return [type] [description]
     * @date   2017-10-13
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function iconPicker()
    {
        
        return $this->fetch();
    }
}