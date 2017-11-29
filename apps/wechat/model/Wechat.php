<?php 

namespace app\wechat\model;

use app\common\model\Base;

/**
 * 微信公众号管理模型
 * @author 心云间、凝听 <981248356@qq.com>
 */
class Wechat extends Base {
    
    // 定义时间戳字段名 
    protected $updateTime = '';
    /**
     * 自动完成
     * @author 心云间、凝听 <981248356@qq.com>
     */
    protected $insert=['status'=>1,'valid_token','token','encodingaeskey'];

    protected function setValidTokenAttr($value, $data)
    {
        return create_rand();
    }

    protected function setTokenAttr($value, $data)
    {
        return $this->get_token();
    }

    protected function setEncodingaeskeyAttr($value, $data)
    {
        return $this->get_encodingaeskey();
    }
    /**
     * 获取公众号标识
     * @author 心云间、凝听 <981248356@qq.com>
     */
    protected function get_token() {
        return md5(input('origin_id'));
    }

    /**
     * 获取消息加解密秘钥
     * @author 心云间、凝听 <981248356@qq.com>
     */
    protected function get_encodingaeskey() {
        return create_rand(43);
    }

    /**
     * 获取公众号列表
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function get_weixin_lists($user_id) {
        if (empty($user_id)) {
            return false;
        }
        $map['user_id'] = $user_id;
        $weixin_lists = $this->where($map)->select();
        return $weixin_lists;
    }

    /**
     * 获取公众号信息
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getWechatInfo($wxid = '') {
        !$wxid && $wxid = get_wxid();
        if (!$wxid) {
            return false;
        }
        $map['id'] = $wxid;
        $weixin_info = $this->where($map)->find();
        return $weixin_info;
    }
    
    /**
     * 公众号类型（1：普通订阅号；2：认证订阅号；3：普通服务号；4：认证服务号
     */
    public function weixinType($id=0) {
        $list = [
            1=>'普通订阅号',
            2=>'认证订阅号',
            3=>'普通服务号',
            4=>'认证服务号',
        ];
        return $id ? $list[$id] : $list;
    }
}
