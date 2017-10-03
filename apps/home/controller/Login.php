<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.eacoo123.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\home\controller;
use app\common\model\User;
use think\captcha\Captcha;

class Login extends Home {

    function _initialize()
    {
        parent::_initialize();
        $this->user_model = new User();
    }
    
    /**
     * 登录
     * @return [type] [description]
     */
    public function login()
    {
        $this->pageConfig('登录','login','login');
        if(session('user_login_auth')) $this->redirect(url('home/usercenter/profile'));
        if (IS_POST) {
           $captcha = new Captcha();
            if(!$captcha->check($this->param['captcha'],1)){
                $this->error('验证码错误');
            }
            $rememberme = input('post.rememberme')==1 ? true : false;

            $uid = $this->user_model->login($this->param['username'],$this->param['password'], $rememberme);
            if (!$uid) {
                $this->error($this->user_model->getError());
            } elseif (0 < $uid) {
                $this->success('登录成功！','home/usercenter/profile');
            } else {
                $this->logout();
            }
        } else {
            return $this->fetch();
        }
    	
    }

    /**
     * 注册
     * @return [type] [description]
     */
    public function register()
    {
        $this->pageConfig('注册用户','register','login');
       
        return $this->fetch();
    }

    /**
     * 退出登录
     * @return [type] [description]
     */
    public function logout(){
        session(null);
        cookie(null,config('cookie.prefix'));
        $this->redirect(url('home/login/login'));
    }
    
     //图片验证码
    public function verify_img($id=1){
        $captcha = new \think\captcha\Captcha((array)config('captcha'));
        return $captcha->entry($id);
    }
}