<?php
// 用户首页
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\user\controller;
use app\home\controller\Home;

use app\common\model\User as UserModel;
use app\common\logic\User as UserLogic;
class Index extends Home{
    function _initialize()
    {
        parent::_initialize();
        $this->userModel = new UserModel;
    }

    /*
     *  Description: 会员列表
     *  By: yyyvy  <QQ:76836785>
     *  Time: 2017-12-28 05:09:42
     * */
    public function index(){

        $map['status'] = 1; // 禁用和正常状态
        list($user_list,$total) = $this->userModel->getListByPage($map,'uid,username,nickname,avatar,reg_time','reg_time desc',20);
        $this->assign('user_list',$user_list);
        
        $this->pageInfo('会员列表','users');
        return $this->fetch();

    }

    /*
     *  Description: 会员主页
     *  By: yyyvy  <QQ:76836785>
     *  Time: 2017-12-28 06:55:11
     * */
    public function info($uid = 0){
        try {
            if ($uid>0) {
                $info = UserLogic::info($uid);
                $this->assign('info',$info);
                return $this->fetch();
            }
            throw new \Exception("用户ID不正确", 0);
            
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        
    }

}
