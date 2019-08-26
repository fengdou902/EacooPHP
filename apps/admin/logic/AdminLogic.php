<?php
// 后台逻辑层基类
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2019 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\logic;

use app\common\logic\Base as BaseLogic;

class AdminLogic extends BaseLogic {

    //依赖的应用对应的类型标识
    const DEPEND_APP_TYPE = [
        'module' => 1,
        'plugin' => 2,
        'theme'  => 3
    ];

    //依赖的应用对应的类型
    const DEPEND_TYPE_APP = [
        1=>'module',
        2=>'plugin',
        3=>'theme'
    ];

    public $currentUser = [];
    public $uid = 0;
    public $adminUid = 0;

	protected function initialize()
    {
        parent::initialize();
        $this->currentUser = session('admin_login_auth');
        $this->uid = $this->adminUid = is_admin_login();
    }

    /**
     * 校验当前用户是否允许同时登录
     * @return [type] [description]
     * @date   2018-02-28
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function checkAllowLoginByTime()
    {
        if (config('admin_allow_login_many')==1) {
            return true;
        } elseif (session('admin_activation_auth_sign') == model('admin/AdminUser')->where('uid',is_admin_login())->value('activation_auth_sign')) {
            return true;
        }
        return false;
    }
}