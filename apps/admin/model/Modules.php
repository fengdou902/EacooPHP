<?php
// 模块模型       
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
use app\admin\controller\Extension;

class Modules extends Base {

    //protected $auto   = ['update_time'];
    protected $insert     = ['status' => 1,'sort'=>0];

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
        $dirs = self::getInstallFiles(APP_PATH);
        foreach ($dirs as $subdir) {
            $info_file = APP_PATH.$subdir.'/install/info.json';
            if (is_file($info_file) && $subdir != '.' && $subdir != '..') {
                $info = self::getInfo($subdir);//模块名即为当前模块的文件夹名
                if (!empty($info)) {
                    $logo = Extension::getLogo($info['name'],'module');
                    if ($logo) {
                        $info['logo'] = '<img src="'.$logo.'" class="module-logo">';
                    } else{
                        $info['logo'] = '<span class="module-logo module-avatar-tx">'.mb_substr($info['title'], 0,1,'utf-8').'</span>';
                    }
                    $module_list[] = $info;
                }
                unset($info);
            }
        }
        foreach ($module_list as &$val) {
            if (!isset($val['name'])) {
                continue;
            }
            if (!isset($val['right_button'])) $val['right_button']='';
            switch($val['status']){
                case -3:  // 模块信息异常
                    $val['status'] = '<span class="text-danger">异常</span>';
                    $val['right_button']  = '<a class="btn btn-danger btn-sm" href="http://forum.eacoo123.com" target="_blank">反馈</a>';
                    break;
                case -2:  // 损坏
                    $val['status'] = '<span class="text-danger">损坏</span>';
                    $val['right_button']  = '<a class="btn btn-danger btn-sm ajax-get" href="'.url('setStatus', ['status' => 'delete', 'ids' => $val['id']]).'" data-pjax="false">删除记录</a>';
                    break;
                case -1:  // 未安装
                    $val['status'] = '<i class="fa fa-download text-warning"></i>';
                    $val['right_button']  = '<a class="btn btn-success btn-sm app-install-before" href="javascript:void(0)" data-type="modules" data-name="'.$val['name'].'" >安装</a>';
                    $val['right_button']  .= '<a class="btn btn-danger btn-sm ajax-get confirm ml-5" href="'.url('del',['name'=>$val['name']]).'" data-pjax="false">删除</a>';
                    break;
                case 0:  // 禁用
                    $val['status'] = '<i class="fa fa-ban text-danger"></i>';
                    $val['right_button'] .= '<a class="btn btn-info btn-sm ajax-get" href="'.url('updateInfo', ['id' => $val['id']]).'" data-pjax="false">刷新</a> ';
                    $val['right_button'] .= '<a class="btn btn-success btn-sm ajax-get" href="'.url('setStatus', ['status' => 'resume', 'ids' => $val['id']]).'" data-pjax="false">启用</a> ';
                    $val['right_button'] .= '<a class="btn btn-default btn-sm app-local-uninstall" href="javascript:void(0)" data-type="modules" data-id="'.$val['id'].'" >卸载</a> ';
                    break;
                case 1:  // 正常
                    $val['status'] = '<i class="fa fa-check text-success"></i>';
                    $val['right_button'] .= '<a class="btn btn-info btn-sm ajax-get" href="'.url('updateInfo?id='.$val['id']).'" data-pjax="false">刷新</a> ';
                    if (!$val['is_system']) {
                        $val['right_button'] .= '<a class="btn btn-warning btn-sm ajax-get" href="'.url('setStatus', ['status' => 'forbid', 'ids' => $val['id']]).'" data-pjax="false">禁用</a> ';
                        $val['right_button'] .= '<a class="btn btn-default btn-sm app-local-uninstall" href="javascript:void(0)" data-type="modules" data-id="'.$val['id'].'" >卸载</a> ';
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
            $extensionObj = new Extension;
            $extensionObj->initInfo('module',$name);
            $module_info = $extensionObj->getInfoByFile();//从文件获取

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
    public static function checkInstall($name='')
    {
        if ($name!='') {
            $res = self::where(['name' => $name,'status'=>1])->count();
            if ($res>0) {
                return true;
            }
        }

        return false;
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
