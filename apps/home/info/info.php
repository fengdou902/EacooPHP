<?php

// 模块信息配置
return array(
    // 模块信息
    'info' => array(
        'name'        => 'home',
        'title'       => '前台模块',
        'description' => '一款基础前台模块',
        'developer'   => '心云间、凝听',
        'version'     => '1.0.0',
        /*'dependences' => array(
            'Admin'   => '1.1.0',
        )*/
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
                'Comment' => 'Comment',
            ),
            'value'  => array(
                '0'  => 'Comment',
            ),
        ),
    ),

);
