<?php 
use think\Route;
use app\common\builder\Builder;
use app\admin\model\Modules as ModulesModel;
use app\admin\model\Plugins as PluginsModel;

/**
 * 获取构建器实例
 * @param  string $type 类型（list|form）
 * @return [type] [description]
 * @date   2018-02-02
 * @author 心云间、凝听 <981248356@qq.com>
 */
function builder($type='')
{
    $builder = Builder::run($type);
    return $builder;
}

/**
 * 获取逻辑层实例
 * @param  string $name [description]
 * @return [type] [description]
 * @date   2018-02-02
 * @author 心云间、凝听 <981248356@qq.com>
 */
function logic($name='')
{
    return model($name,'logic');
}

/**
 * 获取服务层实例
 * @param  string $name [description]
 * @return [type] [description]
 * @date   2018-02-02
 * @author 心云间、凝听 <981248356@qq.com>
 */
function getService($name='')
{
    return model($name,'service');
}

/**
 * 检测是否安装某个模块
 * @param  string $name 模块标识
 * @return [type] [description]
 * @date   2017-09-17
 * @author 心云间、凝听 <981248356@qq.com>
 */
function check_install_module($name='')
{
    return ModulesModel::checkInstall($name);
}

/**
 * 检测是否安装某个插件
 * @param  string $name 插件标识
 * @return [type] [description]
 * @date   2017-11-14
 * @author 心云间、凝听 <981248356@qq.com>
 */
function check_install_plugin($name='')
{
    return PluginsModel::checkInstall($name);
}

/**
 * 处理插件钩子
 * @param  [type] $hook 钩子
 * @param  array $params 参数
 * @param  boolean $is_return 是否返回（true:返回值，false:直接输入）
 * @return [type] [description]
 * @date   2018-01-17
 * @author 心云间、凝听 <981248356@qq.com>
 */
function hook($hook, $params = [],$is_return =false)
{
    if ($is_return==true) {
        return \think\Hook::listen($hook, $params);exit;
    }
    \think\Hook::listen($hook, $params);
}

/**
 * 返回某个插件类的类名
 * @param  [type] $name 插件标识
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
     * @param  [type] $url   格式三段式，如：插件标识/控制器名称/操作名
     * @param  [type] $param [description]
     * @return [type]        [description]
     */
    function plugin_url($url, $param=[])
    {
        $params = [];
        // 拆分URL
        $url  = explode('/', $url);

        if (!isset($url[1]) && !isset($url[2])) {
            $params['_plugin']     = input('param._plugin');
            $params['_controller'] = input('param._controller');
            $params['_action']     = $url[0];
        } elseif (!isset($url[2])) {
            $params['_plugin']     = input('param._plugin');
            $params['_controller'] = $url[0];
            $params['_action']     = $url[1];
        } else {
            $params['_plugin']     = $url[0];
            $params['_controller'] = $url[1];
            $params['_action']     = $url[2];
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
 * @param  string $type 类型。0完整url，1模块地址，2插件地址，3主题
 * @return [type] [description]
 * @date   2017-11-14
 * @author 心云间、凝听 <981248356@qq.com>
 */
function eacoo_url($url, $param=[],$type=1)
{
    if ($type==2) {//插件
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
    } else{
        if($url=='' || !$url || strpos($url, 'http://')!==false || strpos($url, 'https://')!==false){
            return $url;
        } 
        return url($url,$param);
    }
}

/**
 * 设置日志记录
 * @param  string $content 日志内容
 * @param  string $scene_name 场景类型名
 * @param  string $type 内容类型：如：info,error,debug
 * @date   2017-11-06
 * @author 心云间、凝听 <981248356@qq.com>
 */
function setAppLog($content='', $scene_name='default', $type='info')
{
    if (is_array($content)) {
        $content = var_export($content,true);
    }
    $now = date('Y-m-d H:i:s');
    $remote  = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    $method  = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'CLI';
    $uri     = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $base_message = "---------------------------------------------------------------\r\n[{$now}] {$remote} {$method} {$uri}\r\n";
    $file = RUNTIME_PATH."applog".DS.$scene_name.DS.$type.'_'.date('Ymd',time()).".log";
    $path = dirname($file);
    !is_dir($path) && mkdir($path, 0755, true);
    $content = $content." \r\n";
    file_put_contents($file,$base_message.$content,FILE_APPEND);
    return true;
}