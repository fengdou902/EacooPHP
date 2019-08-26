<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use eacoo\EacooAccredit;

class Index extends Admin
{

    /**
     * 首页
     * @return mixed [type] [description]
     * @date   2018-02-05
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index()
    {
        $this->assign('meta_title','首页');
        $this->assign('current_message_count',0);//当前消息数量

        $install_lock = json_decode(file_get_contents(APP_PATH . 'install.lock'),true);
        $this->assign('accredit_status',$install_lock['accredit_status']);
        return $this->fetch();
    }

    /**
     * 清理缓存
     * @return [type] [description]
     */
    public function delCache() { 
        //防止认证信息被清理
        $eacoo_identification = cache('eacoo_identification');
        header("Content-type: text/html; charset=utf-8");
        //清文件缓存
        $dirs = [ROOT_PATH.'runtime/'];
        @mkdir('runtime',0777,true);
        //清理缓存
        foreach($dirs as $dir) {
            rmdirs($dir);
        }
        //清理缓存
        logic('index')->clearCache();
        //防止认证信息被清理
        cache('eacoo_identification',$eacoo_identification);
        $this->success('清除缓存成功！');
    }

    /**
     * 刷新授权信息
     * @return void [type] [description]
     * @date   2018-03-04
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function refreshAccreditInfo()
    {
        EacooAccredit::runAccredit(['access_token'=>ACCREDIT_TOKEN]);
        $this->success('刷新成功');
    }

    /**
     * 获取侧边栏菜单
     * @return [type] [description]
     * @date   2018-02-12
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getSidebarMenus()
    {
        try {
            $default_header_menu_module = cookie('default_header_menu_module');
            if (empty($default_header_menu_module)) {
                $default_header_menu_module = 'admin';
            }
            $position = input('param.position',$default_header_menu_module);
            $result = logic('index')->getAdminSidebarMenu($position);
            cookie('default_header_menu_module',$position);
            return json(['code'=>1,'msg'=>'获取侧边栏菜单成功','data'=>$result]);
        } catch (\Exception $e) {
            return json(['code'=>$e->getCode(),'msg'=>$e->getMessage(),'data'=>[]]);
        }
    }

    /**
     * 获取顶部收藏菜单
     * @return [type] [description]
     * @date   2018-02-15
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getCollectMenus()
    {
        try {
            $admin_uid = is_admin_login();
            $result = logic('index')->getAdminCollectMenus($admin_uid);
            return json(['code'=>1,'msg'=>'获取顶部收藏菜单成功','data'=>$result]);
        } catch (\Exception $e) {
            return json(['code'=>$e->getCode(),'msg'=>$e->getMessage(),'data'=>[]]);
        }
        
    }

    /**
     * 设置顶部模块菜单
     * @date   2018-12-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function setHeaderModulesMenus()
    {
        try {
            $result = logic('index')->getModuleMenus();
            return json(['code'=>1,'msg'=>'获取顶部模块菜单成功','data'=>$result]);
        } catch (\Exception $e) {
            return json(['code'=>$e->getCode(),'msg'=>$e->getMessage(),'data'=>[]]);
        }
    }
}
