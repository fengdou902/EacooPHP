<?php
// 一键管理插件   
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class Plugin extends Command
{
    protected function configure()
    {
        $this->setName('plugin')->setDescription('一键管理插件 ');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("TestCommand:");
    }
}