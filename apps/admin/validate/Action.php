<?php
namespace app\admin\validate;

use think\Validate;

class Action extends Validate
{
    // 验证规则
    protected $rule = [
        'name'  => 'require',
        'title' => 'require'
    ];

    protected $message = [
        'name.require'  => '行为标识不能为空！',
        'title.require' => '行为标题不能为空！',
    ];

    protected $scene=[
        'edit' => ['name','title'],
    ];
}