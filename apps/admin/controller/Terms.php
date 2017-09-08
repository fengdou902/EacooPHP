<?php
// 分类控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.eacoo123.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use app\admin\builder\Builder;

use app\admin\model\Terms as TermsModel;

class Terms extends Admin {

    protected $termsModel;
    protected $termTaxonomy;

    function _initialize()
    {
        parent::_initialize();
        $this->termsModel = new TermsModel();
        $this->termTaxonomy = config('termTaxonomy');//获取所有分类法
    }

	//分类管理
    public function index($taxonomy='all',$fromTable,$tab_obj=[],$edit_U=NULL){
        // 获取所有用户
        $map['status'] = 1; // 发布和不发布状态
        if ($taxonomy!='all') {
            $map['taxonomy']=$taxonomy;
        }
        
        list($data_list,$page) = $this->termsModel->getListByPage($map,'sort desc,create_time desc','*',20);
        $builder = Builder::run('List');
        $builder->setMetaTitle('分类管理'); // 设置页面标题
        if (!empty($tab_obj)) {//构建tab
            $builder->setTabNav($tab_obj['tab_list'],$tab_obj['current']);  // 设置页面Tab导航
        }
        $addnew_href=null;
        if ($edit_U) {
           $addnew_href=['href'=>$edit_U];//新增按钮URL
        }
        $builder->addTopButton('addnew',$addnew_href)  // 添加新增按钮
                ->addTopButton('resume')  // 添加启用按钮
                ->addTopButton('forbid')  // 添加禁用按钮
                ->addTopButton('recycle') //添加回收按钮
                ->setSearch('输入分类名称', url('index'))
                ->keyListItem('term_id', 'ID')
                ->keyListItem('name', '名称','link',url('index',['term_id'=>'__data_id__']))//约定分类对象
                ->keyListItem('slug', '别名')
                ->keyListItem('parent', '父分类')
                ->keyListItem('seo_description', '描述')
                ->keyListItem('term_id', '对象数','callback', ['callback_fun'=>[ $this->termsModel,'term_relation_count'],'sub_param'=>[$fromTable]])
                ->keyListItem('status', '状态', 'status')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListDataKey('term_id')
                ->setListData($data_list)    // 数据列表
                ->setListPage($page) // 数据列表分页
                ->addRightButton('edit',$addnew_href)// 添加编辑按钮
                ->addRightButton('recycle')// 添加删除按钮
                ->fetch();

    }

	//分类编辑
    public function edit($term_id=0,$taxonomy,$tab_obj=[]){
        $title = $term_id>0 ? "编辑" : "新增";
        if (IS_POST) {
            // 提交数据
            $data = $this->input('post.');
            // seo标题
            if ($data['seo_title'] === '') {
                $data['seo_title']=$data['name'];
            }
            $data['taxonomy'] = $taxonomy;
            $term_id          = isset($data['term_id']) && $data['term_id']>0 ? $data['term_id']:false;
            //验证数据
            $this->validateData('Term',$data);

            $result           = $this->termsModel->editData($data,$term_id,'term_id');
            if ($result) {
                $this->success($title.'成功', url($tab_obj['current']));
            } else {
                $this->error($this->termsModel->getError());
            }

        } else {
            $info=[];
            if ($term_id!=0) {
                $info = $this->termsModel->get($term_id);
            }
            $p_terms = db('terms')->where(['taxonomy'=>$taxonomy])->select();

            $p_terms = model('common/Tree')->toFormatTree($p_terms,'name','term_id');

            foreach ($p_terms as $key => $term) {
                $p_terms[$key]['id']= $term['term_id'];
            }

            $p_terms = array_merge([0=>['id'=>0,'title_show'=>'顶级菜单']], $p_terms);
            // 使用FormBuilder快速建立表单页面。
            $builder = Builder::run('Form');
            $builder->setMetaTitle($title.'分类');  // 设置页面标题
            if (!empty($tab_obj)) {//构建tab
             $builder->setTabNav($tab_obj['tab_list'],$tab_obj['current']);  // 设置页面Tab导航
            }
            $builder->addFormItem('term_id', 'hidden', 'ID', 'ID')
                    ->addFormItem('name', 'text', '分类名称', '分类名称','','required')
                    ->addFormItem('slug', 'text', '分类别名', '分类别名','','required')
                    ->addFormItem('taxonomy', 'select', '分类类型', '选择一个分类法',$this->termTaxonomy)
                    ->addFormItem('pid', 'multilayer_select', '上级分类', '上级分类',$p_terms)
                    ->addFormItem('limit', 'number', '分页条数', '设置前台的分页条数')
                    ->addFormItem('seo_title', 'text', 'SEO标题', '留空自动设置为分类名称')
                    ->addFormItem('seo_keywords', 'text', 'SEO关键字', 'SEO关键字')
                    ->addFormItem('seo_description', 'textarea', '描述', '同时也作为SEO描述')
                    ->setFormData($info)
                    //->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
    }


}