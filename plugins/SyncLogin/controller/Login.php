<?php
namespace Addons\SyncLogin\Controller;
use Think\Hook;
use Sns\Controller\AddonController;
require_once(dirname(dirname(__FILE__))."/ThinkSDK/ThinkOauth.class.php");
require_once(dirname(dirname(__FILE__))."/ThinkSDK/ThinkOauthInfo.class.php");
/**
 * 第三方登录控制器
 */
class LoginController extends AddonController{
    /**
     * 登录地址
     */
    public function login(){
        $type= I('get.type');
        empty($type) && $this->error('参数错误');
        $sns  = \ThinkOauth::getInstance($type); //加载ThinkOauth类并实例化一个对象
        redirect($sns->getRequestCodeURL()); //跳转到授权页面
    }

    /**
     * 登陆后回调地址
     */
    public function callback(){
        $code =  I('get.code');
        $type= I('get.type');
        $sns  = \ThinkOauth::getInstance($type);

        //腾讯微博需传递的额外参数
        $extend = null;
        if($type == 'tencent'){
            $extend = array('openid' => I('get.openid'), 'openkey' =>  I('get.openkey'));
        }

        $token = $sns->getAccessToken($code , $extend); //获取第三方Token
        $user_sns_info = \ThinkOauthInfo::$type($token); //获取第三方传递回来的用户信息
        $user_sync_info = D('Addons://SyncLogin/SyncLogin')->getUserByOpenidAndType($token['openid'], $type); //根据openid等参数查找同步登录表中的用户信息
        
        $weixin_db_user =D('Weixin/WeixinUser')->where(array('unionid'=>$user_sns_info['unionid']))->find();
        if($weixin_db_user&&$weixin_db_user['uid']) { //曾经绑定过
            D('Addons://SyncLogin/SyncLogin')->updateTokenByTokenAndType($token, $type);
            if (!$weixin_db_user ['openid0']) {
                D('Weixin/WeixinUser')->where(array('unionid'=>$user_sns_info['unionid']))->setField('openid0',$user_sns_info['openid']);
            }
            $user_sys_info = D('User/User')->find($weixin_db_user ['uid']); //根据UID查找系统用户中是否有此用户
            D('User/User')->auto_login($user_sys_info);
            redirect(Cookie('__forward__') ? : C('INDEX_URL'));
        }else{ //没绑定过，去注册页面
            session('token', $token);
            session('user_sns_info', $user_sns_info);
            $this->register();
            /*$this->assign('user_sns_info', $user_sns_info);
            $this->assign('meta_title', "登陆" );
            $this->display(T('Addons://SyncLogin@./default/reg'));*/
        }
    }

    /**
     * 创建新用户
     */
    public function register() {

        //注册用户
        $user_sns_info = session('user_sns_info');
        $username = 'U'.NOW_TIME;
        $password = $user_sns_info['openid'];

        // 构造注册数据
        $reg_data = array();
        //$reg_data['user_type'] = 1;
        $reg_data['sex'] = $user_sns_info['sex'];
        $reg_data['nickname']  = $user_sns_info['name'];
        $reg_data['username']  = $username;
        //$reg_data['password']  = $_POST['password'];
        $reg_data['password']  = $password;
        //$reg_data['reg_type']  = strtolower($user_sns_info['type']);
        //$reg_data['avatar']    = $_POST['avatar'];
        $reg_data['avatar']    = $user_sns_info['head'];
        $reg_data['role_id']=C('REG_DEFAULT_ROLEID');
        $user_object = D('User/User');
        $user_data   = $user_object->create($reg_data);
        if($user_data){
            $uid = $user_object->add($user_data);
            if ($uid) {
                D('Addons://SyncLogin/SyncLogin')->update($uid);
                D('Admin/AuthRole')->addToGroup($uid,C('REG_DEFAULT_ROLEID'));//添加授权组
                if ($user_sns_info['type']=='WEIXIN') {
                    D('Weixin/WeixinUser')->update($uid,'public0');
                }
                //登录用户
                $user_info = get_user_info($uid);
                if ($user_info) {
                    $uid = $user_object->auto_login($user_info);
                    session('user_sns_info', null);
                    $this->success('注册成功', C('INDEX_URL'));
                } else {
                    $this->error('错误');
                }
            } else {
                $this->error('注册失败');
            }
        } else {
            $this->error($user_object->getError());
        }
    }

    /**
     * 绑定本地帐号
     */
    public function bind(){
        $username = $_POST['username'];
        $password = $_POST['password'];
        $user_object = D('User/User');
        $uid = $user_object->login($username, $password);
        if($uid > 0){
            //新增SNS登录账号
            if(D('Addons://SyncLogin/SyncLogin')->update($uid)){
                session('user_sns_info', null);
                $this->success('微信账号绑定成功', Cookie('__forward__') ? : C('INDEX_URL'));
            }else{
                $this->error('新增SNS登录账号失败');
            }
        }else{
            $this->error('绑定失败'.$user_object->getError()); // 绑定失败
        }
    }

    /**
     * 取消绑定本地帐号
     */
    public function cancelbind($uid){
        $condition['uid'] = $uid;
        $condition['type'] = $_GET['type'];
        $ret = D('Addons://SyncLogin/SyncLogin')->where($condition)->delete();
        if($ret){
            $this->success('取消绑定成功');
        }else{
            $this->error('取消绑定失败');
        }
    }
}
