<?php
// 分类管理控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\common\model\Terms as TermsModel;
use eacoo\Tree;

class Terms extends Admin {

    protected $termsModel;
    protected $termTaxonomy;

    function _initialize()
    {
        parent::_initialize();
        $this->termsModel = new TermsModel();
        $this->termTaxonomy = config('term_taxonomy');//获取所有分类法
    }

	/**
     * 分类管理
     * @param  string $taxonomy 分类法
     * @param  [type] $fromTable 来源的数据库表名
     * @param  array $tab_obj [description]
     * @param  [type] $edit_U [description]
     * @return [type] [description]
     * @date   2018-01-20
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index($taxonomy='all',$from_table='',$tab_nav=[],$edit_U=NULL){
        $map =[];
        
        if ($taxonomy!='all') {
            $map['taxonomy']=$taxonomy;
        }
        
        list($data_list,$total) = $this->termsModel->search('name,slug')->getListByPage($map,true,'sort desc,create_time desc',15);
        $addnew_href=null;
        if ($edit_U) {
           $addnew_href=['href'=>$edit_U];//新增按钮URL
        }
        if (!empty($data_list)) {
            foreach ($data_list as $key => &$row) {
                $row['object_count'] = logic('common/Terms')->termRelationCount($row['term_id'],$from_table);
            }
        }
        $builder = builder('List')->setMetaTitle('分类管理');
        if (!empty($tab_nav)) {//构建tab
            $builder->setTabNav($tab_nav['tab_list'],$tab_nav['current']);  // 设置页面Tab导航
        }
        return $builder->addTopButton('addnew',$addnew_href)  // 添加新增按钮
                        ->addTopButton('resume')  // 添加启用按钮
                        ->addTopButton('forbid')  // 添加禁用按钮
                        ->addTopButton('recycle') //添加回收按钮
                        ->setSearch('输入分类名称', url('index'))
                        ->keyListItem('term_id', 'ID')
                        ->keyListItem('name', '名称','link',url('index',['term_id'=>'__data_id__']))//约定分类对象
                        ->keyListItem('slug', '别名')
                        ->keyListItem('parent', '父分类')
                        ->keyListItem('seo_description', '描述')
                        ->keyListItem('object_count', '对象数')
                        ->keyListItem('status', '状态', 'status')
                        ->keyListItem('right_button', '操作', 'btn')
                        ->setListPrimaryKey('term_id')
                        ->setListData($data_list)    // 数据列表
                        ->setListPage($total,15) // 数据列表分页
                        ->addRightButton('edit',$addnew_href)// 添加编辑按钮
                        ->addRightButton('recycle')// 添加删除按钮
                        ->fetch();

    }

	/**
     * 分类编辑
     * @param  integer $term_id [description]
     * @param  [type] $taxonomy [description]
     * @param  array $tab_obj [description]
     * @return [type] [description]
     * @date   2018-01-20
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function edit($term_id=0,$taxonomy,$tab_obj=[]){
        $title = $term_id>0 ? "编辑" : "新增";
        if (IS_POST) {
            // 提交数据
            $data = $this->request->param();
            // seo标题
            if ($data['seo_title'] === '') {
                $data['seo_title']=$data['name'];
            }
            $data['taxonomy'] = $taxonomy;
            
            //验证数据
            $this->validateData($data,
                                [
                                    ['name','require|chsDash','分类名称不能为空|分类名称只能是汉字和字母'],
                                    ['taxonomy','require|alphaDash','描述只能是汉字字母数字|分类法名称只能是字母和数字，下划线符合']
                                ]);
            //$data里包含主键term_id，则editData就会更新数据，否则是新增数据
            $result = $this->termsModel->editData($data);
            if ($result) {
                $this->success($title.'成功', url($tab_obj['current']));
            } else {
                $this->error($this->termsModel->getError());
            }

        } else {
            $info=[];
            if ($term_id!=0) {
                $info = TermsModel::get($term_id);
            }
            $p_terms = TermsModel::where(['taxonomy'=>$taxonomy])->select();
            $p_terms = $p_terms->toArray();
            $tree_obj = new Tree;
            $p_terms = $tree_obj->toFormatTree($p_terms,'name','term_id');

            foreach ($p_terms as $key => $term) {
                $p_terms[$key]['id']= $term['term_id'];
            }

            $p_terms = array_merge([0=>['id'=>0,'title_show'=>'顶级菜单']], $p_terms);
            // 使用FormBuilder快速建立表单页面。
            $builder = builder('Form');
            $builder->setMetaTitle($title.'分类');  // 设置页面标题
            if (!empty($tab_obj)) {//构建tab
             $builder->setTabNav($tab_obj['tab_list'],$tab_obj['current']);  // 设置页面Tab导航
            }
            return $builder->addFormItem('term_id', 'hidden', 'ID', 'ID')
                    ->addFormItem('name', 'text', '分类名称', '分类名称','','require')
                    ->addFormItem('slug', 'text', '分类别名', '分类别名','','require')
                    ->addFormItem('taxonomy', 'select', '分类类型', '选择一个分类法',$this->termTaxonomy)
                    ->addFormItem('pid', 'multilayer_select', '上级分类', '上级分类',$p_terms)
                    ->addFormItem('limit', 'number', '分页条数', '设置前台的分页条数')
                    ->addFormItem('seo_title', 'text', 'SEO标题', '留空自动设置为分类名称')
                    ->addFormItem('seo_keywords', 'text', 'SEO关键字', 'SEO关键字')
                    ->addFormItem('seo_description', 'textarea', '描述', '同时也作为SEO描述')
                    ->setFormData($info)
                    //->setAjaxSubmit(false)
                    //->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
    }


}