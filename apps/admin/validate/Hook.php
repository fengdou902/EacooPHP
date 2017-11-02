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

class Hook extends Validate
{
    // 验证规则
    protected $rule = [
        'name'        => 'require|between:1,32|regex:^[a-zA-Z]\w{0,39}$|unique',
        'description' => 'require'
    ];

    protected $message = [
        'name.require'        => '钩子名称必须！',
        'name.between'        => '钩子名称长度为1-32个字符',
        'name.regex'          => '钩子名称由字母和下划线组成',
        'name.unique'         => '钩子名称已经存在',
        'description.require' => '钩子描述必须！',
    ];

    protected $scene=[
        'edit' => ['name','description'],
    ];
}