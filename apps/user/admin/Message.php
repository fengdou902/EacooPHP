<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.eacoo123.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\user\admin;
use app\admin\controller\Admin;

use app\user\model\Message as MessageModel;
use app\common\model\User;

use app\admin\builder\Builder;

class Message extends Admin {
    protected $message_model;
    protected $tab_list;

    function _initialize()
    {
        parent::_initialize();
        $this->message_model = new MessageModel();
    }

    //消息列表
    public function messages($box_type='inbox'){
        $meta_title = $box_type=='inbox' ? '收件箱':'发件箱';

        $this->assign('meta_title',$meta_title);
        $this->assign('box_type',$box_type);

        // 获取所有该用户站内信
        if ($box_type=='inbox') {//发件箱
            $msg_map['to_uid']=is_login();
        }elseif ($box_type=='outbox') {//
           $msg_map['from_uid']=is_login();
        }
        $msg_map['pid']=0;
        $msg_map['is_read']=0;
        $msg_map['status']=1;
        //dump($msg_map);
        list($data_list,$totalCount) =$this->message_model->getListByPage($msg_map); 
        foreach($data_list as $k=>$data){
            $data_list[$k]['fromuid_avatar']   = get_user_info($data['from_uid'])['avatar'];
            $data_list[$k]['touid_avatar']     = get_user_info($data['to_uid'])['avatar'];
            
            $data_list[$k]['fromuid_nickname'] = get_user_info($data['from_uid'])['nickname'];
            $data_list[$k]['touid_nickname']   = get_user_info($data['to_uid'])['nickname'];

            if ($box_type=='inbox') {
                $data_list[$k]['avatar']   =$data_list[$k]['fromuid_avatar'];
                $data_list[$k]['nickname'] =$data_list[$k]['fromuid_nickname'];
            }elseif ($box_type=='outbox') {
                $data_list[$k]['avatar']   =$data_list[$k]['touid_avatar'];
                $data_list[$k]['nickname'] =$data_list[$k]['touid_nickname'];
            }
            
        }
        $this->assign('message_list',$data_list);

        $inboxMessageCount  =$this->message_model->newMessageCount(null,'inbox');
        $outboxMessageCount =$this->message_model->newMessageCount(null,'outbox');
        
        $this->assign('inboxMessageCount',$inboxMessageCount);
        $this->assign('outboxMessageCount',$outboxMessageCount);
        return $this->fetch();
    }

    //发送消息
    public function send_message($from_uid=0,$type=1){
        $this->assign('meta_title','发送消息');
        $dataMessage=[];

        if (!$from_uid) {
           $from_uid = is_login();
        }
        if(IS_POST && $from_uid){
            $to_uids = input('post.to_uids');
            $to_uids = explode(',',$to_uids);
            foreach ($to_uids as $key => $to_uid) {
                $data['from_uid'] = $from_uid;
                $data['to_uid']   = $to_uid;
                $data['title']    = input('post.title');
                $data['content']  = input('post.content','','htmlspecialchars_decode');
                $data['type']     = $type;
                $data['pid']      = 0;

                if ($from_uid===$to_uid) {
                    $this ->error('对不起，不能给自己发私信');
                }else{
                    if (!$data['content']) {
                        $this ->error('消息内容不能为空');
                    }
                    $result =$this->message_model->sendMessage($data);
                    if (!$result) {
                        $this ->error($this->message_model->getError());
                    }
                }
            }
            if($result){
                $this ->success('发送成功');
            }
            return;
        } else{
            $inboxMessageCount  =$this->message_model->newMessageCount(null,'inbox');
            $outboxMessageCount =$this->message_model->newMessageCount(null,'outbox');
            
            $this->assign('inboxMessageCount',$inboxMessageCount);
            $this->assign('outboxMessageCount',$outboxMessageCount);
            return $this->fetch();
        }
        
    }
    
    //消息详情
    function detail($to_uid){
        
        $this->assign('meta_title','消息详情');
        // 获取所有该用户站内信
                
        $data_list = $this->message_model->where(function ($query) use($to_uid) {
                $query->where(['to_uid'=>$to_uid,'from_uid'=>is_login()]);
            })->whereOr(function ($query)use($to_uid) {
                $query->where(['to_uid'=>is_login(),'from_uid'=>$to_uid]);
            })->order('create_time asc')->limit(60)->select();
        foreach($data_list as $k=>$data){
            $data_list[$k]['fromuid_avatar']   = get_user_info($data['from_uid'])['avatar'];
            $data_list[$k]['touid_avatar']     = get_user_info($data['to_uid'])['avatar'];
            
            $data_list[$k]['fromuid_nickname'] = get_user_info($data['from_uid'])['nickname'];
            $data_list[$k]['touid_nickname']   = get_user_info($data['to_uid'])['nickname'];
        }
        $this->assign('message_list',$data_list);
        $this->assign('to_user_info',get_user_info($to_uid));

        $inboxMessageCount  = $this->message_model->newMessageCount(null,'inbox');
        $outboxMessageCount = $this->message_model->newMessageCount(null,'outbox');
        
        $this->assign('inboxMessageCount',$inboxMessageCount);
        $this->assign('outboxMessageCount',$outboxMessageCount);
        return $this->fetch();
        
    }

}