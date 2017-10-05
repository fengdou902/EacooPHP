<?php
namespace app\install\validate;

use think\Validate;

class InstallConfig extends Validate
{
    // 验证规则
    protected $rule = [
        //管理员信息验证规则
        'username'             => 'require|between:1,32|regex:^(?!_)(?!\d)(?!.*?_$)[\w]+$',
        'password'             => 'require|between:6,30',
        'repassword'           => 'require|confirm:password',
        'email'                => 'require|email|between:1,32',
        //网站信息验证规则
        'web_site_title'       => 'require',
        'index_url'            => 'require',
        'web_site_description' => 'require',
        'web_site_keyword'     => 'require',
        //数据库验证规则
        'type'                 => 'require',
        'hostname'             => 'require',
        'database'             => 'require',
        'username'             => 'require',
        'password'             => 'require',
        'hostport'             => 'require|number|gt:0',
        'prefix'               => 'require',
    ];

    protected $message = [
        'username.require'   => '请填写用户名',
        'username.between'   => '用户名长度为1-32个字符',
        'username.regex'     => '用户名只可含有数字、字母、下划线且不以下划线开头结尾，不以数字开头！',
        'password.require'   => '请填写密码',
        'password.between'   => '密码长度为6-30位',
        'repassword.require' =>'请填写重复密码',
        'repassword.confirm' =>'两次输入的密码不一致',
        'email.require'      => '请填写邮箱',
        'email.email'        => '邮箱格式不正确',
        'email.between'      => '邮箱长度为1-32个字符',

        'web_site_title.require'   => '请填写完整网站信息',
        'index_url.require'   => '请填写完整网站信息',
        'web_site_description.require'   => '请填写完整网站信息',
        'web_site_keyword.require'   => '请填写完整网站信息',

        'type.require'   => '请填写完整的数据库配置',
        'hostname.require'   => '请填写完整的数据库配置',
        'database.require'   => '请填写完整的数据库配置',
        'username.require'   => '请填写完整的数据库配置',
        'password.require'   => '请填写完整的数据库配置',
        'hostport.require'   => '请填写完整的数据库配置',
        'prefix.require'   => '请填写完整的数据库配置',

    ];

    protected $scene=[
        'admin_info' => ['username','password'=>'require|between','repassword','email'],
        'web_config' => ['web_site_title','index_url','web_site_description','web_site_keyword'],
        'db_config' => ['type','hostname','database','username','password','hostport','prefix'],
    ];
}