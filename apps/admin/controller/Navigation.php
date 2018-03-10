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

use app\common\model\Nav as NavModel;
use eacoo\Tree;

class Navigation extends Admin {

    protected $navModel;

    function _initialize()
    {
        parent::_initialize();
        $this->navModel = new NavModel;
    }
    
    /**
     * 前台导航菜单管理
     * @return [type] [description]
     */
    public function index(){

        //移动上级按钮属性
        $move_position_attr = [
            'icon'    =>'fa fa-exchange',
            'title'   =>'移动位置',
            'class'   =>'btn btn-info btn-sm',
            'onclick' =>'move_menu_position()'
        ];
        $total = $this->navModel->count();
        return builder('List')
                ->setMetaTitle('前台导航管理')
                ->addTopBtn('addnew')  // 添加新增按钮
                ->addTopBtn('resume',['model'=>'Nav'])  // 添加启用按钮
                ->addTopBtn('forbid',['model'=>'Nav'])  // 添加禁用按钮
                ->addTopBtn('delete',['model'=>'Nav'])  // 添加删除按钮
                ->addTopButton('self', $move_position_attr) //移动菜单位置
                ->addTopBtn('sort',['model'=>'Nav','href'=>url('sort')])  // 添加排序按钮
                //->setSearch('', url('rule'))
                ->keyListItem('id','ID')
                ->keyListItem('title_show','名称')
                ->keyListItem('value', 'URL（支持完整http地址和三段式式）')
                ->keyListItem('icon', '图标','icon')
                ->keyListItem('target','打开方式','array',['_blank'=>'新的窗口打开','_self'=>'本窗口打开'])
                ->keyListItem('depend_type', '来源类型','array',[0=>'外部链接',1=>'模块',2=>'插件',3=>'主题'])
                ->keyListItem('depend_flag', '来源标识')
                ->keyListItem('sort', '排序')
                ->keyListItem('status','状态','status')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListPrimaryKey('id')
                ->setListPage($total,false)
                ->setListData(logic('Navigation')->getNavMenus())    // 从逻辑层获取数据
                ->setExtraHtml(logic('Navigation')->moveMenuHtml())//添加移动按钮html
                ->addRightButton('edit')      // 添加编辑按钮
                ->fetch();
    }

    /**
     * 菜单编辑
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function edit($id=0){
        $title = $id ? "编辑":"新增";
        if ($id==0) {//新增
            $pid       = (int)input('param.pid');
            $pid_data  = $this->navModel->get($pid);
            $menu_data = array('depend_flag'=>$pid_data['depend_flag'],'pid'=>$pid);
        }
        
        if(IS_POST){
            // 提交数据
            $data = $this->request->param();
            //验证数据
            $this->validateData($data,
                                [
                                    ['title','require|chsAlpha','名称不能为空|名称只能是汉字和字母'],
                                    ['value','require','导航地址不能为空'],
                                    ['position','require|in:header,my','请选择导航显示位置|请选择正确的导航显示位置'],
                                    ['depend_type','require|in:0,1,2,3','请设置来源类型|请设置正确来源类型'],
                                ]);
            
            //$data里包含主键id，则editData就会更新数据，否则是新增数据
            if ($this->navModel->editData($data)) {
                cache('front_'.$data['position'].'_navs',null);//清空前台导航缓存
                $this->success($title.'菜单成功', url('index',array('pid'=>input('param.pid'))));
            } else {
                $this->error($this->navModel->getError());
            }   

        } else{
            $info = ['target'=>'_self','sort'=>99,'status'=>1];
            // 获取菜单数据
            if ($id>0) {
                $info = NavModel::get($id);
            }
            $menus = $this->navModel->select();
            if (!empty($menus)) {
                $menus = collection($menus)->toArray();
                $tree_obj = new Tree;
                $menus = $tree_obj->toFormatTree($menus,'title');
            }
            $menus = array_merge([0=>['id'=>0,'title_show'=>'顶级菜单']], $menus);
            return builder('Form')
                    ->setMetaTitle($title.'导航菜单')  // 设置页面标题
                    ->addFormItem('id', 'hidden', 'ID', 'ID')
                    ->addFormItem('title', 'text', '标题', '用于前台显示的导航标题')
                    ->addFormItem('pid', 'multilayer_select', '上级菜单', '上级菜单',$menus)
                    ->addFormItem('value', 'text', 'URL', '导航地址。支持url生成规则，三段式')
                    ->addFormItem('position', 'select', '位置', '导航菜单显示位置，页面头部，登录个人中心',['header'=>'头部(Header)','my'=>'我的(My)'],'require')
                    ->addFormItem('target', 'select', '打开方式', '',['_blank'=>'新的窗口打开','_self'=>'本窗口打开'])
                    ->addFormItem('icon', 'icon', '字体图标', '字体图标')
                    ->addFormItem('depend_type', 'select', '来源类型', '来源类型。分别是模块，插件，主题',[0=>'外部链接',1=>'模块',2=>'插件',3=>'主题'])
                    ->addFormItem('depend_flag', 'text', '来源标识', '如模块、插件、主题的标识名。外部链接可不填写')
                    ->addFormItem('sort', 'number', '排序', '排序')
                    ->addFormItem('status', 'radio', '状态', '状态，开启或关闭',[1=>'是',0=>'否'])
                    ->setFormData($info)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }   
        
    }

    /**
     * 移动菜单位置
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function moveMenusPosition() {
        if (IS_POST) {
            $ids    = input('param.ids');
            $to_pid = input('param.to_pid');
            if ($to_pid || $to_pid==0) {
                $result = logic('Navigation')->moveMenusPosition($ids,$to_pid);
                if ($result) {
                    $this->success('移动成功',url('index'));
                } else{
                    $this->error('移动成功',url('index'));
                }
            } else{
                $this->error('请选择目标菜单'.$to_pid);
            }
            
        }
        
    }

    /**
     * 对菜单进行排序
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function sort($ids = null)
    {
        $builder= builder('Sort');
        $pid = input('param.pid',false);//是否存在父ID
        $map = [];
        if ($pid>0 || $pid===0) {
            $map['pid'] = $pid;
        } 
        
        if (IS_POST) {
            cache('front_header_navs',null);//清空前台导航缓存
            cache('front_my_navs',null);//清空前台我的缓存
            $builder->doSort('nav', $ids);
        } else {
            //$map['status'] = array('egt', 0);
            $list = $this->navModel->getList($map,'id,title,sort','sort asc');
            foreach ($list as $key => $val) {
                $list[$key]['title'] = $val['title'];
            }
            return $builder->setMetaTitle('配置排序')
                    ->setListData($list)
                    ->addButton('submit')
                    ->addButton('back')
                    ->fetch();
        }
    }
}