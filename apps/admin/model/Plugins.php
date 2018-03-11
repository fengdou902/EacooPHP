<?php
// 插件模型 该类参考了OneThink的部分实现
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\model;
use think\Model;
use app\admin\controller\Extension;

/**
 * 插件模型
 * 该类参考了OneThink的部分实现
 */
class Plugins extends Model {

    protected $insert = ['sort'=>99,'status'=>1];
    
    static public $pluginName;

    /**
     * 获取插件列表
     * @param string $plugin_dir
     */
    public function getAll() {

        $plugin_dir = PLUGIN_PATH;
        $dirs = array_map('basename', glob($plugin_dir.'*', GLOB_ONLYDIR));
        if ($dirs == false || !file_exists($plugin_dir)) {
            $this->error = '插件目录不可读或者不存在';
            return false;
        }
        $plugins     = [];
        $map['name'] = ['in', $dirs];
        $list = $this->where($map)
                     ->field(true)
                     ->order('sort asc,id desc')
                     ->select();
        foreach ($list as $plugin) {
            $plugins[$plugin['name']] = $plugin->toArray();
            $logo = Extension::getLogo($plugin['name'],'plugin');
            if (!$logo) {
                $plugins[$plugin['name']]['logo'] = '<span class="plugin-logo plugin-avatar-tx">'.mb_substr($plugin['title'], 0,1,'utf-8').'</span>';
            } else{
                $plugins[$plugin['name']]['logo'] = '<img src="'.$logo.'" class="plugin-logo">';
            }
        }

        $extensionObj = new Extension;
        foreach ($dirs as $value) {
            if (!isset($plugins[$value])) {
                $info_file = PLUGIN_PATH.$value.'/install/info.json';
                $info      = $extensionObj->getInfoByFile($info_file);
                //设置插件LOGO
                $logo = Extension::getLogo($value,'plugin');
                if ($logo) {
                    $info['logo'] = '<img src="'.$logo.'" class="plugin-logo">';
                    
                } else{
                    $info['logo'] = '<span class="plugin-logo plugin-avatar-tx">'.mb_substr($info['title'], 0,1,'utf-8').'</span>';
                }
                
                $info_flag = $extensionObj->checkInfoFile($info_file);
                if (!$info || !$info_flag) {
                    \think\Log::record('插件'.$value.'的信息缺失！');
                    continue;
                }
                
                $plugins[$value] = $info;
                if ($plugins[$value]) {
                    $plugins[$value]['status'] = -1;  // 未安装
                }
            }
        }
        
        foreach ($plugins as &$val) {
            if (!isset($val['name'])) {
                continue;
            }
            $val['from_type']    = 'local';
            $val['create_time'] = isset($val['create_time']) ? friendly_date(strtotime($val['create_time']),'mohu') :'';
            $extensionObj->initInfo('plugin',$val['name']);
            //判断是否有设置
            $name_options = $extensionObj->getOptionsByFile($val['name']);
            $val['is_option'] = 1;
            if (empty($name_options)) {
                $val['is_option'] = 0;
            }
            switch ($val['status']) {
                case -1:  // 未安装
                    $val['status'] = '<i class="fa fa-trash" style="color:red"></i>';
                    $val['right_button']  = '<a class="btn btn-primary btn-sm app-install-before" href="javascript:void(0)" data-type="plugins" data-name="'.$val['name'].'" >安装</a>';
                    $val['right_button']  .= '<a class="btn btn-danger btn-sm ajax-get confirm ml-5" confirm-info="您确定要删除该插件吗？" href="'.url('delPlugin',['name'=>$val['name']]).'">删除</a>';
                    break;
                case 0:  // 禁用
                    $val['status'] = '<i class="fa fa-ban" style="color:red"></i>';
                    $val['right_button'] = '<a class="btn btn-success btn-sm ajax-get" href="'.url('setStatus',array('status'=>'resume', 'ids' => $val['id'])).'">启用</a> ';
                    $val['right_button'] .= '<a class="btn btn-default btn-sm app-local-uninstall" href="javascript:void(0)" data-type="plugins" data-id="'.$val['id'].'" title="准备卸载">卸载</a> ';
                    if (!empty($val['admin_manage_into'])) {
                        $val['right_button'] .= '<a class="btn btn-success btn-sm" href="'.url('adminManage',array('name'=>$val['name'])).'" >后台管理</a>';
                    }
                    break;
                case 1:  // 正常
                    $val['status'] = '<i class="fa fa-check" style="color:green"></i>';
                    if ($val['is_option']==1) {
                        $val['right_button']  = '<a class="btn btn-info btn-sm opentab" href="'.url('config',['name'=>$val['name']]).'" data-iframe="true" tab-title="设置-'.$val['title'].'" tab-name="navtab-collapse-app-plugin-option-'.$val['id'].'">设置</a> ';
                    } else{
                        $val['right_button'] = '';
                    }
                    
                    $val['right_button'] .= '<a class="btn bg-orange btn-warning btn-sm ajax-get" href="'.url('setStatus',['status'=>'forbid', 'ids' => $val['id']]).'">禁用</a> ';
                    $val['right_button'] .= '<a class="btn btn-default btn-sm app-local-uninstall" href="javascript:void(0)" data-type="plugins" data-id="'.$val['id'].'" title="准备卸载">卸载</a> ';
                    if (!empty($val['admin_manage_into'])) {
                        $val['right_button'] .= '<a class="btn btn-success btn-sm" href="'.url('adminManage',['name'=>$val['name']]).'"  title="后台管理">后台管理</a>';
                    }
                    break;
            }
        }
        return $plugins;
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

}
