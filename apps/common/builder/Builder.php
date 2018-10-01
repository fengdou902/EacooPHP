<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\builder;
use app\common\controller\Base;
use think\Exception;

/**
 * Builder：快速建立管理页面。
 *
 * Class Builder
 * @package common\Builder
 */
class Builder extends Base {

    protected $metaTitle; // 页面标题
    protected $tips;      // 页面标题
    protected $pluginName;
    protected $preQueryConnector;

    public function _initialize() {
        parent::_initialize();

        $this->pluginName = null;
        if (input('?param._plugin')) {
            $this->pluginName = input('param._plugin');
        }
        //参数前缀连接符
        $this->preQueryConnector = SERVER_SOFTWARE_TYPE=='nginx' ? '&' : '?';
    }

    /**
     * 开启Builder
     * @param  string $type 构建器名称
     * @return [type]       [description]
     */
    public static function run($type='')
    {
        if ($type == '') {
            throw new \Exception('未指定构建器', 100001);
        } else {
            $type = ucfirst(strtolower($type));
        }

        // 构造器类路径
        $class = '\\app\\common\\builder\\Builder'. $type;
        if (!class_exists($class)) {
            throw new \Exception($type . '构建器不存在', 100002);
        }

        return new $class;

    }

    protected function compileHtmlAttr($attr) {
        $result = [];
        foreach($attr as $key=>$value) {
            if (is_string($value)) {
                $value = htmlspecialchars($value);
                $result[] = "$key=\"$value\"";
            }
        }
        $result = implode(' ', $result);
        return $result;
    }

    /**
     * 设置页面标题
     * @param $title 标题文本
     * @return $this
     */
    public function setMetaTitle($meta_title) {
        $this->metaTitle = $meta_title;
        return $this;
    }

    /**
     * 设置页面说明
     * @param $title 标题文本
     * @return $this
     */
    public function setPageTips($content,$type='info') {
        $this->tips = $content;
        return $this;
    }

    /**
     * 模版输出
     * @param  string $template 模板文件名
     * @param  array  $vars         模板输出变量
     * @param  array  $replace      模板替换
     * @param  array  $config       模板参数
     * @return [type]               [description]
     */
    public function fetch($template = '',$vars = [], $replace = [], $config = []) {
        $template_path_str = '../';

        $this->assign('template_path_str',$template_path_str);
        $this->assign('_builder_style_', $template_path_str.'apps/common/view/builder/style.html');  // 页面样式
        $this->assign('_builder_javascript_', $template_path_str.'apps/common/view/builder/javascript.html');  // 页面样式
        
        $template_vars = [
            'show_box_header' => 1,//是否显示box_header
            'meta_title'      => $this->metaTitle,// 页面标题
            'tips'            => $this->tips,// 页面提示说明
        ];
        $this->assign($template_vars);
        //显示页面
        if ($template!='') {
            return parent::fetch($template,$vars,$replace,$config);
        }

    }
}

