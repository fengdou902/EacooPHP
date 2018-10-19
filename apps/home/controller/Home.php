<?php
// 前台基类
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\home\controller;
use app\common\controller\Base;
use think\Loader;
use think\Request;
class Home extends Base {

     function _initialize() {
        parent::_initialize();
        // 系统开关
        if (!config('toggle_web_site')) {
           $this->error('站点已经关闭，请稍后访问~');
        }

        $this->currentUser = session('user_login_auth');
        $this->assign('current_user', $this->currentUser);
        $this->assign('header_menus',logic('Nav')->getNavigationMenus('header'));
        $this->assign('users_menus',logic('Nav')->getNavigationMenus('my'));
        $this->assign('current',logic('Nav')->current());
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
    public function pageInfo($title='',$mark='',$extend=[])
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
        if (isset($page_config['crumb_parent_title'])) {
            if(!empty($page_config['crumb_parent_title'])) $crumbs.='<li><a href="'.$this->url.'">'.$page_config['crumb_parent_title'].'</a></li>';
        } else{
            $module_info = db('modules')->where(['name'=>MODULE_NAME])->field('title')->find();
            $crumbs.='<li><a href="'.$this->url.'">'.$module_info['title'].'</a></li>';
        }
        $crumbs.='<li class="active">'.$page_config['title'].'</li>';

        return '<li><a href="'.url('home/index/index').'"><i class="fa fa-dashboard"></i> 首页</a></li>'.$crumbs;
    }

    /**
     * 模版输出
     * @param  string $templateFile 模板文件名
     * @param  array  $vars         模板输出变量
     * @param  array  $replace      模板替换
     * @param  array  $config       模板参数
     * @param  array  $render       是否渲染内容
     * @return [type]               [description]
     */
    public function fetch($template='', $vars = [], $replace = [], $config = [] ,$render=false) {

        $ACTION_NAME = Request::instance()->action(true);   //处理驼峰写法模板兼容，一定要给true，不然方法名会自动变小写；

        if (!is_file($template)) {
            
            if (!$template) {
                $template_name = $this->request->controller().'/'.self::toUnderScore($this->request->action());
            } else{
                $template_name = $this->request->controller().'/'.$template;
            }

            // 当前模版文件
            $template = config('template.view_path').strtolower($template_name).'.'.config('template.view_suffix'); //当前主题模版是否存在
            if (!is_file($template)) {
                $template = APP_PATH.MODULE_NAME. '/view/'. strtolower($template_name) . '.' .config('template.view_suffix');
                if (!is_file($template)) {
                    throw new \Exception('模板不存在：'.$template, 5001);
                }
            }
            
        }

        return $this->view->fetch($template, $vars, $replace, $config, $render);
    }


    /*
     *  驼峰写法 转换大写为下划线加小写
     *  @param  string $str 需要转换的字符串
     *  @time: 2018-10-12
     *  @author: yyyvy <76836785@qq.com>
     * */
    public static function toUnderScore($str){
        $str = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $str);
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $str), "_"));
    }
}
