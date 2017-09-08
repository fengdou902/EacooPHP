<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // 生成应用公共文件
    '__file__' => ['common.php', 'config.php', 'database.php'],
    // // 定义demo模块的自动生成 （按照实际定义的文件名生成）
    // 'Admin'     => [
    //     '__file__'   => ['common.php','config.php'],
    //     '__dir__'    => ['behavior', 'controller', 'model', 'view','validate'],
    //     'controller' => ['Index', 'Access','System'],
    //     'model'      => ['AuthGroup','AuthGroupAccess','Config','Member'],
    //     'view'       => [],
    // ],
    // // 定义demo模块的自动生成 （按照实际定义的文件名生成）
    // 'common'     => [
    //     '__file__'   => ['common.php'],
    //     '__dir__'    => ['behavior', 'controller', 'model', 'view'],
    //     'controller' => ['Base', 'Admin', 'Api', 'Fornt','Upload', 'User'],
    //     'model'      => ['Common', 'Hooks','User','Tree'],
    //     'view'       => [],
    // ],
    // 定义demo模块的自动生成 （按照实际定义的文件名生成）
    'User'     => [
        '__file__'   => ['common.php','config.php'],
        '__dir__'    => ['behavior', 'controller', 'model', 'view','validate'],
        'controller' => ['Index'],
        'model'      => ['User'],
        'view'       => [],
    ],
    // 其他更多的模块定义
];
