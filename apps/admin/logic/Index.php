<?php
// 框架逻辑层
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\logic;

use eacoo\EacooAccredit;

use think\Cache;

class Index extends AdminLogic {

    protected function initialize()
    {
        parent::initialize();
    }

    /**
     * 获取侧边栏菜单
     * @return [type] [description]
     */
    public function getAdminSidebarMenu()
    {
        try {
            $uid = is_login();
            if (!$uid) {
                throw new \Exception("暂未登录", 0);
                
            }
            $admin_sidebar_menus = Cache::get('admin_sidebar_menus_'.$uid);
            if (!$admin_sidebar_menus) {
                
                if(!is_administrator() && !empty($this->currentUser['auth_group'])){//如果是非超级管理员则按存储显示
                    $rules= db('auth_group')->where(['id'=>['in',array_keys($this->currentUser['auth_group'])]])->value('rules');    
                    $map_rules['id']=['in',$rules];;
                }
                $map_rules['status']=1;
                $map_rules['is_menu']=1;
                //是否开发者模式
                if (1!=config('develop_mode')) {
                    $map_rules['developer']=0;
                }
                $menu = db('auth_rule')->where($map_rules)->field(true)->order('sort asc')->select();
                if (!empty($menu)) {
                    foreach ($menu as $key => $row) {
                        $menu[$key]['url'] = eacoo_url($row['name'],[],$row['depend_type']);
                    }
                }
                $admin_sidebar_menus = list_to_tree($menu);
                Cache::set('admin_sidebar_menus_'.$uid,$admin_sidebar_menus);
            }
            return $admin_sidebar_menus;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
            
        }
        
    }
    
    /**
     * 获取顶部菜单
     * @return [type] [description]
     * @date   2018-02-15
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getAdminTopMenu()
    {
        try {
            $collect_menus = config('admin_collect_menus');
            if (!$collect_menus) {
                throw new \Exception("暂未收藏菜单", 0);
            }
            $result = [];
            foreach ($collect_menus as $key => $row) {
                $row['url']=$key;
                $result[]=$row;
            }
            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 清理缓存
     * @return [type] [description]
     * @date   2018-03-04
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function clearCache()
    {
        
        cache('admin_sidebar_menus_'.is_login(),null);//清空后台菜单缓存
        cache('DB_CONFIG_DATA',null);
        
    }

    /**
     * 获取eacoophp安装信息
     * @return [type] [description]
     * @date   2018-03-04
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getInstallAccreditInfo()
    {
        $install_lock = json_decode(file_get_contents(APP_PATH . 'install.lock'),true);
        if (!isset($install_lock['status_show_text']) || !isset($install_lock['accredit_status']) ||$install_lock['product_verion']!=EACOOPHP_V) {
            EacooAccredit::runAccredit(['access_token'=>ACCREDIT_TOKEN]);
            $install_lock = json_decode(file_get_contents(APP_PATH . 'install.lock'),true);
        }

        $product_info = $install_lock['status_show_text'];
        return $product_info;
    }


}