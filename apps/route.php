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

//防止路由冲突了后台
if (MODULE_MARK!=='admin') {
	//微信接口
	Route::rule('WxInterface/:wxid', 'wechat/WxInterface/index');
	Route::rule('wechat/Oauth/:wxid', 'wechat/home/wechatOauth');
	//前台插件执行入口
	Route::rule('plugin_execute', 'home/plugin/execute');
	//前台上传入口
	Route::rule('upload', 'home/Upload/upload');
}