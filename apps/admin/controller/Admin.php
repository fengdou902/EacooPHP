<?php
// 后台公共控制器       
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use app\common\controller\Base;

use app\common\model\User as UserModel;
use app\admin\model\AuthRule;

use eacoo\EacooAccredit;

use think\Cache;
use think\Loader;
use think\Hook;
use think\Cookie;

class Admin extends Base
{ 
    public function _initialize() {
        parent::_initialize();
        //检测是否是最新版本
        $eacoo_version = EacooAccredit::getVersion();
        if ($eacoo_version['version']>EACOOPHP_V) {
            $this->assign('eacoo_version',$eacoo_version);
        }

        if (SERVER_SOFTWARE_TYPE=='nginx') {
            \think\Url::root('/admin.php?s=');
            $this->assign('url_model',2);
        } else{
            \think\Url::root('/admin.php');
            $this->assign('url_model',1);
        }
        
        if( !is_login()){
            // 还没登录 跳转到登录页面
            $this->redirect('admin/login/index');
            exit;
        } else {
            $this->currentUser = session('user_login_auth');
        }

        if (!in_array($this->urlRule,['admin/login/index', 'admin/index/logout'])) {
            // 检测系统权限
            if(!is_administrator()){
                if (config('admin_allow_ip')) {
                    // 检查IP地址访问
                    if (!in_array($this->ip, explode(',', config('admin_allow_ip')))) {
                        $this->error('403:禁止访问');
                    }
                }
                $this->checkAuth();
            }
            
        }

        if (session('activation_auth_sign') != UserModel::where('uid',$this->currentUser['uid'])->value('activation_auth_sign')) {
            $this->error('您的帐号正在别的地方登录!',url('admin/login/logout'));
        }

        $this->assign('current_user',$this->currentUser);

        if(!IS_AJAX){
            //是否菜单被收藏
            $collect_menus = config('admin_collect_menus');
            $this->assign('is_menu_collected',0);
            if (isset($collect_menus[$this->request->url()])) {
                $this->assign('is_menu_collected',1);
            }
            if (PUBLIC_RELATIVE_PATH=='') {
                $template_path_str = '../';
            } else{
                $template_path_str = './';
            }

            $_admin_public_base = '';
            if ($this->request->param('load_type')=='iframe') {
                $_admin_public_base = $template_path_str.'apps/admin/view/public/layerbase.html';
            } else{
                $_admin_public_base = $template_path_str.'apps/admin/view/public/base.html';
            }
            $_admin_public_base = APP_PATH.'admin/view/public/base.html';

            //顶部模版
            $this->assign('_admin_document_header_',$template_path_str.'apps/admin/view/public/document_header.html');
            $this->assign('_admin_public_left_',$template_path_str.'apps/admin/view/public/left.html');
            $this->assign('_admin_public_base_', $_admin_public_base);
            $this->assign('_admin_public_layerbase_', $template_path_str.'apps/admin/view/public/layerbase.html');
        } 
        
    }

