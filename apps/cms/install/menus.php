<?php

return [
    // 导航
    'navigation' => [
        'top' =>[
            'title' => '我的文档',
            'icon'  => 'fa fa-list',
            'url'   => 'cms/Index/my',
        ],
        'usercenter' => [
                    [
                        'title' => '我的文章',
                        'icon'  => 'fa fa-list',
                        'url'   => 'cms/Index/my',
                    ],
        ],
    ],

    // 后台菜单及权限节点配置
    'admin_menus' =>[
        [
            'title'=>'门户CMS',
            'name' =>'cms/posts',
            'icon' => 'fa fa-file-text',
            'is_menu'=>1,
            'sub_menu'=>[
                [
                    'title'=>'文章列表',
                    'name' => 'cms/posts/index',
                    'is_menu'=>1
                ],
                [
                    'title'=>'文章编辑',
                    'name' => 'cms/posts/edit',
                    'is_menu'=>0
                ],
                [
                    'title'=>'文章分类',
                    'name' => 'cms/category/index',
                    'is_menu'=>1
                ],
                [
                    'title'=>'文章标签',
                    'name' => 'cms/category/index?taxonomy=post_tag',
                    'is_menu'=>1
                ],
                [
                    'title'=>'页面列表',
                    'name' => 'cms/page/index',
                    'is_menu'=>1
                ],
                [
                    'title'=>'回收站',
                    'name' => 'cms/posts/trash',
                    'is_menu'=>1
                ],
            ],
        ]
        
    ],
];