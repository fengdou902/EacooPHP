<?php
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2016 http://www.eacoomall.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author: 心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use app\common\controller\Base;

use app\common\model\User;
use think\captcha\Captcha;
use think\Url;

class Index extends Base
{
    public function _initialize() {
        parent::_initialize();

        if (SERVER_SOFTWARE_TYPE=='nginx') {
            Url::root('/admin.php?s=');
        } else{
            Url::root('/admin.php');
        }
    }

    /**
     * 后台登录
     */
    public function login(){ 
        if(session('user_login_auth')) $this->redirect('admin/dashboard/index');

        if (IS_POST) {
           $captcha = new Captcha();
            if(!$captcha->check($this->param['captcha'],1)){
                $this->error('验证码错误');
            }
            $rememberme = $this->input('post.rememberme')==1 ? true : false;

            $user_model = new User();
            $uid        = $user_model->login($this->param['username'],$this->param['password'], $rememberme);
            if (!$uid) {
                $this->error($user_model->getError());
            } elseif (0 < $uid) {
                action_log('user_login_admin', 'user', $uid, $uid,1);
                $this->success('登录成功！',url('admin/dashboard/index'));
            } else {
                $this->logout();
            }

        } else{
            return $this->fetch('public/login');
        }
    }

    /**
     * 退出登录
     * @return [type] [description]
     */
    public function logout(){
        session(null);
        cookie(null,config('cookie.prefix'));
        $this->redirect('admin/index/login');
    }
    
    /**
     * 清理缓存
     * @return [type] [description]
     */
    public function delcache() { 
           header("Content-type: text/html; charset=utf-8");
          //清文件缓存
          $dirs = [ROOT_PATH.'runtime/'];
          @mkdir('runtime',0777,true);
          //清理缓存
          foreach($dirs as $dir) {
              $this->rmdirr($dir);
          }
          $this->success('清除缓存成功！');
     } 

     //图片验证码
    public function verify_img($id = 1){
        $captcha = new Captcha((array)config('captcha'));
        return $captcha->entry($id);
    }
    /////////////下面是处理方法
        
     public function rmdirr($dirname) {
          if (!file_exists($dirname)) {
                return false;
          }
          if (is_file($dirname) || is_link($dirname)) {
                return unlink($dirname);
          }
          $dir = dir($dirname);
          if($dir){
               while (false !== $entry = $dir->read()) {
                if ($entry == '.' || $entry == '..') {
                 continue;
                }
                //递归
                $this->rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
               }
          }
          $dir->close();
          return rmdir($dirname);
    }
}
