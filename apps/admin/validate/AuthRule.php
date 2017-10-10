<?php
namespace app\admin\validate;

use think\Validate;

class AuthRule extends Validate
{
    // 验证规则
    protected $rule = [
        'title' => 'require',
        'name'  => 'require'
    ];

    protected $message = [
        'title.require'   => '标题不能为空',
        'name.require'    => '链接不能为空',
        // 'name.between' => '链接长度为1-80个字符',
        // 'name.unique'  => '链接已经存在',
    ];

    // protected $scene=[
    //     'edit' => ['title','name'],
    // ];
}