<?php

namespace app\wechat\model;

use app\common\model\Base;
/**
 * 用户模型
 * @author 心云间、凝听 <981248356@qq.com>
 */
class WechatUser extends Base {

    // 定义时间戳字段名 
    protected $createTime = '';
    protected $updateTime = '';

    /**
     * 新增微信登录用户信息
     */
    public static function add($wxid,$weixin_user){

        if ($weixin_user['uid']>0) {
            $data = [
                'openid'.$wxid   => $weixin_user['openid'],
                'uid'            => $weixin_user['uid'],
                'nickname'       => $weixin_user['nickname'],
                'subscribe'      => isset($weixin_user['subscribe']) ? $weixin_user['subscribe']:'', 
                'subscribe_time' => isset($weixin_user['subscribe_time']) ? $weixin_user['subscribe_time']:'',
                'sex'            => $weixin_user['sex'],
                'city'           => $weixin_user['city'],
                'country'        => $weixin_user['country'],
                'province'       => $weixin_user['province'],
                'headimgurl'     => $weixin_user['headimgurl'],
                'unionid'        => isset($weixin_user['unionid']) ? $weixin_user['unionid']:'', 
                'last_update'    => isset($weixin_user['last_update']) ? $weixin_user['last_update']:'', 
            ];
            $info = self::create($data);
            return $info->id;
        }
        return false;
    }
}
