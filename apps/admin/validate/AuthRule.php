<?php
// 规则验证器
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

class AuthRule extends Validate
{
    // 验证规则
    protected $rule = [
        'title' => 'require',
        'name'  => 'require',
        'depend_type'  => 'require',
        'depend_flag'  => 'require',
    ];

    protected $message = [
        'title.require'   => '标题不能为空',
        'name.require'    => '链接/规则不能为空',
        'depend_type.require'    => '请选择一个来源类型',
        'depend_flag.require'    => '请选择一个来源标识',
        // 'name.between' => '链接长度为1-80个字符',
        // 'name.unique'  => '链接已经存在',
    ];

    protected $scene=[
        'edit' => ['title','name','depend_type','depend_flag'],
    ];
}