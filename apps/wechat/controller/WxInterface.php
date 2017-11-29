<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.eacoomall.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\wechat\controller;
//use Wechat\Controller\HomeController;
use think\Controller;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\Text;
use EasyWeChat\Message\Image;
use EasyWeChat\Message\Video;
use EasyWeChat\Message\Voice;
use EasyWeChat\Message\News;
use EasyWeChat\Message\Article;

use app\wechat\model\Kfpush;
use app\wechat\model\Reply;
use app\wechat\model\Material;
use app\wechat\model\WechatUser;
use app\common\model\User;

class WxInterface extends Controller {

    protected $wxid;
    
    protected function _initialize()
    {
        $this->wxid    = get_wxid($this->request->param('wxid'));
        $wechat_option = get_wechat_info($this->wxid);
        $options = [
           /**
             * Debug 模式，bool 值：true/false
             *
             * 当值为 false 时，所有的日志都不会记录
             */
            'debug'  => true,
            /**
             * 账号基本信息，请从微信公众平台/开放平台获取
             */
            'app_id'  => $wechat_option['appid'],         // AppID
            'secret'  => $wechat_option['appsecret'],     // AppSecret
            'token'   => $wechat_option['valid_token'],   // Token
            'aes_key' => $wechat_option['encodingaeskey'],  // EncodingAESKey，安全模式下请一定要填写！！！
            /**
             * 日志配置
             *
             * level: 日志级别, 可选为：
             *         debug/info/notice/warning/error/critical/alert/emergency
             * permission：日志文件权限(可选)，默认为null（若为null值,monolog会取0644）
             * file：日志文件位置(绝对路径!!!)，要求可写权限
             */
            'log' => [
                'level'      => 'debug',
                'permission' => 0777,
                'file'       => 'runtime/log/wechat/easywechat.logs',
            ],
        ];
        $this->app = new Application($options);
        
    }

    /**
     * 默认方法
     */
    public function index() {

        // $data = $this->wechatObj->getRev()->getRevData();//获取微信服务器发来信息(不返回结果)并返回微信服务器发来的信息（数组）
        // if (! empty ( $data ['ToUserName'] ))
        // {
        //     get_wechat_token ( $data ['ToUserName'] );
        // }
        // if (! empty ( $data ['FromUserName'] ))
        // {
        //     get_openid ( $data ['FromUserName'] );
        // }

        // 回复数据
        $this->reply();
        exit ();
    }

    /**
     * 微信回复数据
     *
     */
    private function reply()
    {
        $server = $this->app->server;
        $data = $server->getMessage();

        $key = $data ['Content'];
        $keywordArr = [];
        /**
         * 微信事件转化成特定的关键词来处理
         * event可能的值：
         * subscribe : 关注公众号
         * unsubscribe : 取消关注公众号
         * scan : 扫描带参数二维码事件
         * LOCATION : 上报地理位置事件
         * click : 自定义菜单事件
         */
        if ($data ['MsgType'] == 'event') {
            
            if ( $data['Event'] == 'LOCATION' ) {
                $event = 'report_location';
            } else {
                $event = strtolower ( $data ['Event'] );
            }
            
            if ($event == 'click' && ! empty ( $data ['EventKey'] )) {
                $key = $data ['Content'] = $data ['EventKey'];
            } else {
                $key = $data ['Content'] = $event;
            }
        } else {

            /**
             * 非事件型消息处理逻辑
             * event可能的值：
             * text : 文本消息
             * image : 图片消息
             * voice : 语音消息
             * video : 视频消息
             * shortvideo : 短视频消息
             * location : 地理位置消息
             * link : 链接消息
             */
            $event = strtolower ( $data ['MsgType'] );
            // 数据保存到消息管理中
            if($data) db('wechat_message' )->insert($data);
        }
        $reply_map         = [];
        $reply_map['type'] = $event=='text' ? 'keyword' : $event;
        $reply_map['wxid'] = $this->wxid;
        //$this->wechatObj->text("debug:".$this->wxid)->reply();
        
        
        switch ($data['MsgType']) {
            case 'event':
                break;
            case 'text':
                $reply_map['keyword']=$key;
                //$this->kfpush($data);//添加订阅事件
                break;
            case 'image':
                break;
            case 'voice':
                break;
            case 'video':
                break;
            case 'link':
                break;
            case 'shortvideo':
                break;
            case 'report_location':
                break;
            case 'scan':
                break;
            case 'location':
                break;
            case 'link':
                break;
            case 'subscribe':
                $this->welcome($data);
                break;
            case 'unsubscribe':
                $this->unsubscribe($data);
                break;
            case 'click'://自定义菜单事件
                $this->kfpush($data);//添加订阅事件
                $reply_map['type']    = 'keyword';//转换为文本回复
                $reply_map['keyword'] = $key;
                break;
            // ... 其它消息
            default:
                $server->setMessageHandler(function ($data) {
                    return "您好！您想让我告诉您什么~~";
                });
                break;
        }
        
        $material_id = Reply::where($reply_map)->value('material_id');
        if (!$material_id) {
            $material_id = Reply::where(['type'=>'default'])->value('material_id');
        }
         $this->replyEvent($material_id);
        exit;
    }

