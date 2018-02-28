<?php
// 邮箱控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\common\model\Config as ConfigModel;

class Mailer extends Admin{

    protected $configModel;
    protected $tabList;

    function _initialize()
    {
        parent::_initialize();
        $this->configModel = new ConfigModel();
        $this->tabList = [
            'register_active' =>['title'=>'注册激活模板','href'=>url('template',['template_type'=>'register_active'])],
            'captcha_active'  =>['title'=>'邮箱验证码模板','href'=>url('template',['template_type'=>'captcha_active'])]
            ];
    }

     /**
     * 邮箱模板
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function template($template_type='register_active'){
        if (IS_POST) {
            // 提交数据
            $template_data         = $this->request->param();
            $content_data['value'] = htmlspecialchars_decode($template_data['template_content']);//模板内容单独存放
            unset($template_data['template_content']);//去除模板内容，模板内容单独放
            $data['value']         = json_encode($template_data);
            if ($data) {
        		$map = $mail_map = [];
                //第一步：保存配置值
        		switch ($template_type) {
        			case 'register_active':
    						$map['name']='mail_reg_active_template';
            				break;
	            	case 'captcha_active':
    						$map['name']='mail_captcha_template';
            				break;
	            	default:
            				$map['name']='mail_reg_active_template';
            				break;
        		}
        		if ($map) {
        				$result =$this->configModel->allowField(true)->save($data,$map);
        				if ($template_type) {
                            //第二步：保存模板内容
                            switch ($template_type) {
                                case 'register_active':
                                        $mail_map['name']='mail_reg_active_template_content';
                                        break;
                                case 'captcha_active':
                                        $mail_map['name']='mail_captcha_template_content';
                                        break;
                                default:
                                        $mail_map['name']='mail_reg_active_template_content';
                                        break;
                            }
                            $result =$this->configModel->allowField(true)->save($content_data,$mail_map);
                            cache($mail_map['name'],null);//清理配置缓存
                        }
        					cache($map['name'],null);//清理配置缓存
                            
	                    $this->success('保存成功');
	                } else {
	                    $this->error('保存失败');
	                }

                
            } else {
                $this->error('数据为空');
            }
        } else {
            $info = [];
            switch ($template_type) {
                case 'register_active':
                        $info             = config('mail_reg_active_template');//获取配置值
                        $template_content = config('mail_reg_active_template_content');//获取配置值
                    break;
                case 'captcha_active':
                        $info             = config('mail_captcha_template');//获取配置值
                        $template_content = config('mail_captcha_template_content');//获取配置值
                    break;
                default:
                        $info             = config('mail_reg_active_template');//获取配置值
                        $template_content = config('mail_reg_active_template_content');//获取配置值
                    break;
            }
            
            $info['template_content'] = $template_content;

            return builder('Form')
                    ->setMetaTitle('邮箱模板')  // 设置页面标题
            		->setTabNav($this->tabList, $template_type)  // 设置Tab按钮列表
                    ->addFormItem('active', 'radio', '邮箱激活', '',[1=>'开启',0=>'关闭'])
                    ->addFormItem('subject', 'text', '邮件主题', '')
                    //->addFormItem('template_content', 'ueditor', '邮箱激活模板', '请用http://#link#代替激活链接，#username#代替用户名',array('width'=>'100%','height'=>'260px','config'=>''))
                    ->addFormItem('template_content', 'wangeditor', '邮箱激活模板', '请用http://#link#代替激活链接，#username#代替用户名',['picturesModal'=>false,'menus'=>"'head','bold','italic','foreColor','link','emoticon','code','undo','redo'"])
                    ->setFormData($info)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
    }

}