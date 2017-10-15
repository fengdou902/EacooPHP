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

class Modules extends Base {

    //protected $auto   = ['update_time'];
    protected $insert     = ['status' => 1,'sort'=>0];

    //安装描述文件名
    static public $infoFile = 'info.json';

    //安装菜单文件名
    static public $menusFile = 'menus.php';

    //安装选项文件名
    static public $optionsFile = 'options.php';
    

    /**
     * 获取模块菜单
     */
    public function getAdminMenu($module_name = MODULE_NAME) {
        $map = [
            'module'  =>$module_name,
            'is_menu' =>1,
            'status'  =>1
        ];
        $_menu_list = db('auth_rule')->where($map_rules)->field('id,name,title,module,pid,type,icon')->order('sort asc')->select();
        // 转换成树结构
        $tree = new tree();
        return $tree->list_to_tree($_menu_list);
    }

    /**
     * 获取模块列表
     * @param string $module_dir
     */
    public static function getAll() {

        // 文件夹下必须有$info_file定义的安装描述文件
        $dir = self::getInstallFiles(APP_PATH);
        foreach ($dir as $subdir) {
            $info_file = realpath(APP_PATH.$subdir).'/install/'.self::$infoFile;
            if (is_file($info_file) && $subdir != '.' && $subdir != '..') {
                $module_info = self::getInfo($subdir);//模块名即为当前模块的文件夹名
                if (!empty($module_info)) {
                    $module_list[] = $module_info;
                }
                unset($module_info);
            }
        }
        foreach ($module_list as &$val) {
            if (!isset($val['right_button'])) $val['right_button']='';
            switch($val['status']){
                case -3:  // 模块信息异常
                    $val['status'] = '<span class="text-danger">异常</span>';
                    $val['right_button']  = '<a class="label label-danger" href="http://forum.eacoo123.com" target="_blank">反馈</a>';
                    break;
                case -2:  // 损坏
                    $val['status'] = '<span class="text-danger">损坏</span>';
                    $val['right_button']  = '<a class="label label-danger ajax-get" href="'.url('setStatus', ['status' => 'delete', 'ids' => $val['id']]).'">删除记录</a>';
                    break;
                case -1:  // 未安装
                    $val['status'] = '<i class="fa fa-download text-warning"></i>';
                    $val['right_button']  = '<a class="label label-success" href="'.url('install_before', ['name' => $val['name']]).'">安装</a>';
                    break;
                case 0:  // 禁用
                    $val['status'] = '<i class="fa fa-ban text-danger"></i>';
                    $val['right_button'] .= '<a class="label label-info ajax-get" href="'.url('updateInfo', ['id' => $val['id']]).'">刷新</a> ';
                    $val['right_button'] .= '<a class="label label-success ajax-get" href="'.url('setStatus', ['status' => 'resume', 'ids' => $val['id']]).'">启用</a> ';
                    $val['right_button'] .= '<a class="label label-danger" href="'.url('uninstall_before', ['id' => $val['id']]).'">卸载</a> ';
                    break;
                case 1:  // 正常
                    $val['status'] = '<i class="fa fa-check text-success"></i>';
                    $val['right_button'] .= '<a class="label label-info ajax-get" href="'.url('updateInfo?id='.$val['id']).'">刷新</a> ';
                    if (!$val['is_system']) {
                        $val['right_button'] .= '<a class="label label-warning ajax-get" href="'.url('setStatus', ['status' => 'forbid', 'ids' => $val['id']]).'">禁用</a> ';
                        $val['right_button'] .= '<a class="label label-danger" href="'.url('uninstall_before', ['id' => $val['id']]).'" >卸载</a> ';
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
    public static function getInfo($name)
    {
        $module = self::where(['name' => $name])->field(true)->find();
        if ($module === false || empty($module)) {//数据库中不存在信息
            $module_info       = self::getInfoByFile($name);//从文件获取

            if (!empty($module_info)) {
                $module_info['status']=-1;
                return $module_info;
            } else{
                $module_info = [
                    'name'=>$name,
                    'title'=>'未知',
                    'description'=>'<span class="text-danger">请在'.$name.'模块目录下的install目录中检测info.json文件信息是否符合格式！</span>',
                    'author'=>'未知',
                    'version'=>'未知',
                    'status'=>-3,
                ];
                return $module_info;
            }

        } else {
            return $module->toArray();
        }
    }

    /**
     * 检测是否安装了某个模块
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-09-17
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function checkInstallModule($name='')
    {
        if ($name!='') {
            $res = self::where(['name' => $name,'status'=>1])->count();
            if ($res>0) {
                return true;
            }
        }

        return false;
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
            return false;
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
     * 本地模块信息
     * @return [type] [description]
     * @date   2017-09-21
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function localModules()
    {
        $data = cache('local_modules');
        if (empty($data) || !$data) {
            $module_dir = APP_PATH;
            $dirs = array_map('basename', glob($module_dir.'*', GLOB_ONLYDIR));
            if ($dirs == false || !file_exists($module_dir)) {
                $this->error = '模块目录不可读或者不存在';
                return false;
            } else{
                if (!empty($dirs)) {
                    foreach ($dirs as $name) {
                        $info = $this->getInfoByFile($name);
                        
                        if (empty($info) || !$info) {
                            \think\Log::record('模块'.$name.'的信息缺失！');
                            continue;
                        } else{
                            $info_flag = $this->checkInfoFile($name);
                            $data[$name] = $info;
                        }
                        
                    }
                    cache('local_modules',$data,600);
                } 
            }
        }
        
        return $data;
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
        $info_file = realpath(APP_PATH.$name).'/install/'.self::$infoFile;
        if (is_file($info_file)) {
            $module_info = file_get_contents($info_file);
            $module_info = json_decode($module_info,true);
            return $module_info;
        } else {
            return false;
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
        $file = realpath(APP_PATH.$name).'/install/'.self::$menusFile;

        if (is_file($file)) {

            $module_menus = include $file;

            return !empty($module_menus['admin_menus']) ? $module_menus['admin_menus'] : false;

        } else {
            return false;
        }
    }

    /**
     * 文件获取安装的前台导航
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-09-16
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getNavigationByFile($name='')
    {
        if ($name=='' || !$name) {
            return false;
        }
        $file = realpath(APP_PATH.$name).'/install/'.self::$menusFile;

        if (is_file($file)) {

            $module_menus = include $file;

            return !empty($module_menus['navigation']) ? $module_menus['navigation'] : false;

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
        $file = realpath(APP_PATH.$name).'/install/'.self::$optionsFile;

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
            $name = self::$pluginName;
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
    
    /*——————————————————————————私有域—————————————————————————————*/
    
    /**
     * 获取文件列表
     */
    private static function getInstallFiles($folder)
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
