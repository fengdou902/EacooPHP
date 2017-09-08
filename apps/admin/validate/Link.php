<?php
namespace app\admin\validate;

use think\Validate;

class Hook extends Validate
{
    // 验证规则
    protected $rule = [
        'title'      => 'require|between:1,80|unique',
        'url'        => 'require|between:1,80|unique'
    ];

    protected $message = [
        'title.require'      => '标题不能为空',
        'title.between'      => '标题长度为1-80个字符',
        'title.unique'       => '标题已经存在',
        'url.require'        => '链接不能为空',
        'url.between'        => '链接长度为1-25个字符',
        'url.unique'         => '链接已经存在',
    ];

    protected $scene=[
        'edit' => ['title','url'],
    ];
}