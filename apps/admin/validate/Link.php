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

class Link extends Validate
{
    // 验证规则
    protected $rule = [
        'title'      => 'require|length:1,80|unique:links',
        'url'        => 'require|url|length:1,180|unique:links'
    ];

    protected $message = [
        'title.require'      => '站点名称不能为空',
        'title.length'      => '站点名称长度为1-80个字符',
        'title.unique'       => '标题已经存在',
        'url.require'        => '链接不能为空',
        'url.url'        => '链接格式不正确',
        'url.length'        => '链接过长',
        'url.unique'         => '链接已经存在',
    ];

    protected $scene=[
        'edit' => ['title','url'],
    ];
}