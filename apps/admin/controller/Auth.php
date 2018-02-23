<?php
// 授权管理控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
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
use app\common\model\User as UserModel;

class Auth extends Admin {

    protected $authRuleModel;
    protected $authGroupModel;
    protected $userModel;

    function _initialize()
    {
        parent::_initialize();

        $this->authRuleModel  = new AuthRuleModel();
        $this->authGroupModel = new AuthGroupModel();
        $this->userModel     = new UserModel;

    }
    
    /**
     * 规则管理
     * @return [type] [description]
     */
    public function index(){

        $depend_flag = input('param.depend_flag','all');//管理类型
        if ($depend_flag!='all') {
            $this->authRuleModel->where('depend_flag',$depend_flag);
        }

        list($data_list,$total)
            = $this->authRuleModel
                ->search() //添加搜索查询
                ->getListByPage([],true,'depend_flag,pid asc,sort asc',20);

        foreach ($data_list as &$row) {
            $row['p_menu']=$row->parent_menu ;
        }
        
        $pid = input('param.pid',0);

        return builder('list')
            ->setMetaTitle('规则管理')
            ->addTopBtn('addnew',array('href'=>url('edit',['pid'=>$pid])))  // 添加新增按钮
            ->addTopBtn('resume',array('model'=>'auth_rule'))  // 添加启用按钮
            ->addTopBtn('forbid',array('model'=>'auth_rule'))  // 添加禁用按钮
            ->addTopBtn('delete',array('model'=>'auth_rule'))  // 添加删除按钮
            ->setTabNav(logic('Auth')->getTabList(), $depend_flag)  // 设置页面Tab导航
            ->addTopBtn('sort',['model'=>'auth_rule','href'=>url('Sort',['pid'=>$pid])])  // 添加排序按钮
            //->setSearch('', url('rule'))
            ->keyListItem('id','ID')
            ->keyListItem('title','名称')
            ->keyListItem('p_menu','上级菜单')
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
            ->addRightButton('forbid',array('model'=>'auth_rule'))// 添加启用禁用按钮
            ->alterListData(
                array('key' => 'pid', 'value' =>'0'),
                array('p_menu' => '无'))
            ->fetch();
    }

