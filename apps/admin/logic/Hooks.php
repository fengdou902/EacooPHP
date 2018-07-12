<?php
// 钩子模型
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\logic;

use app\admin\model\Plugins as PluginsModel;
use app\admin\model\HooksExtra as HooksExtraModel;

/**
 * 插件钩子模型
 * 该类参考了OneThink的部分实现
 */
class Hooks extends AdminLogic {

    /**
    * 获取件所需的钩子是否存在，没有则新增
    * @param string $str  钩子名称
    * @param string $plugins  插件名称
    * @param string $plugins  件简介
    */
    public function existHook($name, $data){
        if ($name=='' || !$name) {
           return false; 
        }

        $map = [
            'name'=>$name
        ];
        $gethook = $this->where($map)->find();
        $gethook = !empty($gethook) ? $gethook->toArray() : $gethook;
        if (!$gethook || empty($gethook)) {
            $data = [
                'name'        => $name,
                'description' => $data['description'],
                'type'        => 1
            ];
            $this->allowField(true)->isUpdate(false)->data($data)->save();
        }
    }

    /**
     * 更新应用里的所有钩子对应的应用
     * @param  [type] $app_type 应用类型，1module，2plugin，3theme
     * @param  [type] $app_name 应用名称
     * @param  [type] $app_hooks 应用存在的钩子
     * @return [type] [description]
     * @date   2018-04-05
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function updateHooks($app_type,$app_name,$app_hooks) {

        $hooks = $this->column('name');
        $common = array_intersect($hooks, $app_hooks);
        if (!empty($common)) {
            foreach ($common as $hook) {
                $flag = $this->updateApps($hook,self::DEPEND_APP_TYPE[$app_type], array($app_name));
                if (false === $flag) {
                    //$this->removeHooks($app_name);
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 更新单个钩子处的应用
     * @param  [type] $hook_name [description]
     * @param  [type] $depend_apps [description]
     * @return [type] [description]
     * @date   2017-09-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function updateApps($hook_name,$depend_type, $depend_apps) {
        
        $hook_id = $this->where('name',$hook_name)->value('id');
        $map = [
            'hook_id'     => $hook_id,
            'depend_type' => $depend_type
        ];
        $hooksExtraModel = new HooksExtraModel;
        $o_apps = $hooksExtraModel->where($map)->column('depend_flag');
        $apps_depends = [];
        if (!empty($o_apps)) {
            $apps = array_merge($o_apps, $depend_apps);
            $apps = array_unique($apps);
            $apps = array_diff($apps, $o_apps);
             
        } else {
            $apps = $depend_apps;
        }

        if (!empty($apps)) {
            foreach ($apps as $key => $app) {
                $apps_depends[] = array_merge($map,['depend_flag'=>$app]);
            }
            $flag = $hooksExtraModel->saveAll($apps_depends,false);
            if (false === $flag && $o_apps) {

            }
            return $flag;
        }

        return true;
    }

    /**
     * 去除应用所有钩子里对应的应用数据
     * @param  [type] $app_type 应用类型，1module，2plugin，3theme
     * @param  [type] $app_name 应用名称
     * @return [type] [description]
     * @date   2018-04-05
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function removeHooks($app_type,$app_name) {
        $hooksExtraModel = new HooksExtraModel;
        $depend_type = self::DEPEND_APP_TYPE[$app_type];
        $hooksExtraModel->where(['depend_type'=>$depend_type,'depend_flag'=>$app_name])->delete();
        return true;
    }

    /**
     * 根据已经安装的查找钩子
     * @param  string $depend_type [description]
     * @param  [type] $depend_flag [description]
     * @return [type] [description]
     * @date   2018-04-06
     * @author 心云间、凝听 <981248356@qq.com>
     */
    private function _getHooks($depend_type='',$depend_flag)
    {
        $hook_ids = HooksExtraModel::where(['depend_type'=>$depend_type,'depend_flag'=>$depend_flag])->column('hook_id');
        $hooks = $this->where('id','in',$hook_ids)->column('name');
        return $hooks;
    }
}
