<?php
// 工具控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
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

}