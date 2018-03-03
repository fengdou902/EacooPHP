<?php
// 后台主题处理逻辑      
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\logic;
use app\admin\controller\Extension;

class Theme extends AdminLogic
{
	// 设置数据表（不含前缀）
    protected $name = 'themes';

 	/**
     * 获取主题列表
     * @param string $addon_dir
     */
    public static function getAll() {
        $path = THEME_PATH;
        $dirs = array_map('basename', glob($path.'*', GLOB_ONLYDIR));
        $extensionObj = new Extension;
        foreach ($dirs as $subdir) {
            $info_file = THEME_PATH.$subdir.'/install/info.json';
            if (is_file($info_file) && $subdir != '.' && $subdir != '..') {
                $info = self::getInfo($subdir);//模块名即为当前模块的文件夹名
                if (!empty($info)) {
                    $logo = Extension::getLogo($info['name'],'theme');
                    if ($logo) {
                        $info['logo'] = '<img src="'.$logo.'" class="theme-logo">';
                    } else{
                        $info['logo'] = '<span class="theme-logo theme-avatar-tx">'.mb_substr($info['title'], 0,1,'utf-8').'</span>';
                    }
                    $theme_list[] = $info;
                }
                unset($info);
            }
        }
        if (empty($theme_list)) {
            return [];
        }
        foreach ($theme_list as &$val) {
            if (!isset($val['right_button'])) {
                $val['right_button']='';
            }
            switch ($val['status']) {
                case '-1': //未安装
                    $val['status'] = '<i class="fa fa-download" style="color:green"></i>';
                    $val['right_button']  = '<a class="btn btn-sm btn-success ajax-get" href="'.url('install', array('name' => $val['name'])).'">安装</a>';
                    break;
                default :
                    $val['status'] = '<i class="fa fa-check" style="color:green"></i>';
                    if ($val['current']==1) {
                        $val['right_button'] .= '<span class="btn btn-sm btn-success"><i class="fa fa-television"></i> 当前电脑端</span> ';
                    } elseif ($val['current']==2) {
                        $val['right_button'] .= '<span class="btn btn-sm btn-warning ajax-get" ><i class="fa fa-mobile f16"></i> 当前移动端</span> ';
                    } else{
                        $val['right_button'] .= '<div class="btn-group">
                      <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">设置 <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu">
                        <li><a class="ajax-get" href="'.url('setCurrent', ['id' => $val['id'],'type'=>1]).'">设为电脑端主题</a></li>
                        <li><a class="ajax-get" href="'.url('setCurrent', ['id' => $val['id'],'type'=>2]).'">设为移动端主题</a></li>
                      </ul>
                    </div> ';
                    }
                    $val['right_button'] .= '<a class="btn btn-sm btn-info ajax-get" href="'.url('updateInfo', array('id' => $val['id'])).'">刷新</a> ';
                    $val['right_button'] .= '<a class="btn btn-sm btn-default ajax-get" href="'.url('uninstall', array('id' => $val['id'])).'">卸载</a> ';
                    break;
            }
        }
        return $theme_list;
    }

    /**通过模块名来获取模块信息
     * @param $name 模块名
     * @return array|mixed
     */
    public static function getInfo($name)
    {
        $info = self::where(['name' => $name])->field(true)->find();
        if ($info === false || empty($info)) {//数据库中不存在信息
            $extensionObj = new Extension;
            $extensionObj->initInfo('theme',$name);
            $theme_info = $extensionObj->getInfoByFile();//从文件获取

            if (!empty($theme_info)) {
                $theme_info['status']=-1;
                return $theme_info;
            } else{
                $theme_info = [
                    'name'=>$name,
                    'title'=>'未知',
                    'description'=>'<span class="text-danger">请在'.$name.'主题目录下的install目录中检测info.json文件信息是否符合格式！</span>',
                    'author'=>'未知',
                    'version'=>'未知',
                    'status'=>-3,
                ];
                return $theme_info;
            }

        } else {
            return $info->toArray();
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
        $file = realpath(THEME_PATH.$name).'/options.php';

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