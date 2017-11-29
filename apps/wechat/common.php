<?php 
/**
 * 微信用户数据
 *
 * @param $fields array|string 如果是数组，则返回数组。如果不是数组，则返回对应的值
 * @param null $uid
 * @return array|null
 */
function weixin_userdata($openid)
{
    $weixinUser=cache('weixin_userdata_'.$openid);
    if (!$weixinUser||!$weixinUser['openid1']) {
    		$weixinUser=M('weixin_user')->where(array('openid1'=>$openid))->find();
        cache('weixin_userdata_'.$openid,$weixinUser,86400*3);
    }
    return $weixinUser;
}