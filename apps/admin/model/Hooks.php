<?php
// 钩子模型
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
use app\admin\model\Plugins;
/**
 * 插件钩子模型
 * 该类参考了OneThink的部分实现
 */
class Hooks extends Base {

    protected $insert =['status'=>1];

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
     * 更新插件里的所有钩子对应的插件
     * @param  [type] $plugins_name [description]
     * @return [type] [description]
     * @date   2017-09-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function updateHooks($plugins_name) {
        $plugins_class = get_plugin_class($plugins_name);//获取插件名
        if (!class_exists($plugins_class)) {
            $this->error = "未实现{$plugins_name}插件的入口文件";
            return false;
        }
        $methods = get_class_methods($plugins_class);

        $hooks = $this->column('name');
        $common = array_intersect($hooks, $methods);
        if (!empty($common)) {
            foreach ($common as $hook) {
                $flag = $this->updatePlugins($hook, array($plugins_name));
                if (false === $flag) {
                    $this->removeHooks($plugins_name);
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 更新单个钩子处的插件
     * @param  [type] $hook_name [description]
     * @param  [type] $plugins_name [description]
     * @return [type] [description]
     * @date   2017-09-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function updatePlugins($hook_name, $plugins_name) {
        $map = [
            'name'=>$hook_name
        ];
        $o_plugins = $this->where($map)->value('plugins');
        if ($o_plugins) {
            $o_plugins = explode(',', $o_plugins);
        }
        if ($o_plugins) {
            $plugins = array_merge($o_plugins, $plugins_name);
            $plugins = array_unique($plugins);
        } else {
            $plugins = $plugins_name;
        }
        $flag = $this->where($map)
                     ->setField('plugins',implode(',', $plugins));
        if (false === $flag) {
            $this->where($map)
                 ->setField('plugins',implode(',', $o_plugins));
        }
        return $flag;
    }

    /**
     * 去除插件所有钩子里对应的插件数据
     * @param  [type] $plugins_name [description]
     * @return [type] [description]
     * @date   2017-09-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function removeHooks($plugins_name) {

        $plugin_class = get_plugin_class($plugins_name);
        if (!class_exists($plugin_class)) {
            return false;
        }
        $methods = get_class_methods($plugin_class);
        $hooks   = $this->column('name');
        $common  = array_intersect($hooks, $methods);
        if ($common) {
            foreach ($common as $hook) {
                $flag = $this->removePlugins($hook, array($plugins_name));
                if (false === $flag) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 去除单个钩子里对应的插件数据
     * @param  [type] $hook_name [description]
     * @param  [type] $plugins_name [description]
     * @return [type] [description]
     * @date   2017-09-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function removePlugins($hook_name, $plugins_name) {
        $o_plugins = $this->where("name='{$hook_name}'")->value('plugins');
        $o_plugins = explode(',', $o_plugins);
        if ($o_plugins) {
            $plugins = array_diff($o_plugins, $plugins_name);
        } else {
            return true;
        }
        $flag = $this->where("name='{$hook_name}'")
                     ->setField('plugins',implode(',', $plugins));
        if (false === $flag) {
            $this->where("name='{$hook_name}'")
                 ->setField('plugins',implode(',', $o_plugins));
        }
        return $flag;
    }
}
