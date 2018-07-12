<?php
// 一键管理应用中心 
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\command;

use think\console\Command;
use think\Exception;

class AppBase extends Command
{
    /**
     * 写入到文件
     * @param  [type] $type 类型。module,plugin,theme
     * @param  [type] $name [description]
     * @param  [type] $data [description]
     * @param  [type] $pathname [description]
     * @return [type] [description]
     * @date   2018-05-30
     * @author 心云间、凝听 <981248356@qq.com>
     */
    protected function writeToFile($type,$name=null, $data, $pathname)
    {
        if (!empty($data)) {
            $search = $replace = [];
            foreach ($data as $k => $v) {
                $search[] = "{%{$k}%}";
                $replace[] = $v;
            }
        }
        
        if ($name) {
            $stub = file_get_contents($this->getTemp($type,$name));
            $content = str_replace($search, $replace, $stub);
        }
        
        if (!is_dir(dirname($pathname))) {
            mkdir(strtolower(dirname($pathname)), 0755, true);
        }

        return !empty($content) ? file_put_contents($pathname, $content):true;
    }

    /**
     * 获取基础模板
     * @param  [type] $type 类型。module,plugin,theme
     * @param  [type] $name [description]
     * @return [type] [description]
     * @date   2018-05-30
     * @author 心云间、凝听 <981248356@qq.com>
     */
    protected function getTemp($type,$name)
    {
        return __DIR__ . '/app/'.$type.'/stubs/' . $name . '.stub';
    }
}