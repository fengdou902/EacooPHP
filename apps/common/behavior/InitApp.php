<?php
// 初始化应用
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\behavior;
use think\Config as thinkConfig;
use think\Hook;

class InitApp {

	public function run(&$params) {
        define('EACOOPHP_V','1.3.1');
        define('BUILD_VERSION','201812021322');//编译版本

        $this->initConst();

        //加载模块全局函数
        if (is_file(APP_PATH . 'install.lock') && is_file(APP_PATH . 'database.php')) {
            $module_names = db('modules')->where(['status' =>1])->column('name');
            if (!empty($module_names)) {
                $module_functions_list = [];
                foreach ($module_names as $key => $module_name) {
                    $module_funcitons_file = APP_PATH.$module_name.'/functions.php';
                    if (is_file($module_funcitons_file)) {
                        $module_functions_list[] = $module_funcitons_file;
                    }
                }
                if (!empty($module_functions_list)) {
                    foreach ($module_functions_list as $key => $funfile) {
                        include($funfile);
                    }
                }
            }
        }
        if (!IS_CLI) {
            //定义模版变量
            $ec_config = [
                'view_replace_str'=>[
                                    '__ROOT__'      => BASE_PATH,
                                    '__STATIC__'    => BASE_PATH.'/static',
                                    '__PUBLIC__'    => BASE_PATH.'/static/assets',
                                    '__LIBS__'      => BASE_PATH.'/static/libs',
                                    '__ADMIN_CSS__' => BASE_PATH.'/static/admin/css',
                                    '__ADMIN_JS__'  => BASE_PATH.'/static/admin/js',
                                    '__ADMIN_IMG__' => BASE_PATH.'/static/admin/img',
                                ],
                //404页面
                'http_exception_template'    =>  [
                    // 定义404错误的重定向页面地址
                    404 =>  THEME_PATH.'404.html',
                    // 还可以定义其它的HTTP status
                    401 =>  THEME_PATH.'401.html',
                ],                
            ];
            //定义接口地址
            $ec_config['eacoo_api_url']='https://www.eacoophp.com';
            thinkConfig::set($ec_config);// 添加配置
        }
	}

    /**
     * 初始化常量
     * @author 闻子 <270988107@qq.com>
     */
    private function initConst()
    {
        $this->initSystemConst();
        
        $this->initResultConst();

        $this->initDataStatusConst();
        
        $this->initTimeConst();

        $this->initDbInfo();

        
    }

    /**
     * 初始化系统常量
     * @author 闻子 <270988107@qq.com>
     */
    private function initSystemConst()
    {
        // 定义插件目录
        define('PLUGIN_PATH', ROOT_PATH . 'plugins/');

        if (!IS_CLI) {
            //定义环境类型
            if (strpos($_SERVER["SERVER_SOFTWARE"],'nginx')!==false) {
                define('SERVER_SOFTWARE_TYPE','nginx');
            } elseif(strpos($_SERVER["SERVER_SOFTWARE"],'apache')!==false){
                define('SERVER_SOFTWARE_TYPE','apache');
            } else{
                define('SERVER_SOFTWARE_TYPE','no');
            }
        }

    }

    /**
     * 初始化数据库
     * @author 闻子 <270988107@qq.com>
     */
    private function initDbInfo()
    {
        
        $database_config = config('database');
        
        $list_rows = config('list_rows');
    
        define('DB_PREFIX', $database_config['prefix']);
        
        empty($list_rows) ? define('DB_LIST_ROWS', 10) : define('DB_LIST_ROWS', $list_rows);

    }

    /**
     * 初始化结果常量
     * @author 闻子 <270988107@qq.com>
     */
    private function initResultConst()
    {
        
        define('RESULT_SUCCESS' , 'success');
        define('RESULT_ERROR'   , 'error');
        define('RESULT_REDIRECT', 'redirect');
        define('RESULT_MESSAGE' , 'message');
        define('RESULT_URL'     , 'url');
        define('RESULT_DATA'    , 'data');

    }

    /**
     * 初始化数据状态常量
     * @author 闻子 <270988107@qq.com>
     */
    private function initDataStatusConst()
    {
        
        define('DATA_COMMON_STATUS' ,  'status');
        define('DATA_NORMAL'        ,  1);
        define('DATA_DISABLE'       ,  0);
        define('DATA_DELETE'        , -1);
        define('DATA_SUCCESS'       , 1);
        define('DATA_ERROR'         , 0);

    }

    /**
     * 初始化时间常量
     * @author 闻子 <270988107@qq.com>
     */
    private function initTimeConst()
    {
        
        define('TIME_CT_NAME' ,  'create_time');
        define('TIME_UT_NAME' ,  'update_time');
        define('TIME_NOW'     ,   time());

    }


}