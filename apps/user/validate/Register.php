<?php
// 注册验证器
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  yyyvy <76836785@qq.com>
// +----------------------------------------------------------------------
namespace app\user\validate;

use think\Validate;

class Register extends Validate
{
    // 验证规则
    protected $rule = [
      'username' => 'require|length:4,20',
      'nickname' => 'require|length:2,20',
      'email' => 'email',
      'password' => 'require|confirm|length:6,16',
    ];

    protected $message = [
      'username.require' => '账号不能为空',
      'username.length' => '账号长度要在4-20个字符之间',
      'nickname.require' => '昵称不能为空',
      'nickname.length' => '昵称长度要在2-20个字符之间',
      'email' => '邮箱格式错误',
      'password.require' => '密码不能为空',
      'password.confirm' => '两次密码不一致',
      'password.length' => '密码长度要在6-16个字符之间',

    ];

    protected $scene=[
      'register' => ['username','nickname','email','password'],
    ];
}