    /**
     * 设置一条或者多条数据的状态
     * @param $script 严格模式要求处理的纪录的uid等于当前登陆用户UID
     */
    public function setStatus($model = CONTROLLER_NAME, $script = false) {
        $ids = $this->request->param('ids/a');
        $status = $this->request->param('status');
        if (empty($ids)) {
            $this->error('请选择要操作的数据');
        }
        $model_primary_key = model($model)->getPk();
        $map[$model_primary_key] = ['in',$ids];
        if ($script) {
            $map['uid'] = ['eq', is_login()];
        }
        switch ($status) {
            case 'forbid' :  // 禁用条目
                $data = ['status' => 0];
                $this->editRow(
                    $model,
                    $data,
                    $map,
                    ['success'=>'禁用成功','error'=>'禁用失败']
                );
                break;
            case 'resume' :  // 启用条目
                $data = ['status' => 1];
                $map  = array_merge(['status' => 0], $map);
                $this->editRow(
                    $model,
                    $data,
                    $map,
                    array('success'=>'启用成功','error'=>'启用失败')
                );
                break;
            case 'hide' :  // 隐藏条目
                $data = array('status' => 1);
                $map  = array_merge(array('status' => 2), $map);
                $this->editRow(
                    $model,
                    $data,
                    $map,
                    array('success'=>'隐藏成功','error'=>'隐藏失败')
                );
                break;
            case 'show' :  // 显示条目
                $data = array('status' => 2);
                $map  = array_merge(array('status' => 1), $map);
                $this->editRow(
                   $model,
                   $data,
                   $map,
                   array('success'=>'显示成功','error'=>'显示失败')
                );
                break;
            case 'recycle' :  // 移动至回收站
                $data['status'] = -1;
                $this->editRow(
                    $model,
                    $data,
                    $map,
                    array('success'=>'成功移至回收站','error'=>'删除失败')
                );
                break;
            case 'restore' :  // 从回收站还原
                $data = array('status' => 1);
                $map  = array_merge(array('status' => -1), $map);
                $this->editRow(
                    $model,
                    $data,
                    $map,
                    array('success'=>'恢复成功','error'=>'恢复失败')
                );
                break;
            case 'delete'  :  // 删除条目
                //action_log(0, is_login(), ['param'=>$this->request->param()],'删除操作');
                $result = model($model)->where($map)->delete();
                if ($result) {
                    $this->success('删除成功，不可恢复！');
                } else {
                    $this->error('删除失败');
                }
                break;
            default :
                $this->error('参数错误');
                break;
        }
    }

    /**
     * 对数据表中的单行或多行记录执行修改 GET参数id为数字或逗号分隔的数字
     * @param string $model 模型名称,供M函数使用的参数
     * @param array  $data  修改的数据
     * @param array  $map   查询时的where()方法的参数
     * @param array  $msg   执行正确和错误的消息
     *                       array(
     *                           'success' => '',
     *                           'error'   => '',
     *                           'url'     => '',   // url为跳转页面
     *                           'ajax'    => false //是否ajax(数字则为倒数计时)
     *                       )
     */
    final protected function editRow($model, $data, $map, $msg) {
        $id = array_unique((array)input('id',0));
        $id = is_array($id) ? implode(',',$id) : $id;
        //如存在id字段，则加入该条件
        // $fields = model($model)->getDbFields();
        // if (in_array('id', $fields) && !empty($id)) {
        //     $where = array_merge(
        //         array('id' => array('in', $id )),
        //         (array)$where
        //     );
        // }
        $msg = array_merge(
            array(
                'success' => '操作成功！',
                'error'   => '操作失败！',
                'url'     => ' ',
                'ajax'    => IS_AJAX
            ),
            (array)$msg
        );
        $result = model($model)->where($map)->update($data);
        if ($result != false) {
            $this->success($msg['success']);
        } else {
            $this->error($msg['error']);
        }
    }

    /**
     * 验证数据
     * @param  string $validate 验证器名或者验证规则数组
     * @param  array  $data          [description]
     * @return [type]                [description]
     */
    protected function validateData($data,$validate)
    {
        if (!$validate || empty($data)) return false;
        $result = $this->validate($data,$validate);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);exit;
        } 
        return true;
        
    }

    /**
     * 检测授权
     * @return [type] [description]
     * @date   2017-10-17
     * @author 心云间、凝听 <981248356@qq.com>
     */
    protected function checkAuth()
     {
        $auth = new \org\util\Auth();
        $name = $this->urlRule;
        //当前用户id
        $uid = is_login();
        //执行check的模式
        $mode = 'url';
        //'or' 表示满足任一条规则即通过验证;
        //'and'则表示需满足所有规则才能通过验证
        $relation = 'and';

        if(!$auth->check($name, $uid, 1, $mode, $relation) && $name!='admin/dashboard/index'){//允许进入仪表盘
            $this->error('无权限访问',Cookie::get('__prevUrl__'));
            return false;
        }
        Cookie::set('__prevUrl__',$this->url,3600);
        return true;
     } 

}