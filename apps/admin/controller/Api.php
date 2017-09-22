<?php
// API配置控制器
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\common\model\Config;
use app\admin\builder\Builder;

class Api extends Admin{

    protected $configModel;
    protected $tabList;
    
    function _initialize()
    {
        parent::_initialize();
        $this->configModel = new Config();
        $this->tabList = [
                'smtp'         => ['title'=>'邮件设置','href'=>url('Api/index')],
                'sms'          => ['title'=>'短信设置','href'=>url('admin/plugins/config',['name'=>'Alidayu'])],
                'aliyun_oss'   => ['title'=>'阿里OSS','href'=>url('admin/Api/aliyunOss')],
                'social_login' => ['title'=>'关联登录','href'=>url('admin/plugins/config',['name'=>'SocialLogin'])],
            ];
    }

	/**
     * SMTP配置
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index(){
        if (IS_POST) {
            // 提交数据
            $smtp_data     =input('post.');
            $data['value'] =json_encode($smtp_data);
            if ($data) {
                $result = $this->configModel->save($data,['name'=>'mail_smtp']);
                if ($result) {
                    cache('DB_CONFIG_DATA',null);
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            } else {
                $this->error('数据为空');
            }
        } else {
    		$info = config('mail_smtp');//获取配置值

            Builder::run('Form')
                    ->setMetaTitle('SMTP配置')  // 设置页面标题
                    ->setTip('邮件设置用于设置您的邮件服务，不进行邮件设置，您将无法使用系统中的各种服务')
                    ->setTabNav($this->tabList,'smtp')  // 设置Tab按钮列表
                    ->addFormItem('smtp_sender', 'text', '发件人昵称', '发件人昵称')
                    ->addFormItem('smtp_address', 'email', '发件人地址', '填写邮箱地址')
                    ->addFormItem('smtp_host', 'text', 'SMTP服务器地址', 'SMTP服务器地址')
                    ->addFormItem('smtp_secure', 'radio', 'SMTP加密方式', '',['none'=>'无','ssl'=>'SSL','tls'=>'TLS'])
                    ->addFormItem('smtp_port', 'number', 'SMTP端口', '默认为25')
                    ->addFormItem('smtp_login', 'email', '发件箱帐号', '完整邮件地址')
                    ->addFormItem('smtp_password', 'password', '发件箱密码', '')
                    ->setFormData($info)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
    }

    /**
     * 阿里云OSS
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function aliyunOss(){
        if (IS_POST) {
            // 提交数据
            $aliyun_oss_data =input('post.');
            $value   =json_encode($aliyun_oss_data);
            if ($value) {
                $result = $this->configModel->where(['name'=>'aliyun_oss'])->update(['value'=>$value,'update_time'=>time()]);
                if ($result) {
                    cache('DB_CONFIG_DATA',null);
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            } else {
                $this->error('数据为空');
            }
        } else {
            $info = config('aliyun_oss');//获取配置值

            Builder::run('Form')
                    ->setMetaTitle('阿里云OSS')  // 设置页面标题
                    ->setTabNav($this->tabList,'aliyun_oss')  // 设置Tab按钮列表
                    ->addFormItem('enable', 'radio', '是否开启', '',[1=>'开启',0=>'关闭'])
                    ->addFormItem('bucket', 'text', 'Bucket名称', '')
                    ->addFormItem('access_key_id', 'text', 'AccessKeyID', '')
                    ->addFormItem('access_key_secret', 'text', 'AccessKeySecret', '')
                    ->addFormItem('root_path', 'text', '图片存储根目录', '系统上传的所有图片均将被存放在此目录下，为空则存放在OSS根目录下，默认为“images”')
                    ->addFormItem('domain', 'text', '自定义绑定域名', '')
                    ->addFormItem('endpoint', 'text', '外网地址endpoint', '')
                    ->addFormItem('style', 'repeater', '样式', '',[
                            'options'=>[
                                'name'=>['title'=>'规则名','type'=>'text','default'=>'','placeholder'=>''],
                            ]
                        ])
                    ->setFormData($info)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
    }

}