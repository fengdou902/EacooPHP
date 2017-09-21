<?php

namespace addons\SyncLogin\Controller;
use app\admin\builder\Builder;

use app\admin\controller\Plugins; 

class AdminSyncLogin extends Plugins{
    protected $tab_list;
    protected $SyncLoginModel;
    protected $addon_name='SyncLogin';
    function _initialize()
    {
        parent::_initialize();
        $this->SyncLoginModel=model('Addons://SyncLogin/SyncLogin');
    }
    /*列表*/
    public function index(){
        // 获取所有链接
        $paged=I('get.p/d',1);
        $map['status'] = array('egt', '0');  // 禁用和正常状态
        list($data_list,$totalCount) =$this->SyncLoginModel->getListByPage($map,$paged,'level asc,id asc','*',20);

        $builder = new AdminListBuilder();
        $builder->setMetaTitle('第三方登录列表')  // 设置页面标题
                ->addTopButton('addnew',array('href'=>U('adminManage',array('name'=>$this->addon_name,'action'=>'edit'))))    // 添加新增按钮
                ->addTopButton('resume',array('model'=>'addon_sync_login'))  // 添加启用按钮
                ->addTopButton('forbid',array('model'=>'addon_sync_login'))  // 添加禁用按钮
                ->setSearch('请输入uid/openid',array('href'=>U('adminManage',array('name'=>$this->addon_name,'action'=>'edit'))))
                ->keyListItem('uid', 'UID')
                ->keyListItem('type', '类别')
                ->keyListItem('openid', 'openid')
                ->keyListItem('status', '状态', 'status')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListData($data_list)     // 数据列表
                ->setListPage($totalCount,20)  // 数据列表分页
                ->addRightButton('edit')           // 添加编辑按钮
                ->addRightButton('forbid')  // 添加禁用/启用按钮
                ->addRightButton('delete')  // 添加删除按钮
                ->display();
    }
        /**
     * 编辑链接
     */
    public function edit() {
        $id=I('get.id/d',0);
        $title=$id?"编辑":"新增";
        if (IS_POST) {
            $data = $this->SyncLoginModel->create();
            if ($data) {
                $id = $this->SyncLoginModel->editData($data);
                if ($id !== false) {
                    $this->success($title.'成功', U('adminManage',array('name'=>$this->addon_name)));
                } else {
                    $this->error($title.'失败');
                }
            } else {
                $this->error($this->SyncLoginModel->getError());
            }
        } else {
            if ($id!=0) {
                $data_list=$this->SyncLoginModel->find($id);
            }
            $builder = new AdminFormBuilder();
            $builder->setMetaTitle($title.'SNS登录账号')  // 设置页面标题
                    ->setPostUrl(U('adminManage',array('name'=>$this->addon_name,'action'=>'edit')))
                    ->addFormItem('uid', 'number', '用户', '绑定的系统用户ID')
                    ->addFormItem('type', 'radio', '类别', '第三方账号类型',array('Weixin'=>'Weixin','Qq'=>'Qq','Sina'=>'Sina','Renren'=>'Renren'))
                    ->addFormItem('openid', 'text', 'openid', '')
                    ->addFormItem('access_token', 'text', 'access_token', '')
                    ->addFormItem('refresh_token', 'text', 'refresh_token', '')
                    ->setFormData($data_list)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->display();
        }
    }

}
