<?php
// 入口
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\behavior;
use think\Config as thinkConfig;
use think\Hook;

class Entrance {

	public function run(&$params) {
        defined('PUBLIC_RELATIVE_PATH') or define('PUBLIC_RELATIVE_PATH','');
        // 定义插件目录
        define('PLUGIN_PATH', ROOT_PATH . 'plugins/');
		//定义环境类型
        if (strpos($_SERVER["SERVER_SOFTWARE"],'nginx')!==false) {
            define('SERVER_SOFTWARE_TYPE','nginx');
        } elseif(strpos($_SERVER["SERVER_SOFTWARE"],'apache')!==false){
            define('SERVER_SOFTWARE_TYPE','apache');
        } else{
            define('SERVER_SOFTWARE_TYPE','no');
        }
       	
        define('EACOOPHP_V','1.0.8');

        $ec_config = [
            'view_replace_str'=>[
                                '__ROOT__'        => BASE_PATH.PUBLIC_RELATIVE_PATH,
                                '__STATIC__'      => BASE_PATH.PUBLIC_RELATIVE_PATH.'/static',
                                '__PUBLIC__'      => BASE_PATH.PUBLIC_RELATIVE_PATH.'/static/assets',
                                '__STATIC_LIBS__' => BASE_PATH.PUBLIC_RELATIVE_PATH.'/static/libs',
                                '__ADMIN_CSS__'   => BASE_PATH.PUBLIC_RELATIVE_PATH.'/static/admin/css',
                                '__ADMIN_JS__'    => BASE_PATH.PUBLIC_RELATIVE_PATH.'/static/admin/js',
                                '__ADMIN_IMG__'   => BASE_PATH.PUBLIC_RELATIVE_PATH.'/static/admin/img',
                            ],
            //404页面
            'http_exception_template'    =>  [
                // 定义404错误的重定向页面地址
                404 =>  THEME_PATH.'404.html',
                // 还可以定义其它的HTTP status
                401 =>  THEME_PATH.'401.html',
            ],                
        ];
        $ec_config['eacoo_api_url']='http://www.eacoo123.org';
        thinkConfig::set($ec_config);// 添加配置
	}

}