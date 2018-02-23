<?php
// 上传
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\home\controller;

class Upload extends Home {

    function _initialize()
    {
        parent::_initialize();
        if (!$this->currentUser || !$this->currentUser['uid']) {
            $this->redirect(url('home/login/index'));
        }
    }

    /**
     * 文件上传
     * @return [type] [description]
     * @date   2018-02-16
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function upload() {
        $return = logic('common/Upload')->upload();
        return json($return);
    }
    
}