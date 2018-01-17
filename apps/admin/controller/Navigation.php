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

use app\admin\model\AuthRule;
use app\admin\model\AuthGroup;
use app\admin\model\AuthGroupAccess;
use app\common\model\User;

use app\admin\builder\Builder;
use eacoo\Tree;

class Navigation extends Admin {

    protected $authRuleModel;
    protected $authGroupModel;
    protected $moduleList;
    protected $userModel;

    function _initialize()
    {
        parent::_initialize();

        $this->authRuleModel  = new AuthRule();
        $this->authGroupModel = new AuthGroup();
        $this->userModel     = new User;

        $default_module = [ 
                        'admin'   =>'后台模块',
                        'home'    =>'前台模块',
                        ];
        $moduleList = db('modules')->where('status',1)->column('title','name');                
        $this->moduleList = $default_module+$moduleList;

    }
    
    /**
     * 规则管理
     * @return [type] [description]
     */
    public function index(){
        
        // 搜索
        $keyword = input('param.keyword');
        if ($keyword) {
            $this->authRuleModel->where('id|name|title','like','%'.$keyword.'%');
        }
        $pid = input('param.pid',0);
        // 获取所有节点信息
        //$map['pid'] = input('param.pid',0);//是否存在父ID
        //$map['is_menu']=1;//只显示菜单
        $map = [];
        $meta_title='规则管理';

        $depend_flag = input('param.depend_flag','all');//管理类型
        if ($depend_flag!='all') {
            $this->authRuleModel->where('depend_flag',$depend_flag);
        }
        $data_list = $this->authRuleModel->where($map)->order('depend_flag,pid asc,sort asc')->field(true)->paginate(20);
        foreach ($data_list as $key=>$list) {
            $data_list[$key]['p_menu']= $this->authRuleModel->where(['id'=>(int)$list['pid']])->value('title');
        }

        //是否标记为菜单：0否，1是
        $marker_menu0_attr['title'] = '取消菜单标记';
        $marker_menu0_attr['class'] = 'btn btn-primary btn-sm confirm ajax-post';
        $marker_menu0_attr['href'] = url('markerMenu',['status'=>0]);
        $marker_menu0_attr['target-form'] ="ids";

        $marker_menu1_attr['title'] = '标记为菜单';
        $marker_menu1_attr['class'] = 'btn btn-primary btn-sm ajax-post';
        $marker_menu1_attr['href'] = url('markerMenu',['status'=>1]);
        $marker_menu1_attr['target-form'] ="ids";

         //移动模块按钮属性
        $movemodule_attr['title'] = '<i class="fa fa-exchange"></i> 移动模块';
        $movemodule_attr['class'] = 'btn btn-info btn-sm';
        $movemodule_attr['onclick'] = 'move_module()';

        //移动上级按钮属性
        $moveparent_attr['title'] = '<i class="fa fa-exchange"></i> 移动位置';
        $moveparent_attr['class'] = 'btn btn-info btn-sm';
        $moveparent_attr['onclick'] = 'move_menuparent()';

        $extra_html=$this->moveMenuHtml();//添加移动按钮html
        $tab_list = ['all'=>['title'=>'全部','href'=>url('index')]];
        foreach ($this->moduleList as $key => $row) {
            $tab_list[$key] = ['title'=>$row,'href'=>url('index',['depend_flag'=>$key])];
        }
        
        Builder::run('List')
            ->setMetaTitle($meta_title)
            ->addTopBtn('addnew',array('href'=>url('ruleEdit',['pid'=>$pid])))  // 添加新增按钮
            ->addTopBtn('resume',array('model'=>'auth_rule'))  // 添加启用按钮
            ->addTopBtn('forbid',array('model'=>'auth_rule'))  // 添加禁用按钮
            ->addTopBtn('delete',array('model'=>'auth_rule'))  // 添加删除按钮
            ->setTabNav($tab_list, $depend_flag)  // 设置页面Tab导航
            //->addTopButton('self', $movemodule_attr) //移动模块
            ->addTopButton('self', $moveparent_attr) //移动菜单位置
            ->addTopBtn('sort',['model'=>'auth_rule','href'=>url('rule_sort',['pid'=>$pid])])  // 添加排序按钮
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
            ->setListDataKey('id')
            ->setListData($data_list)    // 数据列表
            ->setListPage($data_list->render()) // 数据列表分页
            ->setExtraHtml($extra_html)
            ->addRightButton('edit',array('href'=>url('ruleEdit',array('id'=>'__data_id__'))))      // 添加编辑按钮
            ->addRightButton('forbid',array('model'=>'auth_rule'))// 添加删除按钮
            ->addFootBtn('self', $marker_menu0_attr)->addFootBtn('self', $marker_menu1_attr)
            ->alterListData(
                array('key' => 'pid', 'value' =>'0'),
                array('p_menu' => '无'))
            ->fetch();
    }

