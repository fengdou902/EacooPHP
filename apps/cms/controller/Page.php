<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.eacoo123.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\cms\controller;
use app\home\controller\Home;

use app\cms\model\Posts;

class Page extends Home {

	function _initialize()
    {
        parent::_initialize();

    }

    /**
     * 默认方法
     */
    public function index() {

        $this->pageConfig('首页','index','home');
        //音频
        $audio_list = $this->content_model->where(['status' => 1,'type' => 'audio','profession' => $this->current_user['profession']])->limit(3)->select();

        $this->assign('audio_list',$audio_list);
        //视频
        $video_list = $this->content_model->where(['status' => 1,'type' => 'video','profession' => $this->current_user['profession']])->limit(8)->select();
        
        $this->assign('video_list',$video_list);

        return $this->fetch();
    }

    /**
     * 详情
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function about()
    {
        $id = 1;
        Posts::where('id',$id)->setInc('views', 1);//添加浏览次数
    	$info = Posts::get($id);
    	$this->pageConfig($info['title'],'about','page');

    	$this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * 企业介绍
     * @return [type] [description]
     */
    public function companyProfile()
    {
        $id = 1;
        Posts::where('id',$id)->setInc('views', 1);//添加浏览次数
        $info = Posts::get($id);
        $this->pageConfig($info['title'],'about','page');

        $this->assign('info',$info);
        return $this->fetch();
    }
}