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

class Index extends Admin
{

    /**
     * 首页
     * @return [type] [description]
     * @date   2018-02-05
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index()
    {
        $this->assign('meta_title','首页');
        return $this->fetch();
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
        $eacoo_identification = cache('eacoo_identification');
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
        cache('eacoo_identification',$eacoo_identification);
        $this->success('清除缓存成功！');
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
