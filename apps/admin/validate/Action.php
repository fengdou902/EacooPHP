<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class Action extends Validate
{
    // 验证规则
    protected $rule = [
        'name'  => 'require|alphaDash',
        'title' => 'require'
    ];

    protected $message = [
        'name.require'  => '行为标识不能为空！',
        'name.alphaDash'  => '行为标识格式不正确！',
        'title.require' => '行为名称不能为空！',
    ];

    protected $scene=[
        'edit' => ['name','title'],
    ];
}