    /**
     * 后台菜单管理(规则)
     * @return [type] [description]
     */
    public function adminMenu(){
        $manage_type = input('get.manage_type','menu');//管理类型
        // 获取所有节点信息
        $map['pid'] = input('param.pid',0);//是否存在父ID
        $map['is_menu']=1;//只显示菜单
        if ($map['pid']>0) {
            $current_submenu_name = $this->authRuleModel->where(['id'=>(int)$map['pid']])->value('title');
            $meta_title = '<a onclick="javascript:history.back(-1);return false;">'.$current_submenu_name.'</a>》子菜单管理';
        } else{
            $meta_title='菜单管理';
        }
        
        list($data_list,$page) = $this->authRuleModel->getListByPage($map,'sort asc','*',20);
        foreach ($data_list as $key=>$list) {
            $data_list[$key]['p_menu']= $this->authRuleModel->where(['id'=>(int)$list['pid']])->value('title');
        }

        //是否标记为菜单：0否，1是
        $marker_menu0_attr['title'] = '取消菜单标记';
        $marker_menu0_attr['class'] = 'btn btn-primary btn-sm confirm ajax-post';
        $marker_menu0_attr['href'] = url('markerMenu',['status'=>0]);
        $marker_menu0_attr['target-form'] ="ids";

        $marker_menu1_attr['title'] = '标记为菜单';
        $marker_menu1_attr['class'] = 'btn btn-primary btn-sm ajax-post';
        $marker_menu1_attr['href'] = url('markerMenu',['status'=>1]);
        $marker_menu1_attr['target-form'] ="ids";

         //移动模块按钮属性
        $movemodule_attr['title'] = '<i class="fa fa-exchange"></i> 移动模块';
        $movemodule_attr['class'] = 'btn btn-info btn-sm';
        $movemodule_attr['onclick'] = 'move_module()';

        //移动上级按钮属性
        $moveparent_attr['title'] = '<i class="fa fa-exchange"></i> 移动位置';
        $moveparent_attr['class'] = 'btn btn-info btn-sm';
        $moveparent_attr['onclick'] = 'move_menuparent()';

        $extra_html=$this->moveMenuHtml();//添加移动按钮html

        Builder::run('List')
            ->setMetaTitle($meta_title)
            ->addTopBtn('addnew',array('href'=>url('ruleEdit',array('pid'=>$map['pid']))))  // 添加新增按钮
            ->addTopBtn('resume',array('model'=>'auth_rule'))  // 添加启用按钮
            ->addTopBtn('forbid',array('model'=>'auth_rule'))  // 添加禁用按钮
            ->addTopBtn('delete',array('model'=>'auth_rule'))  // 添加删除按钮
            //->addTopButton('self', $movemodule_attr) //移动模块
            ->addTopButton('self', $moveparent_attr) //移动菜单位置
            ->keyListItem('id','ID')
            ->keyListItem('title','名称','link',['link'=>url('Auth/adminMenu',['pid'=>'__data_id__'])])
            ->keyListItem('p_menu','上级菜单')
            ->keyListItem('name', 'URL')
            ->keyListItem('depend_flag', '来源标识')
            ->keyListItem('sort', '排序')
            ->keyListItem('is_menu','菜单','array',[0=>'否',1=>'是'])
            ->keyListItem('status','状态','status')
            ->keyListItem('right_button', '操作', 'btn')
            ->setListDataKey('id')
            ->setListData($data_list)    // 数据列表
            ->setListPage($page) // 数据列表分页
            ->setExtraHtml($extra_html)
            ->addRightButton('edit')      // 添加编辑按钮
            ->addRightButton('forbid',array('model'=>'auth_rule'))// 添加删除按钮
            ->addFootBtn('self', $marker_menu0_attr)->addFootBtn('self', $marker_menu1_attr)
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
            $pid_data  = $this->authRuleModel->find($pid);
            $menu_data = array('depend_flag'=>$pid_data['depend_flag'],'pid'=>$pid);
        }
        
