<?php

return [
    'status'=>[
        'title' =>'是否开启短信接口:',
        'type'  =>'radio',
        'options'=>[
            '1'=>'开启',
            '0'=>'关闭',
        ],
        'value'=>'1',
    ],
    'appkey'=>[
        'title'=>'APPKEY:',
        'type'=>'text',
        'value'=>'',
        'tip'=>'请通过<a href="http://www.alidayu.com" target="_blank">www.alidayu.com</a>申请',
    ],
    'secret'=>[
        'title' => 'SECRET:',
        'type'  => 'text',
        'value' => '',
        'tip'=>'请通过<a href="http://www.alidayu.com" target="_blank">www.alidayu.com</a>申请',
    ],
];
