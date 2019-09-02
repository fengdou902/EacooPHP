<?php
// 后台插件处理逻辑      
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2019 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\logic;

class Plugin extends AdminLogic
{
    static public $pluginName;

    protected $name = 'plugins';

    /**
     * 检测是否安装了某个插件
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