<?php
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

class User extends Validate
{
    protected $regex = [ 'username'=>'^(?!_)(?!\d)(?!.*?_$)[\w]+$','mobile' => '/^1[3|4|5|7|8][0-9]\d{4,8}$/u'];
    // 验证规则
    protected $rule = [
        'nickname'   => 'require|chsAlphaNum',
        'username'   => 'require|length:1,32|unique:users,username,,uid|regex:username',
        'password'   => 'require|length:6,32|regex:(?!^(\d+|[a-zA-Z]+|[~!@#$%^&*()_+{}:"<>?\-=[\];\',.\/]+)$)^[\w~!@#$%^&*()_+{}:"<>?\-=[\];\',.\/]+$',
        'repassword' => 'require|confirm:password',
        'email'      => 'email|unique:users,email,,uid',
        'mobile'     => 'require|regex:mobile|unique:users,mobile,,uid'
    ];

    protected $message = [
        'nickname.require'   => '昵称不能为空',
        'nickname.chsAlphaNum' => '昵称格式不正确',
        'username.require'   => '请填写用户名',
        'username.length'    => '用户名长度为1-32个字符',
        'username.unique'    => '用户名被占用',
        'username.regex'     => '用户名只可含有数字、字母、下划线且不以下划线开头结尾，不以数字开头！',
        'password.require'   => '请填写密码',
        'password.length'    => '密码长度为6-30位',
        'password.regex'     => '密码至少由数字、字符、特殊字符三种中的两种组成',
        'repassword.require' =>'请填写重复密码',
        'repassword.confirm' =>'两次输入的密码不一致',
        'email.email'        => '邮箱格式不正确',
        'email.unique'       => '邮箱被占用',
        'mobile.require'     => '请填写手机号',
        'mobile.regex'       => '手机号格式不正确',
        'mobile.unique'      => '手机号被占用',
    ];

    protected $scene=[
        'add' => ['nickname','username'=>'require|length:1,32|regex:username','email','mobile'=>'regex:mobile|unique:users,mobile,,uid'],
        'edit' => ['nickname','username'=>'require|length:1,32|regex:username','email.email','mobile'=>'regex:mobile'],
    ];
}