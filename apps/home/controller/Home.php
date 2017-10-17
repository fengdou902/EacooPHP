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
     * @param  string $validate 验证器名或者验证规则数组
     * @param  array  $data          [description]
     * @return [type]                [description]
     */
    public function validateData($data,$validate)
    {
        if (!$validate || empty($data)) return false;
        $result = $this->validate($data,$validate);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);exit;
        } 
        return true;
        
    }

    /**
     * 页面配置信息
     * @param  string $title  标题
     * @param  string $main_mark [description]
     * @param  string $mark   [description]
     * @return [type]         [description]
     */
    public function pageConfig($title='',$mark='',$extend=[])
    {
        $page_config = [
            'title'  => $title,
            'mark'   => $mark
        ];
        if(!empty($extend) && is_array($extend)) $page_config = array_merge($page_config,$extend);

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
        $module_info = db('modules')->where(['name'=>MODULE_NAME])->field('title')->find();
        $crumbs.='<li><a href="'.$this->url.'">'.$module_info['title'].'</a></li>';
        $crumbs.='<li class="active">'.$page_config['title'].'</li>';

        return '<li><a href="'.url('home/index/index').'"><i class="fa fa-dashboard"></i> 首页</a></li>'.$crumbs;
    }
}
