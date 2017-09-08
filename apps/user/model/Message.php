<?php
namespace app\user\model;

use app\common\model\Base;

class Message extends Base {

    protected $name = 'messages';
    protected $type = [
        'create_time'  =>  'timestamp:Y-m-d H:i:s',
    ];
    // /**
    //  * 自动验证规则
    //  * @author jry <598821125@qq.com>
    //  */
    // protected $_validate = array(
    //     array('title','require','标题必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    //     array('title', '1,1024', '标题长度为1-32个字符', self::EXISTS_VALIDATE, 'length', self::MODEL_BOTH),
    //     array('to_uid','require','收信人必须填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    // );

    protected $insert = ['is_read'=>0,'sort'=>0,'status'=>1];

    public function message_type($id) {
        $list[0] = '系统消息';
        $list[1] = '评论消息';
        return $id ? $list[$id] : $list;
    }

    /**
     * 发送消息
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function sendMessage($send_data, $extra = true) {
        $msg_data['title']    = $send_data['title']; //消息标题
        $msg_data['content']  = $send_data['content'] ? : $send_data['title']; //消息内容
        $msg_data['to_uid']   = $send_data['to_uid']; //消息收信人ID
        $msg_data['type']     = $send_data['type'] ? : 0; //消息类型
        $msg_data['from_uid'] = $send_data['from_uid'] ? : 0; //消息发信人

        $result = $this->data($msg_data)->save();
        if ($result) {
            if ($extra) {
                hook('SendMessage', $msg_data); //发送消息钩子，用于消息发送途径的扩展
            }
            return $result;
        }else{

        }

    }

    /**
     * 获取当前用户未读消息数量
     * @param $type 消息类型
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function newMessageCount($type = null,$box_type='inbox') {
        $map['status'] = ['eq', 1];
        if ($box_type=='outbox') {
            $map['from_uid'] = ['eq',is_login()];
        }elseif ($box_type=='inbox'){
            $map['to_uid'] = ['eq', is_login()];
        }
        
        $map['is_read'] = ['eq', 0];
        if($type !== null){
            $map['type'] = ['eq', $type];
        }
        return self::where($map)->count();
    }
}
