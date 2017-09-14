<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
// [ PHP版本检查 ]
header("Content-type: text/html; charset=utf-8");
if (version_compare(PHP_VERSION, '5.5', '<')) {
    die('PHP版本过低，最少需要PHP5.5，请升级PHP版本！');
}
// 定义应用目录
define('APP_PATH', __DIR__ . '/../apps/');
// 定义资源目录
define('PUBLIC_PATH', __DIR__ . '/');
//主题目录
define('THEME_PATH',__DIR__ . '/theme/');
/**
 * 定义后台标记
 */
define('MODULE_MARK','admin');
define('EACOOPHP_V','1.0.1');
//define('APP_HOOK',true);
//定义环境类型
if (strpos($_SERVER["SERVER_SOFTWARE"],'nginx')!==false) {
	define('SERVER_SOFTWARE_TYPE','nginx');
} elseif(strpos($_SERVER["SERVER_SOFTWARE"],'apache')!==false){
	define('SERVER_SOFTWARE_TYPE','apache');
} else{
	define('SERVER_SOFTWARE_TYPE','no');
}
/**
 * 项目定义
 * 扩展类库目录
 */
define('BASE_PATH', substr($_SERVER['SCRIPT_NAME'], 0, -10));
// define('ROOT_PATH', dirname(APP_PATH) . DIRECTORY_SEPARATOR);
// define('EXTEND_PATH', ROOT_PATH . 'core' . DIRECTORY_SEPARATOR . 'extend' . DIRECTORY_SEPARATOR);
// define('VENDOR_PATH', ROOT_PATH . 'core' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR);
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
// // 读取自动生成定义文件
// $build = include __DIR__ .'/build.php';
// // 运行自动生成
// \think\Build::run($build);