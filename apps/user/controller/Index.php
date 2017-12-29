<?php
namespace app\user\controller;
use app\home\controller\Home;

use app\common\model\User as UserModel;
class Index extends Home
{
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

        $map['status'] = ['egt', '0']; // 禁用和正常状态
        list($user_list) = $this->userModel->getListByPage($map,'reg_time desc','*',20);
        $this->assign('user_list',$user_list);
        return $this->fetch();

    }

    /*
     *  Description: 会员主页
     *  By: yyyvy  <QQ:76836785>
     *  Time: 2017-12-28 06:55:11
     * */
    public function home($uid){
        $info = userModel::info($uid);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /*
     *  Description: 会员登录
     *  By: yyyvy  <QQ:76836785>
     *  Time: 2017-12-28 10:19:55
     * */
    public function login(){
        if (IS_POST) {
            $data = input('post.');
            $result = $this->validate($data, [
              ['username', 'require|min:1', '登录名不能为空|登录名格式不正确'],
              ['password', 'require|length:6,32', '请填写密码|密码格式不正确']
            ]);
            if (true !== $result) {
                // 验证失败 输出错误信息

                $this->error($result);

                exit;
            }
            if(isset($data['rememberme'])){
                $rememberme = $data['rememberme']==1 ? true : false;
            }else{
                $rememberme = false;
            }

            $result = UserModel::login($data['username'],$data['password'], $rememberme);
            //print_r($result);die;

            if ($result['code']==1) {

                $uid = !empty($result['data']['uid']) ? $result['data']['uid']:0;
                $this->success('登录成功！',url('/'));
            } elseif ($result['code']==0) {
                $this->error($result['msg']);
            } else {
                $this->logout();
            }
        }else{
            return $this->fetch();
        }
    }

    /*
     *  Description: 退出登录
     *  By: yyyvy  <QQ:76836785>
     *  Time: 2017-12-28 10:19:55
     * */
    public function logout(){
        session(null);
        cookie(null,config('cookie.prefix'));
        $this->redirect('/');
    }

    /*
     *  Description: 退出登录
     *  By: yyyvy  <QQ:76836785>
     *  Time: 2017-12-28 10:19:55
     * */
    public function personal(){
        $uid  = is_login();
        //print_r($uid);
        return $this->fetch();
    }

    /*
     *  Description: 修改个人信息
     *  By: yyyvy  <QQ:76836785>
     *  Time: 2017-12-28 14:28:21
     * */
    public function profile($uid = 0) {
        if (IS_POST) {
            $data = input('post.');
            // 提交数据

            $result = $this->userModel->editData($data,$uid,'uid');

            if ($result) {
                if ($uid) {//如果是编辑状态下
                    $this->userModel->updateLoginSession($uid);
                }
                $this->success('提交成功', url('profile',['uid'=>$uid]));
            } else {
                $this->error($this->userModel->getError());
            }
        } else {
            // 获取账号信息

            if ($uid>0) {
                $user_info = get_user_info($uid);
                unset($user_info['password']);
                unset($user_info['auth_group']['max']);
            }
            $this->assign('user_info',$user_info);
            return $this->fetch();

        }
    }
}
