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

class Theme extends Validate
{
    // 验证规则
    protected $rule = [
        'name'        => 'require|unique',
        'version' => 'require'
    ];

    protected $message = [
        'name.require'        => '主题标识不能为空',
        'name.unique'         => '主题标识已经存在',
        'version.require' => '主题版本不能为空',
    ];

}