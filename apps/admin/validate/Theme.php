<?php
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