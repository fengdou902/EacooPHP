<?php
// 分类控制器      
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\cms\admin;
use app\admin\controller\Admin;

use app\common\model\Terms as TermsModel;
use app\common\model\TermRelationships as TermRelationshipsModel;

use eacoo\Tree;

class Category extends Admin {

    protected $termsModel;

    function _initialize()
    {
        parent::_initialize();
        $this->termsModel = new TermsModel();

    }
    
    /**
     * 分类管理
     * @param  string $taxonomy 分类法
     * @return [type] [description]
     * @date   2017-09-29
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index($taxonomy='post_category'){
        $map =[
            'taxonomy'=>$taxonomy
        ];
        
        list($data_list,$total) = $this->termsModel->search('title') //添加搜索查询
                                ->getListByPage($map,true,'sort desc,create_time desc');
        if (!empty($data_list)) {
            foreach ($data_list as $key => &$row) {
                $row['object_count'] = logic('common/Terms')->termRelationCount($row['term_id'],'posts');
            }
        }
        return  builder('List')
                ->setMetaTitle('分类管理') // 设置页面标题
                ->addTopButton('addnew',['href'=>url('edit',['taxonomy'=>$taxonomy])])  // 添加新增按钮
                ->addTopButton('resume')  // 添加启用按钮
                ->addTopButton('forbid')  // 添加禁用按钮
                ->addTopButton('recycle') //添加回收按钮
                ->addTopButton('delete') //添加回收按钮
                ->setTabNav(logic('cms/Base')->getBuilderTab(),$taxonomy)  // 设置页面Tab导航
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
                ->setListPage($total) // 数据列表分页
                ->addRightButton('edit',['href'=>url('edit',['term_id'=>'__data_id__','taxonomy'=>$taxonomy])])// 添加编辑按钮
                ->addRightButton('recycle')// 添加删除按钮
                ->fetch();

    }   

    /**
     * 分类编辑
     * @param  integer $id [description]
     * @param  string $taxonomy [description]
     * @return [type] [description]
     * @date   2017-09-29
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function edit($term_id=0,$taxonomy='post_category'){

        $title = $term_id>0 ? "编辑" : "新增";
        if (IS_POST) {
            // 提交数据
            $data = $this->request->param();
            // seo标题
            if ($data['seo_title'] === '') {
                $data['seo_title'] = $data['name'];
            }
            $data['taxonomy'] = $taxonomy;
            //验证数据
            $this->validateData($data,
                                [
                                    ['name','require|chsDash','分类名称不能为空|分类名称只能是汉字和字母'],
                                    ['taxonomy','require|alphaDash','描述只能是汉字字母数字|分类法名称只能是字母和数字，下划线符合']
                                ]);

            $result = $this->termsModel->editData($data);
            if ($result) {
                $this->success($title.'成功', url('index',['taxonomy'=>$taxonomy]));
            } else {
                $this->error($this->termsModel->getError());
            }

        } else {
            $info = ['sort'=>99,'status'=>1];
            if ($term_id>0) {
                $info = TermsModel::get($term_id);
            }
            $p_terms = TermsModel::where(['taxonomy'=>$taxonomy])->select();
            if ($p_terms) {
                $p_terms = collection($p_terms)->toArray();
                $tree_obj = new Tree;
                $p_terms = $tree_obj->toFormatTree($p_terms,'name','term_id');
            }

            foreach ($p_terms as $key => $term) {
                $p_terms[$key]['id']= $term['term_id'];
            }
            $termTaxonomy = config('term_taxonomy');//获取所有分类法
            $p_terms = array_merge([0=>['id'=>0,'title_show'=>'顶级菜单']], $p_terms);

            return builder('Form')
                    ->setMetaTitle($title.'分类')  // 设置页面标题
                    ->setTabNav(logic('cms/Base')->getBuilderTab(),$taxonomy)  // 设置页面Tab导航
                    ->addFormItem('term_id', 'hidden', 'ID', 'ID')
                    ->addFormItem('name', 'text', '分类名称', '分类名称','','require')
                    ->addFormItem('slug', 'text', '分类别名', '分类别名','','require')
                    ->addFormItem('taxonomy', 'select', '分类类型', '选择一个分类法',$termTaxonomy)
                    ->addFormItem('pid', 'multilayer_select', '上级分类', '上级分类',$p_terms)
                    ->addFormItem('seo_title', 'text', 'SEO标题', '留空自动设置为分类名称')
                    ->addFormItem('seo_keywords', 'text', 'SEO关键字', 'SEO关键字')
                    ->addFormItem('seo_description', 'textarea', '描述（SEO）', '同时也作为SEO描述')
                    ->addFormItem('sort', 'number', '排序', '按照数值大小的倒叙进行排序，数值越小越靠前')
                    ->addFormItem('status', 'radio', '状态', '',[1=>'正常',0=>'禁用'])
                    ->setFormData($info)
                    //->setAjaxSubmit(false)
                    //->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
    }

    /**
     * 移动分类
     */
    public function moveCategory() {
        if (IS_POST) {
            $ids      = input('param.ids');
            $from_cid = input('param.from_cid');
            $to_cid   = input('param.to_cid');
            if ($from_cid === $to_cid) {
                $this->error('存在目标分类与当前分类相同');
            }
            if ($to_cid) {
                $map = [
                    'object_id'=>['in',$ids],
                ];
                $ids = explode(',', $ids);
                if (!empty($ids) && is_array($ids)) {
                    $data = ['term_id' => $to_cid];
                    foreach ($ids as $key => $id) {
                        $map = [
                            'object_id' => $id,
                            'table'     => 'posts'
                        ];
                        $res = TermRelationshipsModel::where($map)->count();
                        if ($res>0) {
                            TermRelationshipsModel::where($map)->update($data);
                        } else{
                            $data = [
                                'object_id' => $id,
                                'table'     => 'posts',
                                'term_id' => $to_cid
                            ];
                            TermRelationshipsModel::create($data);
                        }
                        unset($map);
                    }
                    $this->success('移动成功');
                }
                
                $this->error('移动失败');
            } else {
                $this->error('请选择目标分类');
            }
        }
    }

}