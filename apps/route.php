<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;

//导航
Route::rule('WxInterface/:wxid', 'wechat/WxInterface/index');
Route::rule('wechat/Oauth/:wxid', 'wechat/home/wechatOauth');
//插件执行入口
Route::rule('plugin_execute', 'home/plugin/execute');