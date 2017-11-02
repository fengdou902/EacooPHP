<?php
// 配置
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\validate;

use think\Validate;

class Config extends Validate
{
    // 验证规则
    protected $rule = [
        'name'   => 'require',
        'title'  => 'require',
        'type'   => 'require',
        'sort'   => 'number',
        'group'  => 'require',
        'status' => 'number',
        'value'  => 'require',
    ];

    protected $message = [
        'name.require'   => '配置标识不能为空',
        'title.require'  => '标识说明不能为空',
        'sort.number'    => '排序必须为数字',
        'type.require'   => '配置类型不能为空',
        'type.number'    => '配置类型必须为数字',
        'group.require'  => '配置分组不能为空',
        'group.number'   => '配置分组必须为数字',
        'status.require' => '是否显示不得为空',
        'status.number'  => '是否显示必须为数字',
        'value.require'  => '配置值不能为空',
    ];

    protected $scene=[
        'edit' => ['name','title','sort','type','group','status','value','remark'],
    ];
}