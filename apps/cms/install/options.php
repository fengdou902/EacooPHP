<?php
// 模块配置
return [
    'need_check' => [
        'title'   => '前台发布审核',
        'type'    => 'radio',
        'options' => [
            1 => '需要',
            0 => '不需要',
        ],
        'value'   => '0',
    ],
    'toggle_comment' => [
        'title'  => '是否允许评论文档',
        'type'   =>'radio',
        'options' => [
            '1'   => '允许',
            '0'   => '不允许',
        ],
        'value'  => '1',
    ],
    'group_list' => [
        'title'  => '栏目分组',
        'type'   =>'array',
        'value'  => '1:默认',
    ],
    'cate' => [
        'title'  => '首页栏目自定义',
        'type'   =>'array',
        'value'  => 'a:1',
    ],
    'taglib' => [
        'title'  => '加载标签库',
        'type'   =>'checkbox',
        'options'=> array(
            'cms' => 'cms',
        ),
        'value'  => array(
            '0'  => 'cms',
        ),
    ],

];