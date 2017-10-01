<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.eacoo123.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\home\controller;
use app\common\controller\Base;
use think\Loader;
class Home extends Base {

     function _initialize() {
        parent::_initialize();
        // 系统开关
        if (!config('toggle_web_site')) {
           $this->error('站点已经关闭，请稍后访问~');
        }

        $this->currentUser = session('user_login_auth');
        $this->assign('current_user', $this->currentUser);
        
        $this->assign('_theme_public_', config('theme_public'));  // 页面公共继承模版
        $this->assign('_theme_public_layout', config('theme_public').'layout.html');  // 页面公共继承模版
    }

    /**
     * 验证数据
     * @param  string $validate_name [description]
     * @param  array  $data          [description]
     * @param  string $scene 场景标识
     * @return [type]                [description]
     */
    // public function validateData($validate_name='',$data=[],$scene='')
    // {
    //     if (!$validate_name || empty($data)) return false;
    //     $validate = Loader::validate($validate_name);
    //     if ($scene) {
    //         $validate->scene($scene);
    //     }
    //     if(!$validate->check($data)){
    //         $this->error($validate->getError());
    //     }
    //     return true;
    // }

    /**
     * 页面配置信息
     * @param  string $title  标题
     * @param  string $main_mark [description]
     * @param  string $mark   [description]
     * @return [type]         [description]
     */
    public function pageConfig($title='',$mark='',$main_mark='',$extend=[])
    {
        $page_config = [
            'title'  => $title,
            'main_mark' => $main_mark,
            'mark'   => $mark
        ];
        $page_config = array_merge($page_config,$extend);

        //添加面包屑导航数据
        $page_config['breadcrumbs'] = $this->breadCrumbs($page_config);
        $this->assign('page_config',$page_config);
    }

    /**
     * 面包屑导航
     * @param  array  $page_config [description]
     * @return [type]              [description]
     */
    protected function breadCrumbs($page_config = [])
    {
        $crumbs = '';
        switch ($page_config['main_mark']) {
            case 'qa':
                $crumbs.=' » <a href="/wenda">讨论社区</a>';
                if ($page_config['mark']!='question_list') {
                    $crumbs.=' » '.$page_config['title'];
                }
                
                break;
            case 'usercenter':
                $crumbs.=' » <a href="/profile">个人中心</a>';
                $crumbs.=' » '.$page_config['title'];
                break;
            default:
                $crumbs.=' » '.$page_config['title'];
                break;
        }
        return '<a href="'.url('home/index/index').'" class="color-white">首页</a>'.$crumbs;
    }
}
