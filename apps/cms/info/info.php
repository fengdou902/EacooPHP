<?php

// 模块信息配置
return array(
    // 模块信息
    'info' => array(
        'name'        => 'cms',
        'title'       => 'CMS',
        'description' => '内容管理系统，门户网站建设方案',
        'developer'   => '心云间、凝听',
        'version'     => '1.0.0',
        // 'dependences' => array(
        //     'Admin'   => '1.1.0',
        // )
    ),

    // 用户中心导航
    'user_nav' => array(
        'center' => array(
            '0' => array(
                'title' => '我的文档',
                'icon'  => 'fa fa-list',
                'url'   => 'Cms/Index/my',
            ),
        ),
    ),

    // 模块配置
    'config' => array(
        'need_check' => array(
            'title'   => '前台发布审核',
            'type'    => 'radio',
            'options' => array(
                '1'   => '需要',
                '0'   => '不需要',
            ),
            'value'   => '0',
        ),
        'toggle_comment' => array(
            'title'  => '是否允许评论文档',
            'type'   =>'radio',
            'options' => array(
                '1'   => '允许',
                '0'   => '不允许',
            ),
            'value'  => '1',
        ),
        'group_list' => array(
            'title'  => '栏目分组',
            'type'   =>'array',
            'value'  => '1:默认',
        ),
        'cate' => array(
            'title'  => '首页栏目自定义',
            'type'   =>'array',
            'value'  => 'a:1',
        ),
        'taglib' => array(
            'title'  => '加载标签库',
            'type'   =>'checkbox',
            'options'=> array(
                'Cms' => 'Cms',
            ),
            'value'  => array(
                '0'  => 'Cms',
            ),
        ),
    ),

    // 后台菜单及权限节点配置
    'admin_menu' => array(
        '0' => array(
            'title' =>'内容管理',
            'icon'  =>'icon-jishiben',
            'name'  =>'Cms/AdminCms',
            'is_menu'=>1,
            'sub_menu'=>array(
                        '0' => array(
                            'title' => '文章管理',
                            'icon'  => 'fa fa-edit',
                            'name'  => 'Cms/AdminCms/index',
                            'is_menu'=>1,
                            'sub_menu'=>array(
                                    '0' => array(
                                        'title' => '文章编辑',
                                        'name'  => 'Cms/AdminCms/edit',
                                        'is_menu'=>1,
                                    ),
                                )
                        ),
                        '1' => array(
                            'title' => '文章配置',
                            'icon'  => 'fa fa-wrench',
                            'name'   => 'Cms/AdminCms/module_config',
                            'is_menu'=>1,
                        ),
                        '2' => array(
                            'title' => '字段管理',
                            'icon'  => 'fa fa-database',
                            'name'   => 'Cms/AdminCms/Attribute',
                            'is_menu'=>1,
                            'sub_menu'=>array(
                                        '0' => array(
                                            'title' => '文章编辑',
                                            'name'   => 'Cms/AdminCms/AttrEdit',
                                            'is_menu'=>0,
                                        ),
                                        '1' => array(
                                            'pid'   => '8',
                                            'title' => '设置状态',
                                            'name'   => 'Cms/AdminCms/setAttrStatus',
                                            'is_menu'=>0,
                                        ),
                                )
                        ),
                        '4' => array(
                            'title' => '页面管理',
                            'icon'  => 'fa fa-edit',
                            'name'   => 'Cms/AdminCms/page',
                            'is_menu'=>1,
                            'sub_menu'=>array(
                                        '0' => array(
                                            'title' => '页面编辑',
                                            'name'   => 'Cms/AdminCms/edit',
                                            'is_menu'=>0,
                                        ),
                                    )
                        ),
                        '5' => array(
                            'title' => '回收站',
                            'icon'  => 'fa fa-recycle',
                            'name'   => 'Cms/AdminCms/trash',
                            'is_menu'=>1,
                        ),
                )
        ),
        
    )
);
