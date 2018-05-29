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

use think\Cache;
use think\Loader;
use think\Hook;
use think\Cookie;

class AppStore extends AdminLogic {

    protected function initialize()
    {
        parent::initialize();
    }

    /**
     * 获取应用中心Tab
     * @param  string $type [description]
     * @return [type] [description]
     * @date   2018-02-25
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getAppsCenterTabList($type='')
    {
        $module_active_class = $plugin_active_class = $theme_active_class = '';
        switch ($type) {
            case 'module':
                $module_active_class = 'active';
                break;
            case 'plugin':
                $plugin_active_class = 'active';
                break;
            case 'theme':
                $theme_active_class = 'active';
                break;
            default:
                # code...
                break;
        }
        $result = '<ul class="nav nav-tabs appcenter-tabs">
            <li class="'.$module_active_class.'"><a href="'.url('admin/modules/index').'" class="opentab color-6 f15 mr-10" tab-title="应用中心-模块" data-iframe="true" tab-name="navtab-collapse-app-modules"><img src="/static/admin/img/extension/module.svg" width="16"> 模块</a></li>
            <li class="'.$plugin_active_class.'"><a href="'.url('admin/plugins/index').'" class="opentab color-6 f15 mr-10" tab-title="应用中心-插件" data-iframe="true" tab-name="navtab-collapse-app-plugins"><img src="/static/admin/img/extension/plugin.svg" width="16"> 插件</a></li>
            <li class="'.$theme_active_class.'"><a href="'.url('admin/theme/index').'" class="opentab color-6 f15 mr-10" tab-title="应用中心-主题" data-iframe="true" tab-name="navtab-collapse-app-themes"><img src="/static/admin/img/extension/theme.svg" width="16"> 主题</a></li>
            </ul>';
        return $result;
    }

    /**
     * 获取应用中心tab
     * @param  string $type [description]
     * @return [type] [description]
     * @date   2018-02-28
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getAppStoreTabs($type='')
    {
        $module_active_class = $plugin_active_class = $theme_active_class = '';
        switch ($type) {
            case 'module':
                $module_active_class = 'active';
                break;
            case 'plugin':
                $plugin_active_class = 'active';
                break;
            case 'theme':
                $theme_active_class = 'active';
                break;
            default:
                # code...
                break;
        }
        $result = '<div class="row extension-tab">
                <div class="item-apptab col-md-3 col-sm-6 col-xs-12 '.$module_active_class.'">
                  <a href="'.url('admin/modules/index').'" class="opentab" data-iframe="true" tab-name="navtab-collapse-app-modules" data-selftabhtml=\'<img src="/static/admin/img/extension/module.svg" width="16"> 应用中心-模块\'>
                  <div class="info-box">
                    <span class="info-box-icon bg-aqua"><img src="/static/admin/img/extension/module.svg" width="64"></span>

                    <div class="info-box-content color-5">
                      <span class="info-box-text fb">模块</span>
                      <span class="info-box-number"></span>
                    </div>
                  </div>
                  </a>
                </div>
                <div class="item-apptab col-md-3 col-sm-6 col-xs-12 '.$plugin_active_class.'">
                  <a href="'.url('admin/plugins/index').'" class="opentab" data-iframe="true" tab-name="navtab-collapse-app-plugins" data-selftabhtml=\'<img src="/static/admin/img/extension/plugin.svg" width="16"> 应用中心-插件\'>
                    <div class="info-box">
                      <span class="info-box-icon bg-green"><img src="/static/admin/img/extension/plugin.svg" width="64"></span>
                      <div class="info-box-content color-5">
                        <span class="info-box-text fb">插件</span>
                        <span class="info-box-number"></span>
                      </div>
                  </div></a>
                </div>
                <div class="item-apptab col-md-3 col-sm-6 col-xs-12 '.$theme_active_class.'" >
                  <a href="'.url('admin/theme/index').'" class="opentab" data-iframe="true" tab-name="navtab-collapse-app-themes" data-selftabhtml=\'<img src="/static/admin/img/extension/theme.svg" width="16"> 应用中心-主题\'>
                  <div class="info-box">
                    <span class="info-box-icon bg-yellow color-palette"><img src="/static/admin/img/extension/theme.svg" width="64"></span>

                    <div class="info-box-content color-5">
                      <span class="info-box-text fb">主题</span>
                      <span class="info-box-number"></span>
                    </div>
                  </div>
                  </a>
                </div>
              </div>';
        return $result;
    }

    /**
     * 获取分页
     * @param  integer $paged [description]
     * @param  integer $total [description]
     * @param  [type] $page_size [description]
     * @return [type] [description]
     * @date   2018-03-09
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getPaginationHtml($paged =1,$total=0,$page_size=12)
    {
        $page_num = $total/$page_size;
        $html = '';
        if ($page_num>1) {
            $pre_pn = $paged-1;
            $html .= '<li class="page-pre"><a href="#" data-paged="'.$pre_pn.'">‹</a></li>';
            for ($i=0; $i < $page_num; $i++) { 
                $as = '';
                $pn = $i+1;
                if ($paged==$pn) {
                    $as = 'active';
                }
                $html .= '<li class="page-number '.$as.'"><a href="#" data-paged="'.$pn.'">'.$pn.'</a></li>';
            }
            $next_pn = $paged+1;
            if ($next_pn>$page_num+1) {
                $next_pn = $paged;
            }
            $html .= '<li class="page-next"><a href="#" data-paged="'.$next_pn.'">›</a></li>';
        }
        
        return $html;
    }
}