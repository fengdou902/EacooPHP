<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

// [ PHP版本检查 ]
header("Content-type: text/html; charset=utf-8");
if (version_compare(PHP_VERSION, '5.5', '<')) {
    die('PHP版本过低，最少需要PHP5.5，请升级PHP版本！');
}
// 定义应用目录
define('APP_PATH', __DIR__ . '/../apps/');

/**
 * 定义标记
 */

// 检测是否安装
is_file(APP_PATH . 'database.php') && is_file(APP_PATH . 'install.lock') ?  define('MODULE_MARK', 'front') : define('MODULE_MARK', 'install');

/**
 * 项目定义
 * 扩展类库目录
 */
define('BASE_PATH', substr($_SERVER['SCRIPT_NAME'], 0, -10));

if ($_SERVER['REQUEST_URI']==='/admin') {
    //重定向到后台地址
    header("Location:/admin.php?s=/admin/login/index"); 
    exit;
}

// 加载基础文件
require __DIR__ . '/../thinkphp/base.php';

// 支持事先使用静态方法设置Request对象和Config对象

// 执行应用并响应
Container::get('app')->path(APP_PATH)->run()->send();
