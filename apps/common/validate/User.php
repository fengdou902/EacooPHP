<?php
namespace app\common\validate;

use think\Validate;

class User extends Validate
{
    // 验证规则
    protected $rule = [
        'nickname'   => 'require',
        'username'   => 'require|between:1,32|unique|regex:^(?!_)(?!\d)(?!.*?_$)[\w]+$',
        'password'   => 'require|between:6,30|regex:(?!^(\d+|[a-zA-Z]+|[~!@#$%^&*()_+{}:"<>?\-=[\];\',.\/]+)$)^[\w~!@#$%^&*()_+{}:"<>?\-=[\];\',.\/]+$',
        'repassword' => 'require|confirm:password',
        'email'      => 'email|between:1,32|unique',
        'mobile'     => 'require|between:3,32|unique'
    ];

    protected $message = [
        'nickname.require'   => '昵称不能为空',
        'username.require'   => '请填写用户名',
        'username.between'   => '用户名长度为1-32个字符',
        'username.unique'    => '用户名被占用',
        'username.regex'     => '用户名只可含有数字、字母、下划线且不以下划线开头结尾，不以数字开头！',
        'password.require'   => '请填写密码',
        'password.between'   => '密码长度为6-30位',
        'password.regex'     => '密码至少由数字、字符、特殊字符三种中的两种组成',
        'repassword.require' =>'请填写重复密码',
        'repassword.confirm' =>'两次输入的密码不一致',
        'email.email'        => '邮箱格式不正确',
        'email.between'      => '邮箱长度为1-32个字符',
        'email.unique'       => '邮箱被占用',
        'mobile.between'     => '用户名长度为3-32个字符',
        'mobile.unique'      => '手机号被占用',
    ];

    protected $scene=[
        'edit' => ['nickname','username','email','mobile'=>'between|unique'],
    ];
}