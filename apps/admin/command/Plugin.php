<?php
// 一键管理插件   
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\command;

use think\Config;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Exception;

class Plugin extends AppBase
{
    protected function configure()
    {
        $this->setName('plugin')
            ->addOption('name', 'a', Option::VALUE_REQUIRED, '插件名', null)
            ->addOption('action', 'c', Option::VALUE_REQUIRED, '操作方式(create/enable/disable/install/uninstall/refresh/upgrade/package)', 'create')
            ->addOption('title', 't', Option::VALUE_OPTIONAL, '插件名（中文）', null)
            ->setDescription('一键管理插件 ');
    }

    protected function execute(Input $input, Output $output)
    {
        //插件名称
        $name = $input->getOption('name') ?: '';
        //操作方式(create/enable/disable/install/uninstall/refresh/upgrade/package)
        $action = $input->getOption('action') ?: '';
        //插件名称（中文）
        $title = $input->getOption('title') ? : $name;
        if (!$name) {
            throw new Exception('插件名不能为空');
        }
        if (!$action || !in_array($action, ['create', 'disable', 'enable', 'install', 'uninstall', 'refresh', 'upgrade', 'package'])) {
            throw new Exception('请输入一个正确的操作类型');
        }

        $pluginDir = PLUGIN_PATH . $name . DS;
        switch ($action) {
            case 'create':
                $prefix = Config::get('database.prefix');

                $data = [
                    'name'  => $name,
                    'title' => $title,
                ];
                $write_files = [
                    ['name' =>'info','pathname'=>'/install/info.json'],
                    ['name' =>'install_sql','pathname'=>'/install/install.sql'],
                    ['name' =>'uninstall_sql','pathname'=>'/install/uninstall.sql'],
                    ['name' =>'menus','pathname'=>'/install/menus.php'],
                    ['name' =>'options','pathname'=>'/install/options.php'],
                    ['name' =>'admin_Example','pathname'=>'/admin/Example.php'],
                    ['name' =>'controller_Example','pathname'=>'/controller/Example.php'],
                    ['name' =>'model_Example','pathname'=>'/model/Example.php'],
                    ['name' =>'logic_LogicBase','pathname'=>'/logic/'.$name.'Logic.php'],
                    ['name' =>'logic_Example','pathname'=>'/logic/Example.php'],
                    ['name' =>'index','pathname'=>'/index.php'],
                    ['name'=>null,'pathname'=>'/static/js/'.$name.'.js'],
                    ['name'=>null,'pathname'=>'/static/css/'.$name.'.css'],
                ];
                foreach ($write_files as $key => $row) {
                    $this->writeToFile('plugin',$row['name'], $data, $pluginDir . $row['pathname']);
                }
                //试图修改目录用户组
                recurse_chown_chgrp_chmod($pluginDir,'www','www',0755);
                
                $output->info("创建插件成功!");
                break;
            case 'delete':
                //删除是危险操作
                
                // if ($name) {
                //     if (!is_writable(PLUGIN_PATH.$name)) {
                //         $output->error("请设置主题名");
                //     }
                //     @rmdirs(PLUGIN_PATH.$name);
                //     Extension::refresh('theme');
                //     $output->info("成功删除主题{$name}!");
                // } else{
                //     $output->error("请设置主题名");
                // }
                
                break;
            default:
                $output->writeln("未知操作");
                break;
        }
        
    }
}