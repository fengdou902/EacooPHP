<?php
// 模块模型       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use app\common\model\Base;

class Module extends Base {

    //protected $auto   = ['update_time'];
    protected $insert     = ['status' => 1,'sort'=>0];

    /**
     * 安装描述文件名
     */
    public function info_file() {
        return 'info.php';
    }

    /**
     * 获取模块菜单
     */
    public function getAdminMenu($module_name = MODULE_NAME) {
        $map_rules['module']  = $module_name;
        $map_rules['status']  = 1;
        $map_rules['is_menu'] = 1;
        $_menu_list=db('auth_rule')->where($map_rules)->field('id,name,title,module,pid,type,icon')->order('sort asc')->select();
        // 转换成树结构
        $tree = new tree();
        return $tree->list_to_tree($_menu_list);
    }

    /**
     * 获取模块列表
     * @param string $addon_dir
     */
    public function getAll() {
        // 获取除了Common等系统模块外的用户模块
        // 文件夹下必须有$info_file定义的安装描述文件
        $dir = $this->getFile(APP_PATH);
        foreach ($dir as $subdir) {
            $info_file = realpath(APP_PATH.$subdir).'/Info/'.$this->info_file();
            if (is_file($info_file) && $subdir != '.' && $subdir != '..') {
                $module_list[] = $this->getModule($subdir);//模块名即为当前模块的文件夹名
            }
        }
        foreach ($module_list as &$val) {
            if (!isset($val['right_button'])) $val['right_button']='';
            switch($val['status']){
                case '-2':  // 损坏
                    $val['status_icon'] = '<span class="text-danger">损坏</span>';
                    $val['right_button']  = '<a class="label label-danger ajax-get" href="'.url('setStatus', ['status' => 'delete', 'ids' => $val['id']]).'">删除记录</a>';
                    break;
                case '-1':  // 未安装
                    $val['status_icon'] = '<i class="fa fa-download text-warning"></i>';
                    $val['right_button']  = '<a class="label label-success" href="'.url('install_before', ['name' => $val['name']]).'">安装</a>';
                    break;
                case '0':  // 禁用
                    $val['status_icon'] = '<i class="fa fa-ban text-danger"></i>';
                    $val['right_button'] .= '<a class="label label-info ajax-get" href="'.url('updateInfo', ['id' => $val['id']]).'">更新菜单</a> ';
                    $val['right_button'] .= '<a class="label label-success ajax-get" href="'.url('setStatus', ['status' => 'resume', 'ids' => $val['id']]).'">启用</a> ';
                    $val['right_button'] .= '<a class="label label-danger ajax-get" href="'.url('uninstall_before', ['id' => $val['id']]).'">卸载</a> ';
                    break;
                case '1':  // 正常
                    $val['status_icon'] = '<i class="fa fa-check text-success"></i>';
                    $val['right_button'] .= '<a class="label label-info ajax-get" href="'.url('updateInfo?id='.$val['id']).'">更新菜单</a> ';
                    if (!$val['is_system']) {
                        $val['right_button'] .= '<a class="label label-warning ajax-get" href="'.url('setStatus', ['status' => 'forbid', 'ids' => $val['id']]).'">禁用</a> ';
                        $val['right_button'] .= '<a class="label label-danger" href="'.url('uninstall_before', ['id' => $val['id']]).'">卸载</a> ';
                    }
                    break;
            }
        }
        return $module_list;
    }
    /**通过模块名来获取模块信息
     * @param $name 模块名
     * @return array|mixed
     */
    public function getModule($name)
    {
        $module = $this->where(['name' => $name])->find();
        if ($module === false || $module == null) {//数据库中不存在信息
            $moduleInfo = $this->getInfo($name);//从文件获取
            if (!empty($moduleInfo)) {
                $moduleInfo['status']=-1;
                return $moduleInfo;
            }

        } else {
            return $module;
        }
    }
 /*——————————————————————————私有域—————————————————————————————*/

    private function getInfo($name)
    {
        $info_file=APP_PATH . '/' . $name . '/Info/'.$this->info_file();
        if (is_file($info_file)) {
            $module = include($info_file);
            return $module['info'];
        } else {
            return [];
        }

    }

    /**
     * 获取文件列表
     */
    private function getFile($folder)
    {
        //打开目录
        $fp = opendir($folder);
        //阅读目录
        while (false != $file = readdir($fp)) {
            //列出所有文件并去掉'.'和'..'
            if ($file != '.' && $file != '..') {
                //$file="$folder/$file";
                $file = "$file";

                //赋值给数组
                $arr_file[] = $file;

            }
        }
        //输出结果
        if (is_array($arr_file)) {
            while (list($key, $value) = each($arr_file)) {
                $files[] = $value;
            }
        }
        //关闭目录
        closedir($fp);
        return $files;
    }
}
