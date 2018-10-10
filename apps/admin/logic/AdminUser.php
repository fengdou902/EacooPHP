<?php
// 后台用户逻辑层
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\logic;
use app\admin\model\AuthGroupAccess;
use app\common\logic\Base as BaseLogic;

class AdminUser extends BaseLogic
{
    // 设置数据表（不含前缀）
    protected $name = 'admin';
    
    /**
     * 用户信息
     * @param  integer $uid [description]
     * @return [type]       [description]
     */
    public static function info($uid = 0)
    {
       if (!$uid) return false;
       $result = cache('AdminUserInfo_'.$uid);
       if (!$result || empty($result)) {
            $info = self::get($uid);
            if ($info) {
                $info           = $info->toArray();
                $info['avatar'] = path_to_url($info['avatar']);
                $result         = self::extendInfo($info);
                $result         = array_merge($info,$result);
                cache('AdminUserInfo_'.$uid, $result,3600);
            }
       }

       return $result;
    }

    /**
     * 置扩展信息
     * @param  array  $result [description]
     * @return [type]         [description]
     */
    protected static function extendInfo($result = [])
    {
        $result['auth_group'] = model('admin/auth_group_access')->userGroupInfo($result['uid']);
        return $result;
    }

    /**
     * 注册用户
     * @param  array   $data   注册信息
     * @param  boolean $strict 是否严格
     * @return [type]          [description]
     */
    public static function register($data =[],$strict=true)
    {
        if (!empty($data)) {
            $check = true;
            if ($strict==true) {
                $check = self::checkDenyUser($data['username']);
            }
            //通过检测
            if ($check) {
               return self::create($data);
            }
            
        }
        return false;
    }

    /**
     * 检测用户名是不是被禁止注册
     * @param  string $username 用户名
     * @return boolean ture 未禁用，false 禁止注册
     */
    public static function checkDenyUser($username){
        if ($username) {
            $deny = config('user_deny_username');
            $deny = explode ( ',', $deny);
            foreach ($deny as $k=>$v) {
                if(stristr($username, $v)){
                    return true;
                }
            }
            return false;
        } 
        return false;
    }

     /**
     * 用户登录
     * @param  string  $login  登录
     * @param  string  $password 用户密码
     * @param  int     $type     登录类型 （1-用户编号, 2-用户账户, 3-手机, 4-用户昵称, 5-用户邮件, 6-全部）
     * @return int               [登录成功-用户ID，登录失败-错误编号]
     * @param bool $rememberme 记住登录
     */
    public static function login($login, $password, $rememberme = false, $type = 6){

        try {
            $map = '';
            switch ($type) {
                case 1:
                    $map = 'number';
                    break;
                case 2:
                    $map = 'username';
                    break;
                case 3:
                    $map = 'mobile';
                    break;
                case 4:
                    $map = 'nickname';
                    break;
                case 5:
                    $map = 'email';
                    break;
                case 6:
                    $map = 'username|email|mobile|nickname';
                    break;
                default:
                    throw new Exception("参数错误", 0);
                    
            }

            /* 获取用户数据 */
            $user = self::get([$map => $login,'status'=>1]);

            if(!empty($user)){   
                $return = ['code'=>0,'msg'=>'用户不存在或被禁用！'];
            }
            /* 验证用户密码 */
            if(encrypt($password) != $user['password']){
                throw new \Exception("密码错误", 0);
            } 

            self::autoLogin($user,$rememberme); //更新用户登录信息
            $return = ['code'=>1,'msg'=>'登录成功','data'=>['uid'=>$user['uid']]];
        } catch (\Exception $e) {
            $return = [
                'code'=>$e->getCode(),
                'msg'=>$e->getMessage(),
                'data'=>[]
            ];
        }
        
        return $return;
    }

    /**
     * 自动登录
     * @param  [type]  $user       用户对象
     * @param  boolean $rememberme 是否记住登录，默认7天
     * @return [type]              [description]
     */
    public static function autoLogin($user, $rememberme = false){
        try {
            if (empty($user)) return false;

            // 记录登录SESSION和COOKIES
            $result          = self::setUserAuthSession($user);
            $auth_login      = $result['auth_login'];
            $auth_login_sign = $result['auth_login_sign'];
            // 更新登录信息
            $data = [
                'last_login_ip'        => request()->ip(),
                'last_login_time'      => $auth_login['last_login_time'],
                'activation_auth_sign' => $auth_login_sign,
            ];

            $res = self::where('uid', $auth_login['uid'])->update($data);
            if (!$res) {
                throw new \Exception("更新失败", 0);
                
            }
            // 记住登录
            if ($rememberme) {
                $signin_token = $user['username'].$user['uid'].$auth_login['last_login_time'];
                cookie('admin_uid', $user['uid'], 24 * 3600 * 7);
                cookie('admin_signin_token', data_auth_sign($signin_token), 24 * 3600 * 7);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
        
    }

     /**
     * 检测用户信息
     * @param  string  $field  用户名
     * @param  integer $type   用户名类型 1-用户名，2-用户邮箱，3-用户电话
     * @return integer         错误编号
     */
    public function checkField($findField, $where, $returnField, $check = false){
        if ($check) {
            // 根据字段得到用户相关索引ID
            return $this->where($findField, $where)->value($returnField);
        } else {
            // 根据字段检测是否存在此用户
            return $this->where($findField, $where)->count();
        }
    }

    /**
     * 更新登录用户的session
     * @return void
     */
    public function updateLoginSession($uid){

        if ($uid == is_admin_login()) {            
            $user    = self::get($uid);
            $result = $this->setUserAuthSession($user);

            return $this->where('uid',$uid)->update(['activation_auth_sign' => $result['auth_login_sign']]);
        }
        return false;
    }

    /**
     * 设置用户授权session
     * @param  [type] $user [description]
     * @date   2017-10-06
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function setUserAuthSession($user)
    {
         // 记录登录SESSION和COOKIES
        $auth_login = [
            'uid'             => $user['uid'],
            'username'        => $user['username'],
            'nickname'        => $user['nickname'],
            'email'           => $user['email'],
            'mobile'          => $user['mobile'],
            'avatar'          => $user['avatar'],
            'auth_group'      => model('admin/auth_group_access')->userGroupInfo($user['uid']),
            'create_time'     => $user['create_time'],
            'last_login_time' => date('Y-m-d H:i:s',time())
        ];

        $auth_login_sign = data_auth_sign($auth_login);
        session('admin_login_auth', $auth_login);
        session('admin_activation_auth_sign', $auth_login_sign);
        cache('AdminUserInfo_'.$user['uid'],null);
        return [
            'auth_login'      =>$auth_login,
            'auth_login_sign' =>$auth_login_sign
        ];
    }

    /**
     * 判断是否登录
     * @return int 0或用户id
     */
    public static function isLogin()
    {
        $user = session('admin_login_auth');
        if (empty($user)) {
            // 判断是否记住登录
            if (cookie('?admin_uid') && cookie('?admin_signin_token')) {
                $user = self::get(cookie('admin_uid'));
                if ($user) {
                    $signin_token = data_auth_sign($user->username.$user->uid.$user->last_login_time);
                    if (cookie('admin_signin_token') == $signin_token) {
                        // 自动登录
                        self::autoLogin($user, true);
                        return $user->uid;
                    }
                }
            };
            return 0;
        } else{
            return session('admin_activation_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
        }
    }

}