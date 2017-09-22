<?php
return [
    'status'=>[
        'title'=>'是否开启:',
        'type'=>'radio',
        'options'=>[
            1=>'开启',
            0=>'关闭',
        ],
        'value'=>1,
    ],
    'type'=>[
        'title'=>'插件选择:',
        'type'=>'select',
        'options'=>[
            'unslider'   =>'Unslider',
            'flexslider' =>'FlexSlider'
        ],
        'value'=>'flexslider',
    ],
    'sliders'=>[
        'title' => '轮播图片',
        'type'  => 'repeater',
        'options'=>[
            'options'=>
                [
                    'img'  =>['title'=>'图片','type'=>'image','default'=>'','placeholder'=>''],
                    'url'  =>['title'=>'链接','type'=>'url','default'=>'','placeholder'=>'http://'],
                    'text' =>['title'=>'文字','type'=>'text','default'=>'','placeholder'=>'输入文字'],
                ]
            ],
        'value' => ''
    ],
    'second'=>[
        'title' =>'轮播间隔时间:',
        'tip'   =>'（单位:毫秒）',
        'type'  =>'text',
        'value' =>'3000', 
    ],
    'direction'=>[
        'title'=>'图片滚动方向:',
        'type'=>'radio',
        'options'=>[
            'horizontal' =>'横向滚动',
            'vertical'   =>'纵向滚动',
        ],
        'value'=>'horizontal',
    ],
    'imgWidth'=>[
        'title' =>'容器宽度',
        'tip'   =>'（单位:像素）',
        'type'  =>'number',
        'value' =>''
    ],
    'imgHeight'=>[
        'title' =>'容器高度',
        'tip'   =>'（单位:像素）',
        'type'  =>'number',
        'value' =>'560'
    ],
    
];
                    