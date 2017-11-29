<?php

return [

    // 后台菜单及权限节点配置
    'admin_menus' =>[
        [
            'title'=>'微信',
            'name' =>'wechat',
            'icon' => 'fa fa-wechat',
            'is_menu'=>1,
            'sub_menu'=>[
                [
                    'title'=>'公众号管理',
                    'name' => 'wechat/wechat/index',
                    'is_menu'=>1
                ],
                [
                    'title'=>'自动回复',
                    'name' => 'wechat/Reply/keyword',
                    'is_menu'=>1
                ],
                [
                    'title'=>'素材管理',
                    'name' => 'wechat/Material/text',
                    'is_menu'=>1
                ],
                // [
                //     'title'=>'自定义菜单',
                //     'name' => 'wechat/Menu/index',
                //     'is_menu'=>1
                // ],
                // [
                //     'title'=>'场景二维码',
                //     'name' => 'wechat/Qrcode/index',
                //     'is_menu'=>1
                // ],
                [
                    'title'=>'微信用户列表',
                    'name' => 'wechat/WechatUser/index',
                    'is_menu'=>1
                ],
                // [
                //     'title'=>'消息列表',
                //     'name' => 'wechat/Message/index',
                //     'is_menu'=>1
                // ],
                // [
                //     'title'=>'微信客服',
                //     'name' => 'wechat/kefu/index',
                //     'is_menu'=>1
                // ],
            ],
        ]
        
    ],
];