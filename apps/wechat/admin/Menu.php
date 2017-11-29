<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.eacoomall.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\wechat\admin;

class Menu extends Base {

    protected $menu_flag_data;
    function _initialize()
    {
        parent::_initialize();
        $this->menu_flag_data ='wechat_custom_menu_'.$this->wxid;
        $this->WechatObj   = get_wechat_object(1);

    }

    //菜单管理
    public function index(){
        $this->assign('meta_title','自定义菜单');
        $wx_menus = $this->get_menu();

        $this->assign('menu',$wx_menus);
        return $this->fetch();
    }

    //发布菜单
    public function publish_menu(){
        $menu_data=$this->get_menu();
        if ($menu_data) {
            $weixin_menu = [];
            $weixin_menu['button']=$menu_data;
            $res=$this->WechatObj->createMenu($weixin_menu);
            if ($res) {
                $this->success('生成菜单成功！');
            }else{
                $this->error('生成菜单失败！'.$this->WechatObj->errMsg);
            }
            
        }
    }
    /**
     * 创建保存菜单
     */
    public function create_menu() {
        if (IS_POST) {
            $menu_button = $this->input('post.button',null);
            if ($menu_button) {
                foreach ($menu_button as $key => $button) {//菜单值处理
                    if ($button["type"]=='view') {
                        $menu_button[$key]["url"]=$button["value"];
                    }elseif($button["type"]){
                        $menu_button[$key]["key"]=$button["value"];
                    }
                    $sub_button=$button["sub_button"];
                    foreach ($sub_button as $k => $sub_row) {
                        if ($sub_row["type"]=="view") {
                            $menu_button[$key]['sub_button'][$k]["url"]=$sub_row["value"];
                        }else{
                            $menu_button[$key]['sub_button'][$k]["key"]=$sub_row["value"];
                        }
                        unset($menu_button[$key]['sub_button'][$k]["value"]);
                    }
                    unset($menu_button[$key]["value"]);
                    if (!$button["type"]) {
                        unset($menu_button[$key]["type"]);
                    }
                }
                unset($sub_button);
                cache($this->menu_flag_data, $menu_button, 3600);
                $this->success('创建菜单成功！');
            }
        }
        
    }
    //获取菜单
    public function get_menu() {
        if (cache($this->menu_flag_data)) {
            $menu = cache($this->menu_flag_data);
        } else {
            $wx_menus = $this->WechatObj->getMenu();
            $menu     = $wx_menus['menu']['button'];
            cache($this->menu_flag_data, $menu, 3600);
        }
        return $menu;
    }
    
    //获取微信端菜单
    public function syc_get_menu() {
        $wx_menus = $this->WechatObj->getMenu();
        $menu     = $wx_menus['menu']['button'];
        cache($this->menu_flag_data, $menu, 3600);
        $this->success('获取菜单成功！');
    }
    
    // 删除菜单
    public function delete_menu() {
        $result =$this->WechatObj->deleteMenu();
        if ($result === true) {
            cache($this->menu_flag_data, null);
            $return['errcode'] = 0;
            $return['errmsg'] = '删除菜单成功';
            $this->success($return['errmsg']);
        } else {
            $return['errcode'] = 1007;
            $return['errmsg']  = '删除菜单失败，错误说明：'.$result;
            $this->error($return['errmsg']);
        }
    }
}