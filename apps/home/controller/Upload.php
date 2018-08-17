<?php
// 上传
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
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
    
    /**
     * 附件信息
     * @param  integer $id [description]
     * @return [type] [description]
     * @date   2018-08-12
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function attachmentInfo($id=0)
    {
        try {
            if ($id>0) {
                $info = get_attachment_info($id);//附件信息
                $this->assign('info',$info);

                //获取分类数据
                $media_cats = model('Terms')->getList(['taxonomy'=>'media_cat']);
                $this->assign('media_cats',$media_cats);
                return $this->fetch();
            } else{
                throw new \Exception("参数不合法", 0);
                
            }
            
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}