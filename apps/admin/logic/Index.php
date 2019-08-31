<?php
// 框架逻辑层
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2019 https://www.eacoophp.com, All rights reserved.         
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
     * @param  string $position [description]
     * @return [type] [description]
     * @date   2018-12-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getAdminSidebarMenu($position =  '')
    {
        try {
            $uid = $this->uid;
            if (!$uid) {
                throw new \Exception("暂未登录", 0);
                
            }
            // $admin_sidebar_menus = Cache::get('admin_sidebar_menus_'.$uid);
            // if (!$admin_sidebar_menus) {
                if (!$this->currentUser['auth_group']) {
                    throw new \Exception("未授权任何权限", 0);
                    
                }
                $map_rules = [];
                $map_rules['is_menu']=1;
                $map_rules['position']= !empty($position) ? $position : 'admin';
                $menu = getAdminUserAuthRule($uid, $map_rules);
                if (!empty($menu)) {
                    foreach ($menu as $key => $row) {
                        $menu[$key]['url'] = eacoo_url($row['name'],[],$row['depend_type']);
                    }
                }
                $admin_sidebar_menus = list_to_tree($menu);
                Cache::set('admin_sidebar_menus_'.$uid,$admin_sidebar_menus);
            //}
            return $admin_sidebar_menus;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage().';File:'.$e->getFile().';'.$e->getLine(), $e->getCode());
            
        }
        
    }
    
    /**
     * 获取顶部菜单
     * @return [type] [description]
     * @date   2018-02-15
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getAdminCollectMenus($admin_uid = 0)
    {
        try {
            if ($admin_uid<=0) {
                throw new \Exception("参数不合法", 0);
            }
            $collect_menus = json_decode(cookie('admin_collect_menus'),true);
            if (!$collect_menus) {
                throw new \Exception("暂未收藏菜单", 0);
            }
            $result = [];
            foreach ($collect_menus as $key => $row) {
                $row['url'] = $key;
                $result[] = $row;
            }
            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function getModuleMenus()
    {
        $result = [];
        $default_header_menu_module = cookie('default_header_menu_module');
        $data_list = model('admin/modules')->where('status',1)->field('id,title,name,icon')->select();
        if (!empty($data_list)) {
            foreach ($data_list as $key => $row) {
                $module_name = $row['name'];
                if ($module_name=='home') {
                    continue;
                }
                if (!empty($row)) {
                    if ($module_name=='admin') {
                        $row['title']='系统';
                    }
                    //默认菜单
                    $row['default_header_menu_module']=0;
                    if ($default_header_menu_module==$module_name) {
                        $row['default_header_menu_module']=1;
                    }
                    $row['icon'] = !empty($row['icon']) ? $row['icon'] : 'fa fa-circle-o ';

                    //判断是否有对应规则
                    $rules = getAdminUserAuthRule($this->adminUid,['is_menu'=>1,'position'=>$module_name]);
                    if(!empty($rules)) $result[] = $row;
                }
                
            }
        }
        return $result;
    }

    /**
     * 清理缓存
     * @return [type] [description]
     * @date   2018-03-04
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function clearCache()
    {
        cache('admin_sidebar_menus_'.is_admin_login(),null);//清空后台菜单缓存
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