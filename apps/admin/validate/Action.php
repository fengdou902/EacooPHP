<?php
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