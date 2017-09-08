<?php
// 主题模型 
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.
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

    /**
     * 安装描述文件名
     */
    public function info_file() {
        return 'info.php';
    }

    /**
     * 获取主题列表
     * @param string $addon_dir
     */
    public function getAll() {
        //获取所有主题（文件夹下必须有$install_file定义的安装描述文件）
        $path = './theme/';;
        $dirs = array_map('basename', glob($path.'*', GLOB_ONLYDIR));
        foreach ($dirs as $dir) {
            $config_file = realpath($path.$dir).'/'.$this->info_file();
            if (is_file($config_file)) {
                $theme_dir_list[]                      = $dir;
                $temp_arr                              = include $config_file;
                $temp_arr['info']['status']            = -1; //未安装
                $theme_list[$temp_arr['info']['name']] = $temp_arr['info'];
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
                $theme_list[$theme['name']] = $theme;
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
}