    /**
     * 微信事件内容回复
     *
     */
    private function replyEvent($material_id){

       $info = Material::get($material_id);
       if(!$info) return false;
       $server = $this->app->server;
       $server->setMessageHandler(function ($message) {
            switch ($info['type']) {
                case 'text':
                   return new Text(['content' => $info['content']]);
                   break;
                case 'image':
                    return new News([
                            'title'       => '这是一张图片',
                            'description' => '点击查看大图',
                            'image'       => get_image($info['attachment_id'],'original'),
                            'url'         => $info['url']
                        ]);
                   break;
                case 'news': 
                   return new News([
                            'title'       => $info['title'],
                            'description' => $info['description'],
                            'image'       => get_image($info['attachment_id'],'original'),
                            'url'         => $info['url']
                        ]);
                   break;
                case 'voice':
                   return new Voice(['media_id' => $info['wx_media_id']]);
                   break;
                case 'video':
                   return new Video([
                                'title' => $info['title'],
                                'media_id' => $mediaId,
                                'description' => $info['description'],
                                'thumb_media_id'=>'',
                                // ...
                            ]);
                   break;
               
               default:
                   return new Text(['content' => $info['content']]);
                   break;
           }
        });

    }

    /**
     * 初始化用户关注
     *
     */
    private function welcome($data){
        // 初始化用户信息
        $this->regWechatUser($data['FromUserName']);

        $reply_map  = $map = $articles = [];
        $reply_map = [
                'wxid' => $this->wxid,
                'type' => 'subscribe',
        ];
        $material_id  = Reply::where($reply_map)->value('material_id');
        if ($is_group = 1) {//是否开启图文组(后期完善后台)
            $map = [
                'type'     => 'news',
                'status'   => 1,
                'group_id' => $material_id,//推送的图文组
            ];
            $data_list = Material::where($map)->field('id,title,type,description,attachment_id,url,group_id')->order('create_time asc')->limit(8)->select();
            if ($data_list) {
                $articles = [];
                foreach ($data_list as $key => $row) {
                    $articles[] = new News([
                                    'title'       => $row['title'],
                                    'description' => $row['description'],
                                    'url'         => $row['url'] ? htmlspecialchars_decode($row['url']) : $this->request->domain().'/'.url('wechat/index/news_detail',['id'=>$row['id']]),
                                    'image'       => get_image($row['attachment_id'],'medium'),
                                ]);
                }
            }
            return $articles;
        } else{
            $this->replyEvent($material_id);
        }
        $this->kfpush($data);//添加订阅事件
        exit;
    }

    /**
     * 用户取消关注
     *
     */
    private function unsubscribe($data){
        // 初始化用户信息
        //$map ['token'] = $data ['ToUserName'];
        $map ['openid'.$this->wxid] = $data ['FromUserName'];
        $count = WechatUser::where($map)->count();
        if ($count>0) {
            WechatUser::where($map)->update(['subscribe'=>0]);
            Kfpush::destroy(['openid'=>$data ['FromUserName'],'wxid'=>$this->wxid]);
        }

        $reply_map         = [];
        $reply_map = [
                'wxid' => $this->wxid,
                'type' => 'unsubscribe',
        ];
        $material_id  = Reply::where($reply_map)->value('material_id');
        $this->replyEvent($material_id);
        exit;
    }

    /**
     * 用户订阅开启或关闭客服推送
     *
     */
    private function kfpush($data){
        // 初始化用户信息
        $this->regWechatUser($data['FromUserName']);
        $status    = '';
        $from_status_key = $data['Content'];//更改状态
        if ($from_status_key=='订阅') {
            $status = 1;
        } elseif($from_status_key=='取消订阅'){
            $status = 0;
        }
        $map           = [];
        $map['wxid']   = $this->wxid;
        $map['openid'] = $data ['FromUserName'];
        $result = Kfpush::where($map)->count();//查找是否存在
        if ($result>0) {
            $return = true;
            if($from_status_key=='订阅'||$from_status_key=='取消订阅'){
                $return = Kfpush::where($map)->update(['status'=>$status]);
            }
            Kfpush::where($map)->update(['timeout'=>0]);
        } else{
            $map['uid'] = WechatUser::where(['openid'.$this->wxid=>$data ['FromUserName']])->value('uid');
            if($from_status_key=='订阅' || $from_status_key=='取消订阅'){
                $map['status'] = $status;
            }
            $return = Kfpush::create($map);
        }
        return $return;
    }
    

    /**
     * 生成注册成用户
     *
     */
    private function regWechatUser($openid){
        //$map ['token'] = $data ['ToUserName'];
        $map ['openid'.$this->wxid] = $openid;
        $count = WechatUser::where($map)->count(); //获取当前用户的uid
        if (!$count) {
            $wechat_user = $this->wechatObj->getUserInfo($openid);
            //注册用户            
            //构造注册数据
            $reg_data = [];
            //$reg_data['user_type'] = 1;
            $reg_data['sex']         = $wechat_user['sex'];
            $reg_data['nickname']    = $wechat_user['nickname'];
            $reg_data['username']    = 'WX'.time();
            $reg_data['password']    = $wechat_user['openid'];
            //$reg_data['reg_type']  = strtolower($user_sns_info['type']);
            $reg_data['avatar']      = $wechat_user['headimgurl'];
            $reg_data['auth_groups'] = config('reg_default_roleid');

            $user = User::create($reg_data);
            $uid = $user->uid;
            if ($uid>0) {
                //model('admin/AuthGroup')->addToGroup($uid,config('reg_default_roleid'));//添加授权组
                $wechat_user['uid'] = $uid;
                WechatUser::add($this->wxid, $wechat_user);//公众号1
                
            }

        } else{
            WechatUser::where($map)->update(['subscribe'=>1]);
        }
    }
}