<?php
// 插件控制器       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2017 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author: 心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\home\admin;
use app\admin\controller\Admin;

/**
 * 插件控制器
 * @package app\index\home
 */
class Plugin extends Admin
{
    public function _initialize() {
        parent::_initialize();

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
            $class = "plugins\\{$plugin}\\controller\\{$controller}";
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

        //$name  = input('param._plugin');

        if ($template != '') {
            $template = config('template.view_path').$template . '.' .config('template.view_suffix');
            echo $this->view->fetch($template, $vars, $replace, $config, $render);
        }
    }
}
