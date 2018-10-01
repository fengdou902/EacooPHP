<?php
// 授权管理控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\AuthRule as AuthRuleModel;
use app\admin\model\AuthGroup as AuthGroupModel;
use app\admin\model\AuthGroupAccess as AuthGroupAccessModel;
use app\admin\model\AdminUser as AdminUserModel;

class Auth extends Admin {

    protected $authRuleModel;
    protected $authGroupModel;
    protected $userModel;

    function _initialize()
    {
        parent::_initialize();

        $this->authRuleModel  = new AuthRuleModel();
        $this->authGroupModel = new AuthGroupModel();
        $this->userModel     = new AdminUserModel;

    }
    
    /**
     * 规则管理
     * @return [type] [description]
     */
    public function index(){

        $depend_flag = input('param.depend_flag','all');//管理类型
        if ($depend_flag!='all' && $depend_flag) {
            $this->authRuleModel->where('depend_flag',$depend_flag);
        }

        list($data_list,$total)
            = $this->authRuleModel
                ->search() //添加搜索查询
                ->getListByPage([],true,'depend_flag,pid asc,sort asc',20);
        
        $pid = input('param.pid',0);

        $return = builder('list')
                ->setPageTips('用于管理后台的规则项')
                ->addTopBtn('addnew',array('href'=>url('edit',['pid'=>$pid])))  // 添加新增按钮
                ->addTopBtn('resume',array('model'=>'auth_rule'))  // 添加启用按钮
                ->addTopBtn('forbid',array('model'=>'auth_rule'))  // 添加禁用按钮
                ->addTopBtn('delete',array('model'=>'auth_rule'))  // 添加删除按钮
                ->setTabNav(logic('Auth')->getTabList(), $depend_flag)  // 设置页面Tab导航
                ->addTopBtn('sort',['model'=>'auth_rule','href'=>url('Sort',['pid'=>$pid])])  // 添加排序按钮
                //->setSearch('', url('rule'))
                ->keyListItem('id','ID')
                ->keyListItem('title','名称')
                ->keyListItem('parent_menu','上级菜单')
                ->keyListItem('name', 'URL')
                ->keyListItem('depend_flag', '来源标识')
                ->keyListItem('sort', '排序')
                ->keyListItem('is_menu','菜单','array',[0=>'否',1=>'是'])
                ->keyListItem('status','状态','status')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListPrimaryKey('id')
                ->setListData($data_list)    // 数据列表
                ->setListPage($total,20) // 数据列表分页
                ->setExtraHtml(logic('Auth')->moveMenuHtml())//添加移动按钮html
                ->addRightButton('edit')      // 添加编辑按钮
                ->addRightButton('forbid',['model'=>'auth_rule'])// 添加启用禁用按钮
                ->alterListData(
                    array('key' => 'pid', 'value' =>'0'),
                    array('parent_menu' => '无'))
                ->fetch();

        return Iframe()
                ->setMetaTitle('规则管理')  // 设置页面标题
                ->search([
                    ['name'=>'is_menu','type'=>'select','title'=>'是否菜单','options'=>[0=>'否',1=>'是']],
                    ['name'=>'status','type'=>'select','title'=>'状态','options'=>[1=>'正常',2=>'待审核']],
                    ['name'=>'depend_flag','type'=>'text','extra_attr'=>'placeholder="请输入来源标识"'],
                    ['name'=>'keyword','type'=>'text','extra_attr'=>'placeholder="请输入查询关键字"'],
                ])
                ->content($return);
    }

