<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
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
          $data = input('post.');
          $result = $this->validate($data,[
                                        ['username','require|min:1','登录名不能为空|登录名格式不正确'],
                                        ['password','require|length:6,32','请填写密码|密码格式不正确']
                                    ]);
          if(true !== $result){
              // 验证失败 输出错误信息
              $this->error($result);
              exit;
          }

          $login = User::where(['username|email|mobile' => $data['username'],'status'=>1])->field('allow_admin')->find();

          if (!empty($login)) {
              if ($login['allow_admin']!=1) {
                $this->error('该用户不允许登录后台');
              }
           } else{
              $this->error('该用户不存在或禁用');
           }

           $captcha = new Captcha();
            if(!$captcha->check($data['captcha'],1)){
                $this->error('验证码错误');
            }
            $rememberme = $data['rememberme']==1 ? true : false;

            $result = User::login($data['username'],$data['password'], $rememberme);
            if ($result['code']==1) {
                $uid = !empty($result['data']['uid']) ? $result['data']['uid']:0;
                $this->success('登录成功！',url('admin/dashboard/index'));

            } elseif ($result['code']==0) {
                $this->error($result['msg']);
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
          cache('admin_sidebar_menus_'.is_login(),null);//清空后台菜单缓存
          cache('DB_CONFIG_DATA',null);
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
