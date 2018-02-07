<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.eacoo123.com, All rights reserved.         
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

    /**
     * 模版输出
     * @param  string $template 模板文件名
     * @param  array  $vars         模板输出变量
     * @param  array  $replace      模板替换
     * @param  array  $config       模板参数
     * @return [type]               [description]
     */
    public function fetch($template='',$vars = [], $replace = [], $config = []) {
        if (PUBLIC_RELATIVE_PATH=='') {
            $template_path_str = '../';
        } else{
            $template_path_str = './';
        }
        $this->assign('template_path_str',$template_path_str);
        $this->assign('_builder_style_', $template_path_str.'apps/common/view/builder/style.html');  // 页面样式
        $this->assign('_builder_javascript_', $template_path_str.'apps/common/view/builder/javascript.html');  // 页面样式
        //显示页面
        if ($template!='') {
            echo parent::fetch($template,$vars,$replace,$config);
        }
        
    }

    protected function compileHtmlAttr($attr) {
        $result = [];
        foreach($attr as $key=>$value) {
            $value = htmlspecialchars($value);
            $result[] = "$key=\"$value\"";
        }
        $result = implode(' ', $result);
        return $result;
    }
}

