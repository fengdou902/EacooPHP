<?php
// 登录
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
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
class Login extends Home{
    function _initialize()
    {
        parent::_initialize();
        $this->userModel = new UserModel;
    }

    /*
     *  Description: 会员登录
     *  By: yyyvy  <QQ:76836785>
     *  Time: 2017-12-28 10:19:55
     * */
    public function login(){
        if (IS_POST) {
            $data = $this->request->param();
            $result = $this->validate($data, [
              ['username', 'require|min:1', '登录名不能为空|登录名格式不正确'],
              ['password', 'require|length:6,32', '请填写密码|密码格式不正确']
            ]);
            if (true !== $result) {
                // 验证失败 输出错误信息

                $this->error($result);

                exit;
            }
            if(isset($data['rememberme'])){
                $rememberme = $data['rememberme']==1 ? true : false;
            }else{
                $rememberme = false;
            }

            $result = UserLogic::login($data['username'],$data['password'], $rememberme);
            //print_r($result);die;

            if ($result['code']==1) {

                $uid = !empty($result['data']['uid']) ? $result['data']['uid']:0;
                $this->success('登录成功！',url('/'));
            } elseif ($result['code']==0) {
                $this->error($result['msg']);
            } else {
                $this->logout();
            }
        }else{
            return $this->fetch();
        }
    }

    /*
     *  Description: 退出登录
     *  By: yyyvy  <QQ:76836785>
     *  Time: 2017-12-28 10:19:55
     * */
    public function logout(){
        session(null);
        cookie(null,config('cookie.prefix'));
        $this->redirect('/');
    }

}
