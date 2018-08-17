<?php
// 一键管理主题 
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

use app\admin\logic\Extension as ExtensionLogic;

class Theme extends AppBase
{

    protected function configure()
    {
        $this->setName('theme')
            ->addOption('name', 'a', Option::VALUE_REQUIRED, '主题名', null)
            ->addOption('action', 'c', Option::VALUE_REQUIRED, '操作方式(create/install/uninstall/refresh/delete/upgrade/package)', 'create')
            ->addOption('title', 't', Option::VALUE_OPTIONAL, '主题名（中文）', null)
            ->setDescription('一键管理主题 ');
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
            throw new Exception('主题名不能为空');
        }
        if (!$action || !in_array($action, ['create', 'delete', 'install', 'uninstall', 'refresh', 'upgrade', 'package'])) {
            throw new Exception('请输入一个正确的操作类型');
        }

        defined('THEME_PATH') or define('THEME_PATH',ROOT_PATH.'public/themes/');
        $themeDir = THEME_PATH. $name . DS;
        switch ($action) {
            case 'create':
                $prefix = Config::get('database.prefix');

                $data = [
                    'name'  => $name,
                    'title' => $title,
                ];
                $write_files = [
                    ['name' =>'info','pathname'=>'/install/info.json'],
                    ['name' =>'menus','pathname'=>'/install/menus.php'],
                    ['name' =>'options','pathname'=>'/install/options.php'],
                    ['name' =>'home_index_template','pathname'=>'/home/index/index.html'],
                    ['name'=>'layout_template','pathname'=>'/public/layout.html'],
                    ['name'=>'common_js','pathname'=>'/public/js/common.js'],
                    ['name'=>'style_css','pathname'=>'/public/css/style.css'],
                    ['name'=>null,'pathname'=>'/public/img/test.png'],
                ];
                foreach ($write_files as $key => $row) {
                    $this->writeToFile('theme',$row['name'], $data, $themeDir . $row['pathname']);
                }

                //试图修改目录用户组
                recurse_chown_chgrp_chmod($themeDir,'www','www',0755);
                $output->info("创建主题成功!");
                break;
            case 'delete':
                //删除是危险操作
                
                if ($name) {
                    if (!is_writable(THEME_PATH.$name)) {
                        $this->error('目录权限不足，请手动删除目录');
                    }
                    @rmdirs(THEME_PATH.$name);
                    ExtensionLogic::refresh('theme');
                    $output->info("成功删除主题{$name}!");
                } else{
                    $output->warning("请设置主题名");
                }
                
                break;
            default:
                $output->writeln("未知操作");
                break;
        }
        
    }
}