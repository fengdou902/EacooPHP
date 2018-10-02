<?php
// 用户首页
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\user\controller;
use app\home\controller\Home;

use app\common\model\User as UserModel;
use app\common\logic\User as UserLogic;

class Personal extends Home{
    function _initialize()
    {
        parent::_initialize();
        $this->userModel = new UserModel;
    }

    /*
     *  Description: 个人信息
     *  By: yyyvy  <QQ:76836785>
     *  Time: 2017-12-28 10:19:55
     * */
    public function index(){
      if(is_login()) {
        return $this->fetch();
      }
      $this->error('未登录');
    }

    /*
     *  Description: 修改个人信息
     *  By: yyyvy  <QQ:76836785>
     *  Time: 2017-12-28 14:28:21
     * */
    public function profile() {
          if(!is_login()){
                $this->error('未登录');
          }
        if (IS_POST) {
          $data = input('post.');
          // 提交数据
          $data['uid']=is_login();
          $result = $this->userModel->editData($data);

          if ($result) {
            logic('common/User')->updateLoginSession(is_login());
            $this->success('提交成功', url('profile'));
          } else {
            $this->error($this->userModel->getError());
          }
        }else {
          // 获取账号信息
          $user_info = get_user_info(is_login());
          unset($user_info['password']);
          unset($user_info['auth_group']['max']);
          $this->assign('user_info',$user_info);
          return $this->fetch();
        }
      
    }
}
