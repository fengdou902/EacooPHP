<?php
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