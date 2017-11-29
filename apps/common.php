<?php 
use think\Route;
use app\admin\model\Modules;
use app\admin\model\Plugins;

/**
 * 检测是否安装某个模块
 * @param  string $name [description]
 * @return [type] [description]
 * @date   2017-09-17
 * @author 心云间、凝听 <981248356@qq.com>
 */
function check_install_module($name='')
{
    return Modules::checkInstall($name);
}

/**
 * 检测是否安装某个插件
 * @param  string $name [description]
 * @return [type] [description]
 * @date   2017-11-14
 * @author 心云间、凝听 <981248356@qq.com>
 */
function check_install_plugin($name='')
{
    return Plugins::checkInstall($name);
}

/**
 * 处理插件钩子
 * @param string $hook 钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook($hook, $params = [],$is_return =false)
{
    if ($is_return==true) {
        return \think\Hook::listen($hook, $params);exit;
    }
    \think\Hook::listen($hook, $params);
}

/**
 * 获取插件类的类名
 * @param  [type] $name [description]
 * @return [type] [description]
 * @date   2017-09-15
 * @author 心云间、凝听 <981248356@qq.com>
 */
function get_plugin_class($name) {
    $class = "\\plugins\\" . $name . "\\Index";
    return $class;
}

/**
 * 获取插件类的配置文件数组
 * @param string $name 插件名
 */
function get_plugin_config($name)
{
    if ($name!='') {
        $class = get_plugin_class($name);
        if (class_exists($class)) {
            $plugin = new $class();
            return $plugin->getConfig();
        } else {
            return [];
        }
    }
    
}

if (!function_exists('plugin_url')) {
    /**
     * 获取插件地址
     * @param  [type] $url   [description]
     * @param  [type] $param [description]
     * @return [type]        [description]
     */
    function plugin_url($url, $param=[])
    {
        $params = [];
        // 拆分URL
        $url  = explode('/', $url);
        if (isset($url[0])) {
            $params['_plugin'] = $url[0];
        }
        if (isset($url[1])) {
            $params['_controller'] = $url[1];
        }
        if (isset($url[2])) {
            $params['_action'] = $url[2];
        }

        // 合并参数
        $params = array_merge($params, $param);

        return url("home/plugin/execute", $params);
        
    }
}

/**
 *  url地址转换
 * @param  [type] $url [description]
 * @param  array $param [description]
 * @param  string $type 模块:1,插件：2,主题：theme
 * @return [type] [description]
 * @date   2017-11-14
 * @author 心云间、凝听 <981248356@qq.com>
 */
function eacoo_url($url, $param=[],$type=1)
{
    if ($type==1) {//模块
        return url($url,$param);
    } elseif ($type==2) {//插件
        $url_params = [];
        $query      = parse_url($url);
        $url        = $query['path'];
        if (!empty($query['query'])) {
            parse_str($query['query'],$url_params);
            $url_params = array_merge($url_params, $param);
        }
        if (strtolower($url)!='admin/plugins/config') {
        
            return plugin_url($url,$url_params);
        } else{
            return url($url,$url_params);
        }
        
    }
}