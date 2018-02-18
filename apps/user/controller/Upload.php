<?php
// 上传
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  yyyvy <76836785@qq.com>
// +----------------------------------------------------------------------
namespace app\user\controller;
use app\home\controller\Home;

class Upload extends Home{
  function _initialize()
    {
        parent::_initialize();
        //必须登录状态才能上传
        if (!$this->currentUser || !$this->currentUser['uid']) {
            $this->redirect(url('home/login/index'));
        }
    }
  /**
   * 上传头像
   * @author yyyvy <76836785@qq.com>
   * @Time 2018-1-1 00:33:49
   */
  public function uploadAvatar(){

    $uid = input('param.uid',0,'intval');
    $return = logic('common/Upload')->uploadAvatar($uid);

    return json($return);
  }
}