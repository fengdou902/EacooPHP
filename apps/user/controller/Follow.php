<?php
// 关注
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\user\controller;
use app\home\controller\Home;

use app\common\model\User as UserModel;
class Follow extends Home{
    function _initialize()
    {
        parent::_initialize();
        $this->userModel = new UserModel;
    }

    /**
     * 获取插件数据例子
     * @return [type] [description]
     * @date   2018-01-06
     * @author 心云间、凝听 <981248356@qq.com>
     * {:URL('user/follow/index',['hook'=>'attention','type'=>'watchlist','uid'=>$info.uid])}
     * 参数：hook是插件名字 *必填
     * 参数：type是插件方法 *必填
     * 参数：uid选填项，可不填
     */
    public function index(){

      hook(input('hook'),input(),true);

        //例子1：获取关注数量
        /*$result = hook('attention',[],true);
        dump($result);*/

        //例子2：获取Ta关注数量
        //$result = hook('attention',['type'=>'watchlist'],true);
        //dump($result);

      //hook('attention',['type'=>'fanslist'],true);

        // 非钩子方法，在需要的地方调用该方法：
        /*$plugin_class = get_plugin_class('attention');
        if (!class_exists($plugin_class)) {
            echo 'fail';
            exit;
        }
        $plugin_obj    = new $plugin_class;
        $result = $plugin_obj->getData();
        dump($result);*/
    }

}
