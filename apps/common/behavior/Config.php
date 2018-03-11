<?php
// 配置行为
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\behavior;
use think\Config as thinkConfig;
use app\common\logic\Config as ConfigLogic;
use app\common\model\Config as ConfigModel;
use think\Cache;
use think\Db;
use think\Request;

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
        
        //验证是否安装
        if ((!is_file(APP_PATH . 'install.lock') || !is_file(APP_PATH . 'database.php')) && MODULE_NAME!='install') {
            if (!IS_CLI) {
                header("location: http://".$_SERVER['HTTP_HOST'].'/install/index/index');exit;
            }
            
        }
        
        //关于请求
        $request = Request::instance();
        defined('IS_MOBILE') or define('IS_MOBILE', $request->isMobile());
        // 安装模式下直接返回
        if(defined('MODULE_NAME') && MODULE_NAME === 'install') return;
        // 当前模块模版参数配置
        $ec_config['view_replace_str'] = thinkConfig::get('view_replace_str',false);  // 先取出配置文件中定义的否则会被覆盖

        if (MODULE_MARK === 'admin') {
            // 如果是后台并且不是Admin模块则设置默认控制器层为Admin
            if (MODULE_NAME!=='admin' && MODULE_NAME!=='api' && MODULE_NAME!=='install') {
                $ec_config['url_controller_layer'] = 'admin';
                //定义后台模版view路径
                $ec_config['template'] = thinkConfig::get('template');
                $ec_config['template']['view_path']   = APP_PATH.MODULE_NAME.'/view/admin/';
            }
            
        } elseif (MODULE_MARK=='front' && is_file(APP_PATH . 'install.lock')){
            //主题区分pc和移动端
            $pc_theme = Db::name('themes')->where('current',1)->cache(true)->value('name');
            $mobile_theme = Db::name('themes')->where('current',2)->cache(true)->value('name');
            if (IS_MOBILE==true ) {
                $current_theme = !empty($mobile_theme) ? $mobile_theme : ($pc_theme ? $pc_theme : '');
            } else{
                $current_theme = !empty($pc_theme) ? $pc_theme : ($mobile_theme ? $mobile_theme : '');
            }
            
            //定义当前主题
            defined('CURRENT_THEME') or define('CURRENT_THEME', $current_theme);

            $current_theme_path = THEME_PATH.$current_theme.'/'; //默认主题设为当前主题
            //主题的公共资源目录
            $theme_public_path = $current_theme_path.'public/';

            if (is_dir($theme_public_path)) {
                $ec_config['theme_public']  = $theme_public_path;

                $theme_static_public_path = PUBLIC_RELATIVE_PATH.'/themes/'.$current_theme.'/'.'public/';
                $ec_config['view_replace_str']['__THEME_PUBLIC__']= $theme_static_public_path;
                $ec_config['view_replace_str']['__THEME_IMG__']   = $theme_static_public_path.'img';
                $ec_config['view_replace_str']['__THEME_CSS__']   = $theme_static_public_path.'css';
                $ec_config['view_replace_str']['__THEME_JS__']    = $theme_static_public_path.'js';
                $ec_config['view_replace_str']['__THEME_LIBS__']  = $theme_static_public_path.'libs';
            }

            // 模块化主题
            $current_theme_module_path = $current_theme_path.MODULE_NAME.'/'; //当前主题模块文件夹路径

            if(is_dir($current_theme_module_path)){
                
                $ec_config['template'] = thinkConfig::get('template');
                $ec_config['template']['view_path'] = $current_theme_module_path;
                
            }
            
            //插件主题化
            $action_url = $params['module'][0].'/'.$params['module'][1].'/'.$params['module'][2];
            //判断来源是否是插件执行入口
            if ($action_url=='home/plugin/execute') {
                $plugin_name = input('param._plugin');
                $theme_plugin_path = $current_theme_path.'plugins/'.$plugin_name.'/'; //当前主题插件文件夹路径
                $ec_config['template'] = thinkConfig::get('template');
                if (is_dir($theme_plugin_path)) {   
                    $ec_config['template']['view_path'] = $theme_plugin_path;
                    
                } else{
                    $ec_config['template']['view_path'] = ROOT_PATH.'plugins/'.$plugin_name.'/view/';
                }
                
            }
            //插件静态资源路径
            $ec_config['view_replace_str']['__PLUGIN_STATIC__'] = $ec_config['view_replace_str']['__STATIC__'].'/plugins';
        }

        //各模块静态资源路径
        $static_path = PUBLIC_RELATIVE_PATH.'/static/'.MODULE_NAME;
        $ec_config['view_replace_str']['__MODULE_STATIC__']    = $static_path;
        $ec_config['view_replace_str']['__MODULE_IMG__']    = $static_path.'/img';
        $ec_config['view_replace_str']['__MODULE_CSS__']    = $static_path.'/css';
        $ec_config['view_replace_str']['__MODULE_JS__']     = $static_path.'/js';
        $ec_config['view_replace_str']['__MODULE_LIBS__']   = $static_path.'/libs';

        thinkConfig::set($ec_config);// 添加配置
        // 读取数据库中的配置
       $system_config = Cache::get('DB_CONFIG_DATA',false);//数据库里的配置
       
        if (!$system_config && is_file(APP_PATH . 'install.lock')) {
            // 获取所有系统配置
            $system_config = ConfigLogic::lists();

            // SESSION与COOKIE与前缀设置避免冲突
            //$system_config['SESSION_PREFIX'] = ENV_PRE.MODULE_MARK.'_';  // Session前缀
            //$system_config['COOKIE_PREFIX']  = ENV_PRE.MODULE_MARK.'_';  // Cookie前缀

            // 获取所有安装的模块配置
            $module_list = db('modules')->where(['status' =>1])->field('name,config')->select();
            foreach ($module_list as $val) {
                $module_config[strtolower($val['name'].'_config')] = json_decode($val['config'], true);
                $module_config[strtolower($val['name'].'_config')]['module_name'] = $val['name'];
            }
            
            if (!empty($module_config) && is_array($module_config)) {
                // 合并模块配置
                $system_config = array_merge($system_config, $module_config);
            }

           // 加载Formbuilder扩展类型
            if (isset($system_config['form_item_type'])) {
                $formbuilder_extend = explode(',', db('Hooks')->where('name','FormBuilderExtend')->value('plugins'));
                if (!empty($formbuilder_extend)) {
                    $plugin_object = db('plugins');
                    foreach ($formbuilder_extend as $val) {
                        $plugin_config = json_decode($plugin_object->where('name',$val)->value('config'), true);
                        if ($plugin_config['form_item_status']) {
                            $form_type[$plugin_config['form_item_type_name']] = $plugin_config['form_item_type_title'];
                            //[$plugin_config['form_item_type_title'], $plugin_config['form_item_type_field']];
                            $system_config['form_item_type'] = array_merge($system_config['form_item_type'], $form_type);
                        }
                    }
                }
            }
            Cache::set('DB_CONFIG_DATA', $system_config, 3600);  // 缓存配置
            
        }
        $system_config['captcha'] = $this->setCaptcha();

        // 移动端不显示trace
        if (IS_MOBILE==true) {
            $system_config['app_trace']=false;
        }
        
        //头信息
        $header_info = $request->header();

        if (!empty($header_info['apiversion']) && !empty($header_info['clientfrom']) && !empty($header_info['sign'])) {
            $system_config['url_controller_layer']='api';
        }
        
        thinkConfig::set($system_config);  // 添加配置

    }

    /**
     * 设置验证码配置
     * @date   2018-03-08
     * @author 心云间、凝听 <981248356@qq.com>
     */
    private function setCaptcha()
    {
        $captcha_type = ConfigModel::where('name','captcha_type')->value('value');
        $return = thinkConfig::get('captcha');
        switch ($captcha_type) {
            case 1://中文
                $return['useZh'] = true;
                break;
            case 2://英文
                $return['codeSet'] = 'abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY';
                break;
            case 3://数字
                $return['codeSet'] = '0123456789';
                break;
            default://英文+数字
                # code...
                break;
        }
        return $return;
    }

}