    /**
     * 菜单编辑
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function edit($id=0){
        $title=$id ? "编辑":"新增";
        if ($id==0) {//新增
            $pid       = (int)input('param.pid');
            $pid_data  = $this->authRuleModel->get($pid);
            $menu_data = array('depend_flag'=>$pid_data['depend_flag'],'pid'=>$pid);
        }
        
        if(IS_POST){
            // 提交数据
            $data = $this->request->param();
            //验证数据
            $this->validateData($data,'AuthRule');
            $data['depend_type']=1;//后台添加默认依赖模块
            $id   = isset($data['id']) && $data['id']>0 ? $data['id']:false;

            if ($this->authRuleModel->editData($data,$id)) {
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
                $pid_data  = $this->authRuleModel->get($pid);
                $info = ['depend_flag'=>$pid_data['depend_flag'],'pid'=>$pid,'is_menu'=>1,'sort'=>99,'status'=>1];
            }

            $menus = logic('Auth')->getAdminMenu();
            $menus = array_merge([0=>['id'=>0,'title_show'=>'顶级菜单']], $menus);

            return builder('Form')
                    ->setMetaTitle($title.'菜单')  // 设置页面标题
                    ->addFormItem('id', 'hidden', 'ID', 'ID')
                    ->addFormItem('title', 'text', '标题', '用于后台显示的配置标题')
                    ->addFormItem('pid', 'multilayer_select', '上级菜单', '上级菜单',$menus)
                    ->addFormItem('depend_type', 'select', '来源类型', '来源类型。分别是模块，插件，主题',[1=>'模块',2=>'插件',3=>'主题'])
                    ->addFormItem('depend_flag', 'text', '来源标识', '如模块、插件、主题的标识名')
                    ->addFormItem('icon', 'icon', '字体图标', '字体图标')
                    ->addFormItem('name', 'text', '链接', '链接')
                    ->addFormItem('is_menu', 'radio', '后台菜单', '是否标记为后台菜单',[1=>'是',0=>'否'])
                    ->addFormItem('sort', 'number', '排序', '按照数值大小的倒叙进行排序，数值越小越靠前')
                    ->addFormItem('status', 'select', '状态', '',[0=>'禁用',1=>'启用'])
                    ->setFormData($info)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
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
            $builder->setMetaTitle('配置排序')
                    ->setListData($list)
                    ->addButton('submit')->addButton('back')
                    ->fetch();
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
     * 角色管理
     * @return [type] [description]
     * @date   2018-02-07
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function role(){
        // 获取所有角色
        list($data_list,$page) = $this->authGroupModel
            ->search() //添加搜索框
            ->getListByPage([],true,'id asc',20);

        return builder('List')        
                ->setMetaTitle('角色管理') // 设置页面标题
                ->addTopButton('addnew',array('href'=>url('roleEdit')))  // 添加新增按钮
                ->addTopButton('delete',['model'=>'AuthGroup'])  // 添加删除按钮
                ->setSearch()
                ->keyListItem('id', 'ID')
                ->keyListItem('title', '角色名')
                ->keyListItem('description', '描述')
                ->keyListItem('status', '状态','status')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListData($data_list)    // 数据列表
                ->setListPage($page) // 数据列表分页
                ->addRightButton('edit',['href'=>url('roleEdit',['group_id'=>'__data_id__']),'class'=>'btn btn-success btn-xs']) 
                ->addRightButton('self',['title'=>'权限分配','href'=>url('access',['group_id'=>'__data_id__']),'class'=>'btn btn-info btn-xs'])  
                ->addRightButton('self',array('title'=>'成员授权','href'=>url('accessUser',array('group_id'=>'__data_id__'))))    
                ->fetch();
    }
    
    //角色编辑
    public function roleEdit($group_id=0){
        $title = $group_id ? '编辑':'新增';
    
         $info =$this->authGroupModel->find($group_id);
         if (IS_POST) {
            $data = $this->request->param();
            $this->validateData($data,  
                                [
                                    ['title','require|chsAlpha','用户组名称不能为空|用户组名称只能是汉字和字母'],
                                    ['description','chsAlphaNum','描述只能是汉字字母数字']
                                ]
                            );
            $id   = isset($data['id']) && $data['id']>0 ? $data['id']:false;

            if ($this->authGroupModel->editData($data,$id)) {
                $this->success($title.'成功', url('role'));
            } else {
                $this->error($this->authGroupModel->getError());
            }

        } else {
            if ($group_id!=0) {
                $this->assign('group_id',$group_id);
            }
            $this->assign('meta_title',$title.'角色');
            $this->assign('info',$info);
            return $this->fetch();
        }
    }

    /**
     * 权限分配
     * @param  integer $group_id 组ID
     * @return [type] [description]
     * @date   2017-08-27
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function access($group_id=0){
        
        $title='权限分配'; 
        if (IS_POST && $group_id>0) {
            $data['id']    = $group_id;
            $menu_auth     = input('param.menu_auth/a','');//获取所有授权菜单
            $data['rules'] = implode(',',$menu_auth);
            $id   = isset($data['id']) && $data['id']>0 ? $data['id']:false;

            //开发过程中先关闭这个限制
            //if($group_id==1){
                //$this->error('不能修改超级管理员'.$title);
           // }else{
                if ($this->authGroupModel->editData($data,$id)) {
                    cache('admin_sidebar_menus_'.$this->currentUser['uid'],null);
                    $this->success($title.'成功', url('role'));
                }else{
                    $this->error($this->authGroupModel->getError());
                }
                
            //}

        } else{
            if ($group_id>0) {
                $this->assign('group_id',$group_id);
            }
            $this->assign('meta_title',$title);
            $role_auth_rule = $this->authGroupModel->where(['id'=>intval($group_id)])->value('rules');
            $this->assign('menu_auth_rules',explode(',',$role_auth_rule));//获取指定获取到的权限
            $rule = db('auth_rule')->select();
            $tree_obj = new \eacoo\Tree;
            $rule = $tree_obj->list_to_tree($rule);
            $this->assign('auth_rules_list',$rule);//所以规则
        }
        

        return $this->fetch();
    }

    /**
     * 用户组授权用户列表
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function accessUser($group_id=0)
    {
        if ($group_id!=0) {
            $this->assign('group_id',$group_id);
        }

        $auth_group = $this->authGroupModel->where(['status'=>['egt','0']])->field('id,title,rules')->select();
        foreach ($auth_group as $key => $row) {
            $authGroup[$row['id']]=$row;
        }
        //$list = $this->lists($model,array('a.group_id'=>$group_id,'m.status'=>array('egt',0)),'m.uid asc',null,'m.uid,m.nickname,m.last_login_time,m.last_login_ip,m.status');
        $list= $this->userModel->alias('m')->join ('__AUTH_GROUP_ACCESS__ a','m.uid=a.uid' )->where(['a.group_id'=>$group_id,'m.status'=>['egt',0]])->order('m.uid asc')->field('m.uid,m.nickname,m.last_login_time,m.last_login_ip,m.status')->paginate(20);

        $this->assign( '_list',     $list );
        $this->assign( 'page',     $list->render());
        $this->assign('auth_group', $authGroup);
        $this->assign('this_group', $authGroup[(int)$group_id]);
        $this->assign('meta_title','成员授权');
        return $this->fetch();
    }

    /**
     * 创建管理员用户组
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    // public function createGroup(){
    //     if ( empty($this->auth_group) ) {
    //         $this->assign('auth_group',['title'=>null,'id'=>null,'description'=>null,'rules'=>null]);//排除notice信息
    //     }
    //     $this->assign('meta_title','新增用户组');
    //     return $this->fetch('editgroup');
    // }

    /**
     * 编辑管理员用户组
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    // public function editGroup(){
    //     $auth_group = $this->authGroupModel->find( (int)$_GET['id'] );
    //     $this->assign('auth_group',$auth_group);
    //     $this->assign('meta_title','编辑用户组');
    //     return $this->fetch();
    // }

    /**
     * 管理员用户组数据写入/更新
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    // public function writeGroup(){
    //     $data = input('param.');
    //     if(isset($data['rules'])){
    //         sort($data['rules']);
    //         $data['rules']  = implode( ',' , array_unique($data['rules']));
    //     }

    //     $id   = isset($data['id']) && $data['id']>0 ? $data['id']:false;
    //     if ($this->authGroupModel->editData($data,$id)) {
    //         $this->success('操作成功!',url('index'));
    //     } else {
    //         $this->error('操作失败'.$this->authGroupModel->getError());
    //     }

    // }
    
    /**
     * 修改用户组描述
     */
    // public function descriptionGroup()
    // {
    //     $title               = input('param.title');
    //     $description         = input('param.description');
    //     $id                  = input('param.id');
    //     $data['description'] = $description;
    //     $data['title']       = $title;
    //     $res=$this->authGroupModel->where('id='.$id)->save($data);
    //     if($res)
    //     {
    //         $this->success('修改成功!');
    //     }
    //     else{
    //         $this->error('修改失败!');
    //     }

