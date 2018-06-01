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
    'post_type'=>[
        'title' => '文档类型',
        'type'  => 'repeater',
        'options'=>[
            'options'=>
                [
                    'name'  =>['title'=>'类型名称','type'=>'text','default'=>'','placeholder'=>'只限英文'],
                    'title'  =>['title'=>'类型标题','type'=>'text','default'=>'','placeholder'=>'中文标题'],
                ]
            ],
        'value' => [
                ['name'=>'post','title'=>'文章'],
                ['name'=>'page','title'=>'页面'],
                ['name'=>'product','title'=>'产品'],
            ]
    ],
];