    /**
     * 规则编辑
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function edit($id=0){
        $title=$id ? "编辑":"新增";
        
        if(IS_POST){
            // 提交数据
            $data = $this->request->param();
            //验证数据
            $this->validateData($data,'AuthRule.edit');

            //$data里包含主键，则editData就会更新数据，否则是新增数据
            if ($this->authRuleModel->editData($data)) {
                cache('admin_sidebar_menus_'.$this->currentUser['uid'],null);//清空后台菜单缓存
                $this->success($title.'菜单成功', url('index',['pid'=>input('param.pid')]));
            } else {
                $this->error($this->authRuleModel->getError());
            }   

        } else{
            // 获取菜单数据
            if ($id>0) {
                $info = $this->authRuleModel->get($id);
            } else{
                $pid       = (int)input('param.pid');
                if ($pid>0) {
                    $pid_data  = $this->authRuleModel->where('pid',$pid)->field('depend_type,depend_flag')->find();
                    $info = ['depend_type'=>$pid_data['depend_type'],'depend_flag'=>$pid_data['depend_flag'],'pid'=>$pid,'is_menu'=>1,'sort'=>99,'status'=>1];
                } else{
                    
                    $info = ['depend_type'=>1,'is_menu'=>1,'sort'=>99,'status'=>1];
                }
            }
            $depend_flag = logic('Auth')->getDependFlags($info['depend_type']);
            //获取所有菜单
            $menus = logic('Auth')->getAdminMenu();
            $menus = array_merge([0=>['id'=>0,'title_show'=>'顶级菜单']], $menus);

            $extra_html = logic('Auth')->getFormMenuHtml();//获取表单菜单html
            $content = builder('Form')
                    ->addFormItem('id', 'hidden', 'ID', 'ID')
                    ->addFormItem('title', 'text', '标题', '用于后台显示的配置标题')
                    ->addFormItem('pid', 'multilayer_select', '上级菜单', '上级菜单',$menus)
                    ->addFormItem('depend_type', 'select', '来源类型', '来源类型。分别是模块，插件，主题',[1=>'模块',2=>'插件',3=>'主题'])
                    ->addFormItem('depend_flag', 'select', '来源标识', '请选择标识名，模块、插件、主题的标识名',$depend_flag)
                    ->addFormItem('icon', 'icon', '字体图标', '字体图标')
                    ->addFormItem('name', 'text', '链接', '链接')
                    ->addFormItem('is_menu', 'radio', '后台菜单', '是否标记为后台菜单',[1=>'是',0=>'否'])
                    ->addFormItem('sort', 'number', '排序', '按照数值大小的倒叙进行排序，数值越小越靠前')
                    ->addFormItem('status', 'select', '状态', '',[0=>'禁用',1=>'启用'])
                    ->setFormData($info)
                    ->setExtraHtml($extra_html)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();

            return Iframe()
                ->setMetaTitle($title.'规则')  // 设置页面标题
                ->content($content);
        }   
        
    }

    /**
     * 对菜单进行排序
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function Sort($ids = null)
    {
        $builder  = builder('Sort');
        $pid = input('param.pid',false);//是否存在父ID
        $map = [];
        if ($pid>0 || $pid===0) {
            $map['pid'] = $pid;
        } 
        
        if (IS_POST) {
            cache('admin_sidebar_menus_'.$this->currentUser['uid'],null);//清空后台菜单缓存
            $builder->doSort('auth_rule', $ids);
        } else {
            //$map['status'] = array('egt', 0);
            $list = $this->authRuleModel->getList($map,'id,title,sort','sort asc');
            foreach ($list as $key => $val) {
                $list[$key]['title'] = $val['title'];
            }
            $content = $builder->setMetaTitle('配置排序')
                    ->setListData($list)
                    ->addButton('submit')->addButton('back')
                    ->fetch();
            return Iframe()
                ->setMetaTitle('规则排序')  // 设置页面标题
                ->content($content);
        }
    }

    /**
     * 标记菜单
     * @return [type] [description]
     * @date   2017-08-27
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function markerMenu(){
        //是否标记菜单：0否，1是
        $model='AuthRule';
        if (IS_POST) {

            cache('admin_sidebar_menus_'.$this->currentUser['uid'],null);//清空后台菜单缓存

            $ids    = input('post.ids/a');
            $status = input('param.status');
            if (empty($ids)) {
                $this->error('请选择要操作的数据');
            }

            $map['id'] = ['in',$ids];
            switch ($status) {
                case 0 :  
                    $data = ['is_menu' => 0];
                    $this->editRow(
                        $model,
                        $data,
                        $map,
                        array('success'=>'标记成功','error'=>'标记失败')
                    );
                    break;
                case 1 :  
                    $data = ['is_menu' => 1];
                    $this->editRow(
                        $model,
                        $data,
                        $map,
                        ['success'=>'标记成功','error'=>'标记失败']
                    );
                    break;
                default :
                    $this->error('参数错误');
                    break;
            }
        }
    }

    /**
     * 移动菜单所属模块
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function moveModule() {
        if (IS_POST) {
            $ids       = input('param.ids');
            $to_module = input('param.to_module');
            if ($to_module) {
                $map['id'] = ['in',$ids];
                $data      = ['depend_flag' => $to_module];
                $this->editRow('auth_rule', $data, $map, array('success'=>'移动成功','error'=>'移动失败',U('index')));

            } else {
                $this->error('请选择目标模块');
            }
        }
    }

    /**
     * 移动菜单位置
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function moveMenuParent() {
        if (IS_POST) {
            $ids    = input('param.ids');
            $to_pid = input('param.to_pid');
            if ($to_pid || $to_pid==0) {
                cache('admin_sidebar_menus_'.$this->currentUser['uid'],null);
                $map['id'] = ['in',$ids];
                $data = array('pid' => $to_pid);
                $this->editRow('auth_rule', $data, $map, ['success'=>'移动成功','error'=>'移动失败',url('index')]);

            } else {
                $this->error('请选择目标菜单'.$to_pid);
            }
        }
    }

    /**
     * 设置角色的状态
     */
    public function setStatus($model ='auth_rule',$script = false){
        $ids = input('request.ids/a');
        if ($model =='AuthGroup') {
            if (is_array($ids)) {
                if(in_array(1, $ids)) {
                    $this->error('超级管理员不允许操作');
                }
            } else{
                if($ids === 1) {
                    $this->error('超级管理员不允许操作');
                }
            }
        } else{
            cache('admin_sidebar_menus_'.$this->currentUser['uid'],null);//清空后台菜单缓存
        }
        
        parent::setStatus($model);
    }
}