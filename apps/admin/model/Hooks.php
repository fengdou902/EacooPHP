<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.eacoo123.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use app\common\model\Base;
/**
 * 插件钩子模型
 * 该类参考了OneThink的部分实现
 */
class Hooks extends Base {

    protected $insert =['status'=>1];

    /**
    * 获取件所需的钩子是否存在，没有则新增
    * @param string $str  钩子名称
    * @param string $addons  插件名称
    * @param string $addons  件简介
    */
    public function existHook($name, $data){
        $map = [
            'name'=>$name
        ];
        $gethook = $this->where($map)->find();
        $gethook = $gethook->toArray();
        if (!$gethook || empty($gethook) || !is_array($gethook)) {
            $data['name']        = $name;
            $data['description'] = $data['description'];
            $data['type']        = 1;
            $this->allowField(true)->isUpdate(false)->data($data)->save();
        }
    }

    /**
     * 更新插件里的所有钩子对应的插件
     * @param  [type] $addons_name [description]
     * @return [type] [description]
     * @date   2017-09-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function updateHooks($addons_name) {
        $addons_class = get_addon_class($addons_name);//获取插件名
        if (!class_exists($addons_class)) {
            $this->error = "未实现{$addons_name}插件的入口文件";
            return false;
        }
        $methods = get_class_methods($addons_class);
        $hooks = $this->column('name');
        $common = array_intersect($hooks, $methods);
        if (!empty($common)) {
            foreach ($common as $hook) {
                $flag = $this->updateAddons($hook, array($addons_name));
                if (false === $flag) {
                    $this->removeHooks($addons_name);
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 更新单个钩子处的插件
     * @param  [type] $hook_name [description]
     * @param  [type] $addons_name [description]
     * @return [type] [description]
     * @date   2017-09-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function updateAddons($hook_name, $addons_name) {
        $o_addons = $this->where("name='{$hook_name}'")->value('addons');
        if ($o_addons) {
            $o_addons = explode(',', $o_addons);
        }
        if ($o_addons) {
            $addons = array_merge($o_addons, $addons_name);
            $addons = array_unique($addons);
        } else {
            $addons = $addons_name;
        }
        $flag = $this->where("name='{$hook_name}'")
                     ->setField('addons',implode(',', $addons));
        if (false === $flag) {
            $this->where("name='{$hook_name}'")
                 ->setField('addons',implode(',', $o_addons));
        }
        return $flag;
    }

    /**
     * 去除插件所有钩子里对应的插件数据
     * @param  [type] $addons_name [description]
     * @return [type] [description]
     * @date   2017-09-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function removeHooks($addons_name) {
        $addons_class = get_addon_class($addons_name);
        if (!class_exists($addons_class)) {
            return false;
        }
        $methods = get_class_methods($addons_class);
        $hooks   = $this->column('name');
        $common  = array_intersect($hooks, $methods);
        if ($common) {
            foreach ($common as $hook) {
                $flag = $this->removeAddons($hook, array($addons_name));
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
     * @param  [type] $addons_name [description]
     * @return [type] [description]
     * @date   2017-09-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function removeAddons($hook_name, $addons_name) {
        $o_addons = $this->where("name='{$hook_name}'")->value('addons');
        $o_addons = explode(',', $o_addons);
        if ($o_addons) {
            $addons = array_diff($o_addons, $addons_name);
        } else {
            return true;
        }
        $flag = $this->where("name='{$hook_name}'")
                     ->setField('addons',implode(',', $addons));
        if (false === $flag) {
            $this->where("name='{$hook_name}'")
                 ->setField('addons',implode(',', $o_addons));
        }
        return $flag;
    }
}