        if(IS_POST){
            // 提交数据
            $data = $this->request->param();
            //验证数据
            $this->validateData($data,'AuthRule');
            $data['depend_type']=1;//后台添加默认依赖模块
            $id   =isset($data['id']) && $data['id']>0 ? $data['id']:false;

            if ($this->authRuleModel->editData($data,$id)) {
                cache('admin_sidebar_menus_'.$this->currentUser['uid'],null);//清空后台菜单缓存
                $this->success($title.'菜单成功', url('index',array('pid'=>input('param.pid'))));
            } else {
                $this->error($this->authRuleModel->getError());
            }   

        } else{
            // 获取菜单数据
            if ($id!=0) {
                $menu_data = $this->authRuleModel->find($id);
            }
            $menus = db('auth_rule')->select();
            $tree_obj = new Tree;
            $menus = $tree_obj->toFormatTree($menus,'title');

            $menus = array_merge([0=>['id'=>0,'title_show'=>'顶级菜单']], $menus);

            Builder::run('Form')
                    ->setMetaTitle($title.'菜单')  // 设置页面标题
                    ->addFormItem('id', 'hidden', 'ID', 'ID')
                    ->addFormItem('title', 'text', '标题', '用于后台显示的配置标题')
                    ->addFormItem('depend_flag', 'select', '所属模块', '所属的模块，模块菜单必须选择，否则无法导出',$this->moduleList)  
                    ->addFormItem('pid', 'multilayer_select', '上级菜单', '上级菜单',$menus)
                    ->addFormItem('icon', 'icon', '字体图标', '字体图标')
                    ->addFormItem('name', 'text', '链接', '链接')
                    ->addFormItem('is_menu', 'radio', '后台菜单', '是否标记为后台菜单',[1=>'是',0=>'否'])
                    ->addFormItem('no_pjax', 'radio', 'Pjax加载', '标记后台菜单后，是否Pjax方式打开该页面',[0=>'是',1=>'否'])
                    ->addFormItem('sort', 'number', '排序', '排序')
                    ->setFormData($menu_data)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }   
        
    }

    /**
     * 构建列表移动配置分组按钮
     * @author 心云间、凝听 <981248356@qq.com>
     */
    protected function moveMenuHtml(){
            //构造移动文档的目标分类列表
            $options = '';
            foreach ($this->moduleList as $key => $val) {
                $options .= '<option value="'.$key.'">'.$val.'</option>';
            }
            //文档移动POST地址
            $move_url = url('moveModule');

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
            $move_menuparent_url = url('moveMenuParent');
            return <<<EOF
            <div class="modal fade mt100" id="movemoduleModal">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
                            <p class="modal-title">移动至</p>
                        </div>
                        <div class="modal-body">
                            <form action="{$move_url}" method="post" class="form-movemodule">
                                <div class="form-group">
                                    <select name="to_module" class="form-control">{$options}</select>
                                </div>
                                <div class="form-group">
                                    <input type="hidden" name="ids">
                                    <button class="btn btn-primary btn-block submit ajax-post" type="submit" target-form="form-movemodule">确 定</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade mt100" id="movemenuParentModal">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
                            <p class="modal-title">移动至</p>
                        </div>
                        <div class="modal-body">
                            <form action="{$move_menuparent_url}" method="post" class="form-movemenu">
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
                function move_module(){
                    var ids = '';
                    $('input[name="ids[]"]:checked').each(function(){
                       ids += ',' + $(this).val();
                    });
                    if(ids != ''){
                        ids = ids.substr(1);
                        $('input[name="ids"]').val(ids);
                        $('.modal-title').html('移动选中的菜单至：');
                        $('#movemoduleModal').modal('show', 'fit')
                    }else{
                        updateAlert('请选择需要移动的菜单', 'warning');
                    }
                }
                function move_menuparent(){
                    var ids = '';
                    $('input[name="ids[]"]:checked').each(function(){
                       ids += ',' + $(this).val();
                    });
                    if(ids != ''){
                        ids = ids.substr(1);
                        $('input[name="ids"]').val(ids);
                        $('.modal-title').html('移动选中的菜单至：');
                        $('#movemenuParentModal').modal('show', 'fit')
                    }else{
                        updateAlert('请选择需要移动的菜单', 'warning');
                    }
                }
            </script>
EOF;
    }
}