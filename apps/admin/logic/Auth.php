<?php
// 授权管理控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\logic;

use app\admin\model\AuthRule as AuthRuleModel;
use app\admin\model\AuthGroup as AuthGroupModel;
use app\admin\model\AuthGroupAccess as AuthGroupAccessModel;
use app\common\model\User as UserModel;

use eacoo\Tree;

class Auth extends AdminLogic {

    protected $authRuleModel;
    protected $authGroupModel;
    protected $userModel;

    protected function initialize()
    {
        parent::initialize();

        $this->authRuleModel  = new AuthRuleModel;
        $this->authGroupModel = new AuthGroupModel;
        $this->userModel      = new UserModel;

    }
    
    /**
     * 后台菜单管理(规则)
     * @return [type] [description]
     */
    public function getAdminMenu(){
        $menus = model('AuthRule')->search()->getList([],true,'sort asc,id asc');

        $menus = collection($menus)->toArray();
        $tree_obj = new Tree;
        return $menus = $tree_obj->toFormatTree($menus,'title');
    }

    /**
     * 获取构建器使用的tablist
     * @return [type] [description]
     * @date   2018-02-07
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getTabList()
    {             
        $module_list = logic('Module')->getModules();
        $tab_list = ['all'=>['title'=>'全部','href'=>url('index')]];
        foreach ($module_list as $key => $row) {
            $tab_list[$key] = ['title'=>$row,'href'=>url('index',['depend_flag'=>$key])];
        }
        return $tab_list;
    }

    /**
     * 获取表单的菜单关联组
     * @param  integer $depend_type [description]
     * @return [type] [description]
     * @date   2018-02-20
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getDependFlags($depend_type=0)
    {
        if (!$depend_type) {
            return false;       
        }
        switch ($depend_type) {
            case 1://模块
                $data_list = logic('Module')->getModules();
                break;
            case 2://插件
                $data_list = model('admin/Plugins')->where('status',1)->column('title','name');
                break;
            case 3://主题
                $data_list = model('admin/Theme')->where('status',1)->column('title','name');
                break;
            default:
                # code...
                break;
        }
        return $data_list;
    }

    /**
     * 获取后台菜单表单的html
     * @return [type] [description]
     * @date   2018-02-20
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getFormMenuHtml()
    {
        $html = <<<EOF
<script type="text/javascript">
 $(function () {
    var depend_type = $('#depend_type').find("option:selected").val();
    var depend_flag = $('#depend_flag').find("option:selected").val();
    switch_select_dependflag_function(depend_type,depend_flag);
    $('#depend_type').on('change',function(){
        var depend_type = $('#depend_type').find("option:selected").val();
        switch_select_dependflag_function(depend_type,0);
    });
})
//事件方法
function switch_select_dependflag_function(type,depend_flag){
    $.get(url("admin/Menu/getSelectDependFlags"),{depend_type:type},function(result){
        if(type == 1){//模块
            var oname = '模块';
        } else if(type == 2){//插件
            var oname = '插件';
        } else{//主题
            var oname = '主题';
        }
        var append_html='<option value="0">请选择一个'+oname+'</option>';
        $.each(result,function(name,value) {
            var selected='';
            if(depend_flag==name){
                var selected = 'selected';
            }
            append_html+='<option value="'+name+'" '+selected+'>'+value+'（'+name+'）</option>';
        });
        $('#depend_flag').html(append_html);
      });
    
}
</script>
EOF;
    return $html;
    }

    /**
     * 对菜单进行排序
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function ruleSort($ids = null)
    {
        $builder    = builder('Sort');
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
            $list = $this->authRuleModel->getList($map, 'sort asc', 'id,title,sort');
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
    public function moveMenusPosition($ids,$to_pid) {

        cache('admin_sidebar_menus_'.$this->currentUser['uid'],null);
        $map['id'] = ['in',$ids];
        $data = array('pid' => $to_pid);
        $result = model('auth_rule')->editRow($data, $map);
        return $result;

    }

    /**
     * 构建列表移动配置分组按钮
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function moveMenuHtml(){

        //移动菜单位置
        $menus = db('auth_rule')->select();
        $tree_obj = new Tree;
        $menus = $tree_obj->toFormatTree($menus,'title');
        $menu_options = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级菜单')), $menus);
        $menu_options_str='';
        foreach ($menu_options as $key => $option) {
                if(is_array($option)){
                    $menu_options_str.='<option value="'.$option['id'].'">'.$option['title_show'].'</option>';
                }else{
                    $menu_options_str.='<option value="'.$option['id'].'">'.$option.'</option>';
                }
        }
        $move_position_url = url('moveMenusPosition');
        return <<<EOF
        <div class="modal fade mt100" id="movemenuPositionModal">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
                        <p class="modal-title">移动至</p>
                    </div>
                    <div class="modal-body">
                        <form action="{$move_position_url}" method="post" class="form-movemenu">
                            <div class="form-group">
                                <select name="to_pid" class="form-control">{$menu_options_str}</select>
                            </div>
                            <div class="form-group">
                                <input type="hidden" name="ids">
                                <button class="btn btn-primary btn-block submit ajax-post" type="submit" target-form="form-movemenu">确 定</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            function move_menuposition(){
                var ids = '';
                $('input[name="ids[]"]:checked').each(function(){
                   ids += ',' + $(this).val();
                });
                if(ids != ''){
                    ids = ids.substr(1);
                    $('input[name="ids"]').val(ids);
                    $('.modal-title').html('移动选中的菜单至：');
                    $('#movemenuPositionModal').modal('show', 'fit')
                }else{
                    updateAlert('请选择需要移动的菜单', 'warning');
                }
            }
        </script>
EOF;
    }

    //角色管理
    public function role(){
        // 搜索
        $keyword = input('param.keyword');
        if ($keyword) {
            $this->authGroupModel->where('title','like','%'.$keyword.'%');
        }
        // 获取所有角色
        $map['status'] = array('egt', '0'); // 禁用和正常状态
        list($data_list,$page) = $this->authGroupModel->getListByPage($map,'id asc','*',20);
        // 使用Builder快速建立列表页面。

        return builder('List')       
                ->setMetaTitle('角色管理') // 设置页面标题
                ->addTopButton('addnew',array('href'=>url('roleEdit')))  // 添加新增按钮
                ->addTopButton('delete',['model'=>'AuthGroup'])  // 添加删除按钮
                ->setSearch('搜索角色','')
                ->keyListItem('id', 'ID')
                ->keyListItem('title', '角色名')
                ->keyListItem('description', '描述')
                ->keyListItem('status', '状态','status')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListData($data_list)    // 数据列表
                ->setListPage($page) // 数据列表分页
                ->addRightButton('edit',['href'=>url('roleEdit',['group_id'=>'__data_id__']),'class'=>'btn btn-success btn-xs']) 
                ->addRightButton('edit',['title'=>'权限分配','href'=>url('access',['group_id'=>'__data_id__']),'class'=>'btn btn-info btn-xs'])  
                ->addRightButton('edit',array('title'=>'成员授权','href'=>url('accessUser',array('group_id'=>'__data_id__'))))    
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
            
            //$data里包含主键id，则editData就会更新数据，否则是新增数据
            if ($this->authGroupModel->editData($data)) {
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
        if ($group_id!=0) {
            $this->assign('group_id',$group_id);
        }
        $title='权限分配';
        $this->assign('meta_title',$title);

        if (IS_POST && $group_id!=0) {
            $data['id']    = $group_id;
            $menu_auth     = input('post.menu_auth/a','');//获取所有授权菜单
            $data['rules'] = implode(',',$menu_auth);

            //开发过程中先关闭这个限制
            //if($group_id==1){
                //$this->error('不能修改超级管理员'.$title);
           // }else{
                //$data里包含主键id，则editData就会更新数据，否则是新增数据
                if ($this->authGroupModel->editData($data)) {
                    cache('admin_sidebar_menus_'.$this->currentUser['uid'],null);
                    $this->success($title.'成功', url('role'));
                }else{
                    $this->error($this->authGroupModel->getError());
                }
                
            //}

        } else{
            $role_auth_rule = $this->authGroupModel->where(['id'=>intval($group_id)])->value('rules');
            $this->assign('menu_auth_rules',explode(',',$role_auth_rule));//获取指定获取到的权限
        }
        $menu = $this->authRuleModel->where(['pid'=>0,'status'=>1])->order('sort asc')->select();
        foreach($menu as $k=>$v){
            $menu[$k]['_child']=$this->authRuleModel->where(['pid'=>$v['id']])->order('sort asc')->select();
        }
        $this->assign('all_auth_rules',$menu);//所以规则
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
    public function createGroup(){
        if ( empty($this->auth_group) ) {
            $this->assign('auth_group',['title'=>null,'id'=>null,'description'=>null,'rules'=>null]);//排除notice信息
        }
        $this->assign('meta_title','新增用户组');
        return $this->fetch('editgroup');
    }

    /**
     * 编辑管理员用户组
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function editGroup(){
        $auth_group = $this->authGroupModel->find( (int)$_GET['id'] );
        $this->assign('auth_group',$auth_group);
        $this->assign('meta_title','编辑用户组');
        return $this->fetch();
    }

    /**
     * 管理员用户组数据写入/更新
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function writeGroup(){
        $data = input('param.');
        if(isset($data['rules'])){
            sort($data['rules']);
            $data['rules']  = implode( ',' , array_unique($data['rules']));
        }

        if ($this->authGroupModel->editData($data)) {
            $this->success('操作成功!',url('index'));
        } else {
            $this->error('操作失败'.$this->authGroupModel->getError());
        }

    }
    /**
     * 修改用户组描述
     */
    public function descriptionGroup()
    {
        $title               = input('param.title');
        $description         = input('param.description');
        $id                  = input('param.id');
        $data['description'] = $description;
        $data['title']       = $title;
        $res=$this->authGroupModel->where('id='.$id)->save($data);
        if($res)
        {
            $this->success('修改成功!');
        }
        else{
            $this->error('修改失败!');
        }

    }
    /**
     * 将用户添加到用户组,入参uid,group_id
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function addToGroup(){
        $uids = input('uids',false);//新增批量用户
        if ($uids) {
            $uid = explode(',',$uids);
        }else{
            $uid = input('uid');
        }
        
        $gid = input('param.group_id');
        if( empty($uid) ){
            $this->error('参数有误');
        }
        if(is_numeric($uid)){
            if ( is_administrator($uid) ) {
                $this->error('该用户为超级管理员');
            }
            if( !$this->userModel->where(['uid'=>$uid])->find() ){
                $this->error('用户不存在');
            }
        }

        if( $gid && !$this->authGroupModel->checkGroupId($gid)){
            $this->error($this->authGroupModel->error);
        }
        if ( $this->authGroupModel->addToGroup($uid,$gid) ){
            $this->success('操作成功');
        }else{
            $this->error($this->authGroupModel->getError());
        }
    }

    /**
     * 将用户从用户组中移除  入参:uid,group_id
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function removeFromGroup(){
        $uid = input('param.uid');
        $gid = input('param.group_id');
        if( $uid==is_login()){
            $this->error('不允许解除自身授权');
        }
        if( empty($uid) || empty($gid) ){
            $this->error('参数有误');
        }
        if( !$this->authGroupModel->find($gid)){
            $this->error('用户组不存在');
        }
        if ( $this->authGroupModel->removeFromGroup($uid,$gid) ){
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