    // }

    /**
     * 将用户添加到用户组,入参uid,group_id
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function addToGroup(){
        try {
            $uids = input('uids',false);//新增批量用户
            if ($uids) {
                $uid = explode(',',$uids);
            }else{
                $uid = input('uid');
            }
            
            $gid = input('param.group_id');
            if( empty($uid) ){
                throw new \Exception("参数有误", 0);
                
            }
            if(is_numeric($uid)){
                if ( is_administrator($uid) ) {
                    throw new \Exception("该用户为超级管理员", 0);
                }
                if( !$this->userModel->where(['uid'=>$uid])->find() ){
                    throw new \Exception("用户不存在", 0);
                }
            }

            if( $gid && !$this->authGroupModel->checkGroupId($gid)){
                $this->error($this->authGroupModel->error);
            }
            if ( !logic('AuthGroup')->addToGroup($uid,$gid) ){
                $this->error($this->authGroupModel->getError());
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('操作成功');
        
    }

    /**
     * 将用户从用户组中移除  入参:uid,group_id
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function removeFromGroup(){
        $uid = input('param.uid');
        $gid = input('param.group_id');
        if( $uid==is_login() ){
            $this->error('不允许解除自身授权');
        }
        if( empty($uid) || empty($gid) ){
            $this->error('参数有误');
        }
        if( !$this->authGroupModel->find($gid)){
            $this->error('用户组不存在');
        }
        if ( logic('AuthGroup')->removeFromGroup($uid,$gid) ){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
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