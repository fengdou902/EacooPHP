<?php 
use think\Route;
//Route::resource('address','api/Address');

/**
 * 处理插件钩子
 * @param string $hook 钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook($hook, $params = [])
{
    \Think\Hook::listen($hook, $params);
}
/**
 * 获取插件类的类名
 * @param strng $name 插件名
 */
function get_addon_class($name) {
    $class = "\\addons\\" . $name . "\\{$name}";
    return $class;
}

/**
 * 获取插件类的配置文件数组
 * @param string $name 插件名
 */
function get_addon_config($name)
{
    $class = get_addon_class($name);
    if (class_exists($class)) {
        $addon = new $class();
        return $addon->getConfig();
    } else {
        return [];
    }
}

/**
 * 插件显示内容里生成访问插件的url
 * @param string $url url
 * @param array $param 参数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function addons_url($url, $param = array()) {
    $url        = parse_url($url);
    $case       = config('url_case_insensitive');
    $addons     = $case ? parse_name($url['scheme']) : $url['scheme'];
    $controller = $case ? parse_name($url['host']) : $url['host'];
    $action     = trim($case ? strtolower($url['path']) : $url['path'], '/');

    /* 解析URL带的参数 */
    if (isset($url['query'])) {
        parse_str($url['query'], $query);
        $param = array_merge($query, $param);
    }

    /* 基础参数 */
    $params = array(
        'ad' => $addons,
        'co' => $controller,
        'ac' => $action,
    );
    $params = array_merge($params, $param); //添加额外参数
    if (strtolower(MODULE_NAME) == 'admin') {
        return url('admin/addons/execute', $params, $suffix, $domain);
    } elseif (strtolower(MODULE_NAME) == 'home') {
        return url('Home/addons/execute', $params, $suffix, $domain);
    } else{
        return url(MODULE_NAME.'/addons/execute', $params, $suffix, $domain);
    }
    //return \think\Url::build('index/addons/execute', $params);
}

/**
 * 获取插件地址
 * @param  [type] $url   [description]
 * @param  [type] $param [description]
 * @return [type]        [description]
 */
function tox_addons_url($url, $param)
{
    // 拆分URL
    $url = explode('/', $url);
    $addon = $url[0];
    $controller = $url[1];
    $action = $url[2];

    // 调用u函数
    $param['_addons'] = $addon;
    $param['_controller'] = $controller;
    $param['_action'] = $action;
    return url("Home/Addons/execute", $param);
}
