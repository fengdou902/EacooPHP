<?php
// Widget基类，仅限模块的widget调用       
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\controller;

class Widget extends Base {

	public function _initialize() {
        parent::_initialize();
    }

    /**
     * widget模版输出
     * @param  string $templateFile 模板文件名
     * @param  array  $vars         模板输出变量
     * @param  array  $replace      模板替换
     * @param  array  $config       模板参数
     * @param  array  $render       是否渲染内容
     * @return [type]               [description]
     */
    public function fetch($template='', $vars = [], $replace = [], $config = [] ,$render=false) {
		$class      = get_class($this);
		$names      = explode('\\', $class);
		$module     = $names[1];
		$controller = strtolower($names[3]);

        if ($template != '') {
            $widget_view_path = $module_view_path = APP_PATH.$module.'/view/';
            $template_path = '';
            if (MODULE_MARK === 'admin') {
                $template_path = 'admin/';
            } else{
                $current_theme_path = THEME_PATH . CURRENT_THEME . '/'; //默认主题设为当前主题
                $widget_view_path = $current_theme_path.$module.'/';
            }
            
            $template_path .= 'widget/';

        	$template_path .= $controller.'/'.$template;
            $template = $widget_view_path.$template_path. '.' .config('template.view_suffix');
            
            if (!is_file($template)) {
                $template = $module_view_path.$template_path. '.' .config('template.view_suffix');
                if (!is_file($template)) {
                    throw new \Exception('模板不存在：'.$template, 5001);
                }
                
            }
            
           echo $this->view->fetch($template, $vars, $replace, $config, $render);
        }
    }
}
