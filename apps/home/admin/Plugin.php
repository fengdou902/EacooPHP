<?php
// 插件控制器       
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\home\admin;
use app\admin\controller\Admin;

/**
 * 插件控制器
 * @package app\index\home
 */
class Plugin extends Admin
{
    protected $name             = '';
    protected $pluginPath       = '';

    public function _initialize() {
        parent::_initialize();

        $name = input('param._plugin', '', 'trim');
        if ($name) {
            $this->name = $name;
            $this->pluginPath = PLUGIN_PATH.$name.DS;
        } else{
            $class = get_class($this);
            $path = strstr($class,substr($class, strrpos($class, '\\') + 1),true);
            $this->pluginPath = ROOT_PATH.str_replace('\\','/',$path);
        }
    }

    /**
    * 执行插件内部方法
    */
    public function execute()
    {
        $plugin     = input('param._plugin');
        $controller = input('param._controller');
        $action     = input('param._action');
        $params     = $this->request->except(['_plugin', '_controller', '_action'], 'param');

        if (empty($plugin) || empty($controller) || empty($action)) {
            $this->error('没有指定插件名称、控制器名称或操作名称');
        } else{
            if (!is_array($params)) {
                $params = (array)$params;
            }
            $class = "plugins\\{$plugin}\\admin\\{$controller}";
            $obj = new $class;
            return call_user_func_array([$obj, $action], $params);
        }

    }

    /**
     * 插件模版输出
     * @param  string $templateFile 模板文件名
     * @param  array  $vars         模板输出变量
     * @param  array  $replace      模板替换
     * @param  array  $config       模板参数
     * @param  array  $render       是否渲染内容
     * @return [type]               [description]
     */
    public function fetch($template='', $vars = [], $replace = [], $config = [] ,$render=false) {
        $plugin_name = input('param.plugin_name');

        if ($plugin_name != '') {
            $plugin     = $plugin_name;
            $controller = input('param._controller');
            $action     = 'index';
        } else {
            $plugin     = input('param._plugin');
            $controller = input('param._controller');
            $action     = input('param._action');
        }
        $template = $template == '' ? $action : $template;
        if (MODULE_MARK === 'admin') {
            $template = 'admin/'.$controller.'/'.$template;
        }
        if ($template != '') {
            if (!is_file($template)) {
                $template = $this->pluginPath. 'view/'. $template . '.' .config('template.view_suffix');
                if (!is_file($template)) {
                    throw new \Exception('模板不存在：'.$template, 5001);
                }
            }
            //$template = config('template.view_path').$template . '.' .config('template.view_suffix');
            
            echo $this->view->fetch($template, $vars, $replace, $config, $render);
        }
    }

}
