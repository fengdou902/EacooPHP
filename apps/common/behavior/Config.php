<?php
// 配置行为
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\behavior;
use think\Config as thinkConfig;
use app\common\model\Config as ConfigModel;
use think\Cache;
/**
 * 根据不同情况读取数据库的配置信息并与本地配置合并
 * 本行为扩展很重要会影响核心系统前后台、模块功能及模版主题使用
 * 如非必要或者并不是十分了解系统架构不推荐更改
 */
class Config {
    /**
     * 行为扩展的执行入口必须是run
     */
    public function run(&$params) {
        defined('MODULE_NAME') or define('MODULE_NAME',$params['module'][0] ? $params['module'][0]:config('default_module'));
        
        define('EACOOPHP_V','1.0.3');
        // 安装模式下直接返回
        if(defined('MODULE_NAME') && MODULE_NAME === 'install') return;
        // 当前模块模版参数配置
        $ec_config['view_replace_str'] = thinkConfig::get('view_replace_str',false);  // 先取出配置文件中定义的否则会被覆盖
        
        if (MODULE_MARK === 'admin') {
            // 如果是后台并且不是Admin模块则设置默认控制器层为Admin
            if (MODULE_NAME!=='admin' && MODULE_NAME!=='api') {
                $ec_config['url_controller_layer'] = 'admin';
            }
            $ec_config['view_replace_str']['__IMG__']    = '/static/'.MODULE_NAME.'/img';
            $ec_config['view_replace_str']['__CSS__']    = '/static/'.MODULE_NAME.'/css';
            $ec_config['view_replace_str']['__JS__']     = '/static/'.MODULE_NAME.'/js';
            $ec_config['view_replace_str']['__LIBS__']   = '/static/'.MODULE_NAME.'/libs';

        } elseif (MODULE_MARK=='front' && is_file(APP_PATH . 'install.lock')){
            // 获取当前主题的名称
            $current_theme = db('themes')->where('current',1)->value('name');
            //主题区分pc和移动端路径
            if (is_mobile()) {
                $current_theme = $current_theme."/mobile";
            } else {
                $current_theme = $current_theme.'/pc';
            }

            $current_theme_path = THEME_PATH.$current_theme.'/'; //默认主题设为当前主题
            //主题的公共资源目录
            $theme_public_path = $current_theme_path.'public/';

            if (is_dir($theme_public_path)) {
                $ec_config['theme_public']  = $theme_public_path;

                $theme_static_public_path = '/theme/'.$current_theme.'/'.'public/';
                $ec_config['view_replace_str']['__THEME_IMG__']   = $theme_static_public_path.'img';
                $ec_config['view_replace_str']['__THEME_CSS__']   = $theme_static_public_path.'css';
                $ec_config['view_replace_str']['__THEME_JS__']    = $theme_static_public_path.'js';
                $ec_config['view_replace_str']['__THEME_LIBS__']  = $theme_static_public_path.'libs';
            }

            // 主题模块化
            $current_theme_module_path = $current_theme_path.MODULE_NAME.'/'; //当前主题模块文件夹路径

            if(is_dir($current_theme_module_path)){
                thinkConfig::get('template');
                $ec_config['template'] = config('template');
                $ec_config['template']['view_path'] = $current_theme_module_path;
                
                // 各模块自带静态资源路径
                $module_public_path = $current_theme_module_path.'public/';
                if (is_dir($module_public_path) ) {
                    $module_public_url = '/theme/'.$current_theme.'/'.MODULE_NAME.'/'.'public';//资源路径url

                    $ec_config['view_replace_str']['__IMG__']  = $module_public_url.'/img';
                    $ec_config['view_replace_str']['__CSS__']  = $module_public_url.'/css';
                    $ec_config['view_replace_str']['__JS__']   = $module_public_url.'/js';
                    $ec_config['view_replace_str']['__LIBS__'] = $module_public_url.'/libs';
                }
            }

        }

        thinkConfig::set($ec_config);// 添加配置

        // 读取数据库中的配置
       $system_config = Cache::get('DB_CONFIG_DATA',false);//数据库里的配置
        if (!$system_config && is_file(APP_PATH . 'install.lock')) {
            // 获取所有系统配置
            $system_config = ConfigModel::lists();

            // SESSION与COOKIE与前缀设置避免冲突
            //$system_config['SESSION_PREFIX'] = ENV_PRE.MODULE_MARK.'_';  // Session前缀
            //$system_config['COOKIE_PREFIX']  = ENV_PRE.MODULE_MARK.'_';  // Cookie前缀

            // 获取所有安装的模块配置
            $module_list = db('modules')->where(['status' =>1])->select();
            foreach ($module_list as $val) {
                $module_config[strtolower($val['name'].'_config')] = json_decode($val['config'], true);
                $module_config[strtolower($val['name'].'_config')]['module_name'] = $val['name'];
            }
            
            if (!empty($module_config) && is_array($module_config)) {
                // 合并模块配置
                $system_config = array_merge($system_config, $module_config);
            }

            // 加载Formbuilder扩展类型
           /* $system_config['FORM_ITEM_TYPE'] = config('FORM_ITEM_TYPE form_item_type');
            //$formbuilder_extend = explode(',', db('Hook')->getFieldByName('FormBuilderExtend', 'plugins'));
            if ($formbuilder_extend) {
                $plugin_object = db('Addon');
                foreach ($formbuilder_extend as $val) {
                    $temp = json_decode($plugin_object->getFieldByName($val, 'config'), true);
                    if ($temp['status']) {
                        $form_type[$temp['form_item_type_name']] = array($temp['form_item_type_title'], $temp['form_item_type_field']);
                        $system_config['FORM_ITEM_TYPE'] = array_merge($system_config['FORM_ITEM_TYPE'], $form_type);
                    }
                }
            }
            */
            Cache::set('DB_CONFIG_DATA', $system_config, 3600);  // 缓存配置
        }

        // 移动端不显示trace
        if (is_mobile()) {
            $system_config['app_trace']=false;
        }
        
        thinkConfig::set($system_config);  // 添加配置

    }

}
