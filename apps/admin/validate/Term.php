<?php
namespace app\admin\validate;

use think\Validate;

class Term extends Validate
{
    // 验证规则
    protected $rule = [
        'name'        => 'require',
        'taxonomy' => 'require'
    ];

    protected $message = [
        'name.require'        => '分类名称必填！',
        'taxonomy.require' => '分类类型必填！',
    ];

    protected $scene=[
        'edit' => ['name','taxonomy'],
    ];
}