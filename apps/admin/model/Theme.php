<?php
// 主题模型 
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use app\common\model\Base;
//use Think\Storage;

class Theme extends Base {

    // 设置数据表（不含前缀）
    protected $name = 'themes';

    protected $insert   = ['current'=>0,'sort'=>0,'status' => 1];

    //安装描述文件名
    static public $infoFile = 'info.json';

    //安装菜单文件名
    static public $menusFile = 'menus.php';

    //安装选项文件名
    static public $optionsFile = 'options.php';

    /**
     * 获取主题列表
     * @param string $addon_dir
     */
    public function getAll() {
        $path = THEME_PATH;
        $dirs = array_map('basename', glob($path.'*', GLOB_ONLYDIR));
        foreach ($dirs as $name) {
            $info_file = realpath($path.$name).'/'.self::$infoFile;
            if (is_file($info_file)) {
                $theme_dir_list[]          = $name;
                $info                      = self::getInfoByFile($name);
                $info['status']            = -1; //未安装
                $theme_list[$info['name']] = $info;
            }
        }

        // 获取系统已经安装的主题信息
        if (isset($theme_dir_list) && $theme_dir_list) {
            $map['name'] = ['in', $theme_dir_list];
        } else {
            return false;
        }
        $installed_theme_list = $this->where($map)->order('sort asc,id desc')->select();

        if ($installed_theme_list) {
            foreach ($installed_theme_list as $theme) {
                $theme_list[$theme['name']] = $theme->toArray();
            }
            //系统已经安装的主题信息与文件夹下主题信息合并
            $theme_list = array_merge($theme_list, $theme_list);
        }

        foreach ($theme_list as &$val) {
            if (!isset($val['right_button'])) {
                $val['right_button']='';
            }
            switch ($val['status']) {
                case '-1': //未安装
                    $val['status'] = '<i class="fa fa-download" style="color:green"></i>';
                    $val['right_button']  = '<a class="btn btn-xs btn-success ajax-get" href="'.url('install', array('name' => $val['name'])).'">安装</a>';
                    break;
                default :
                    $val['status'] = '<i class="fa fa-check" style="color:green"></i>';
                    if ($val['current']) {
                        $val['right_button'] .= '<span class="btn btn-xs btn-success" href="#">我是当前主题</span> ';
                    } else {
                        $val['right_button'] .= '<a class="btn btn-xs btn-primary ajax-get" href="'.url('setCurrent', array('id' => $val['id'])).'">设为当前主题</a> ';
                    }
                    $val['right_button'] .= '<a class="btn btn-xs btn-info ajax-get" href="'.url('updateInfo', array('id' => $val['id'])).'">更新信息</a> ';
                    $val['right_button'] .= '<a class="btn btn-xs btn-danger ajax-get" href="'.url('uninstall', array('id' => $val['id'])).'">卸载</a> ';
                    break;
            }
        }
        return $theme_list;
    }

    /**
     * 检测信息
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-09-18
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function checkInfoFile($name='') {
        if ($name=='') {
            $name = self::$pluginName;
        }
        $info_check_keys = ['name', 'title', 'description', 'author', 'version'];
        foreach ($info_check_keys as $value) {
            if (!array_key_exists($value, self::getInfoByFile($name))) {
                return false;
            }

        }
        return true;
    }

    /**
     * 获取插件依赖的钩子
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-09-18
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getDependentHooks($name='')
    {
        if ($name=='' || !$name) {
            return false;
        }
        $info = self::getInfoByFile($name);
        $dependent_hooks = !empty($info['dependences']['hooks']) ? $info['dependences']['hooks']:'';
        $dependent_hooks = explode(',', $dependent_hooks);
        return $dependent_hooks;
    }

    /**
     * 文件获取模块信息
     * @param  [type] $name [description]
     * @return [type] [description]
     * @date   2017-09-15
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getInfoByFile($name = '')
    {
        if ($name=='' || !$name) {
            return false;
        }
        $info_file = realpath(THEME_PATH.$name).'/'.self::$infoFile;
        if (is_file($info_file)) {
            $info = file_get_contents($info_file);
            $info = json_decode($info,true);
            return $info;
        } else {
            return [];
        }

    }

    /**
     * 文件获取安装信息的后台菜单
     * @param  string $name 模块名
     * @return [type] [description]
     * @date   2017-09-16
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getAdminMenusByFile($name='')
    {
        if ($name=='' || !$name) {
            return false;
        }
        $file = realpath(THEME_PATH.$name).'/'.self::$menusFile;

        if (is_file($file)) {

            $module_menus = include $file;

            return !empty($module_menus['admin_menus']) ? $module_menus['admin_menus'] : false;

        } else {
            return false;
        }
    }

    /**
     * 文件获取安装的后台选项
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-09-16
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getOptionsByFile($name ='')
    {
        if ($name=='' || !$name) {
            return false;
        }
        $file = realpath(THEME_PATH.$name).'/'.self::$optionsFile;

        if (is_file($file)) {

            $module_menus = include $file;

            return $module_menus;

        } else {
            return false;
        }
    }

    /**
     * 获取插件默认配置
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-09-18
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getDefaultConfig($name ='')
    {
        if ($name=='') {
            $name = self::$themeName;
        }

        $config = [];
        if ($name) {
            $options = self::getOptionsByFile($name);
            if (!empty($options) && is_array($options)) {
                $config = [];
                foreach ($options as $key => $value) {
                    if ($value['type'] == 'group') {
                        foreach ($value['options'] as $gkey => $gvalue) {
                            foreach ($gvalue['options'] as $ikey => $ivalue) {
                                $config[$ikey] = $ivalue['value'];
                            }
                        }
                    } else {
                        $config[$key] = $options[$key]['value'];
                    }
                }
            }
        }
        return $config;
    }
}
