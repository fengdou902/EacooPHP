<?php
// 文档类型管理   
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\cms\admin;
use app\admin\controller\Admin;

class Type extends Admin {

    function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 设置类型(待定)
     * @author 
     */
    public function index() {
        if (IS_POST) {
            $data = $this->request->param();
            
        } else {
            $info = [];
            return builder('Form')
                    ->setMetaTitle('文档类型')
                    ->addFormItem('id', 'hidden', '')
                    ->addFormItem('title', 'text', '标题')
                    ->setFormData($info)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
    }

}