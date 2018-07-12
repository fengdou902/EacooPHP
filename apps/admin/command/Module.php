<?php
// 一键管理模块   
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

class Module extends AppBase
{
    protected function configure()
    {
        $this->setName('module')
            ->addOption('name', 'a', Option::VALUE_REQUIRED, '模块名', null)
            ->addOption('action', 'c', Option::VALUE_REQUIRED, '操作方式(create/enable/disable/install/uninstall/refresh/upgrade/package)', 'create')
            ->addOption('title', 't', Option::VALUE_OPTIONAL, '模块名（中文）', null)
            ->setDescription('一键管理模块 ');
    }

    protected function execute(Input $input, Output $output)
    {
        //模块名称
        $name = $input->getOption('name') ?: '';
        //操作方式(create/enable/disable/install/uninstall/refresh/upgrade/package)
        $action = $input->getOption('action') ?: '';
        //模块名称
        $title = $input->getOption('title') ?: $name;
        if (!$name) {
            throw new Exception('模块名不能为空');
        }
        if (!$action || !in_array($action, ['create', 'disable', 'enable', 'install', 'uninstall', 'refresh', 'upgrade', 'package'])) {
            throw new Exception('请输入一个正确的操作类型');
        }

        $moduleDir = APP_PATH . $name . DS;
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
                    ['name' =>'common','pathname'=>'/common.php'],
                    ['name' =>'config','pathname'=>'/config.php'],
                ];
                foreach ($write_files as $key => $row) {
                    $this->writeToFile('module',$row['name'], $data, $moduleDir . $row['pathname']);
                }
                //试图修改目录用户组
                recurse_chown_chgrp_chmod($moduleDir,'www','www',0755);
                $output->info("创建模块成功!");
                break;
            
            default:
                $output->writeln("未知操作");
                break;
        }
        
    }
}