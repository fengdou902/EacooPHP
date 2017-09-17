<?php
return array(
    'status'=>array(
        'title'=>'是否开启:',
        'type'=>'radio',
        'options'=>array(
            '1'=>'开启',
            '0'=>'关闭',
        ),
        'value'=>'0',
    ),
    'type'=>array(
        'title'=>'插件选择:',
        'type'=>'select',
        'options'=>array(
            'unslider'=>'Unslider',
            'flexslider'=>'FlexSlider'
        ),
        'value'=>0,
    ),
    'position'=>array(
        'title' => '推荐位序号',
        'tip'=>'<a href="/admin.php/admin/config/group/id/2" target="_blank">填写网站设置文档推荐位的序号</a>',
        'type'  => 'number',
        'value' => '1'
    ),
    'category'=>array(
        'title' => '分类ID',
        'tip'=>'不填写表示所有分类',
        'type'  => 'number',
        'value' => ''
    ),
    'sliders'=>array(
        'title' => '轮播图片',
        'type'  => 'repeater',
        'options'=>['options'=>
                [
                    'img'  =>['title'=>'图片','type'=>'image','default'=>'','placeholder'=>''],
                    'url'  =>['title'=>'链接','type'=>'url','default'=>'','placeholder'=>'http://'],
                    'text' =>['title'=>'文字','type'=>'text','default'=>'','placeholder'=>'输入文字'],
                ]],
        'value' => ''
    ),
    // 'images'=>array(
    //     'title' => '轮播图片',
    //     'type'  => 'pictures',
    //     'value' => ''
    // ),
    // 'url'=>array(
    //     'title'=>'图片链接',
    //     'tip'=>'一行对应一个图片',
    //     'type'=>'textarea',
    //     'value'=>''
    // ),
    // 'titles'=>array(
    //     'title'=>'图片文字',
    //     'tip'=>'一行对应一个图片',
    //     'type'=>'textarea',
    //     'value'=>''
    // ),
    'second'=>array(
        'title'=>'轮播间隔时间:',
        'tip'=>'（单位:毫秒）',
        'type'=>'text',
        'value'=>'3000', 
    ),
    'direction'=>array(
        'title'=>'图片滚动方向:',
        'type'=>'radio',
        'options'=>array(
            'horizontal'=>'横向滚动',
            'vertical'=>'纵向滚动',
        ),
        'value'=>'horizontal',
    ),
    'imgWidth'=>array(
        'title'=>'容器宽度',
        'tip'=>'（单位:像素）',
        'type'=>'number',
        'value'=>'960'
    ),
    'imgHeight'=>array(
        'title'=>'容器高度',
        'tip'=>'（单位:像素）',
        'type'=>'number',
        'value'=>'200'
    ),
    
);
                    