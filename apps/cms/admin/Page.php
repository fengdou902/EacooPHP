<?php
// 页面控制器      
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
use app\admin\controller\Terms as TermsController;

use app\cms\model\Posts as PostsModel;
use app\common\model\Terms;
use app\common\model\TermRelationships;

use app\admin\builder\Builder;

class Page extends Admin {

    protected $postModel;
    protected $termsModel;
    protected $tab_list;

    function _initialize()
    {
        parent::_initialize();
        $this->postModel  = new PostsModel();
        $this->termsModel = new Terms();
        //实例化terms
        $this->optCategory = $this->termsModel->where(['taxonomy'=>'post_category'])->column('name','term_id');
        $this->optTags = $this->termsModel->where(['taxonomy'=>'post_tag'])->column('name','term_id');

        $this->tab_list= [
            'index'         =>['title'=>'文章管理','href'=>url('index')],
            'post_category' =>['title'=>'文章分类','href'=>url('Category/index')],
            'post_tag'      =>['title'=>'标签','href'=>url('Category/index',['taxonomy'=>'post_tag'])],
        ];
    }
    
    /**
     * 页面列表
     * @return [type] [description]
     * @date   2017-09-28
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index(){
        // 搜索
        $keyword = input('param.keyword');
        if ($keyword) {
            $this->postModel->where('id|title','like','%'.$keyword.'%');
        }
        // 获取所有页面列席
        $map = [
            'status'=>1,
            'type'=>'page'
        ];
        list($data_list,$totalCount) = $this->postModel->getListByPage($map,'create_time desc','*',20);
        //遍历posts遍历的数据
        foreach($data_list as $k=>$page){
            $data_list[$k]['author']=get_user_info($page['author_id'])['nickname'];//获取用户名
        }

        Builder::run('List')
                ->setMetaTitle('页面管理') // 设置页面标题
                ->addTopButton('addnew')  // 添加新增按钮
                ->addTopButton('recycle',array('model'=>'posts'))  // 添加删除按钮
                ->setSearch('搜索页面', url('page'))
                ->keyListItem('title', '标题')
                ->keyListItem('views','浏览量')
                ->keyListItem('author','作者','author')
                ->keyListItem('publish_time','发布时间')
                ->keyListItem('status', '状态', 'status')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListDataKey('id')
                ->setListData($data_list)    // 数据列表
                ->setListPage($totalCount,20) // 数据列表分页
                ->addRightButton('edit',array('href'=>url('edit',array('id'=>'__data_id__'))))  // 添加编辑按钮
                ->addRightButton('recycle')        // 添加删除按钮
               ->fetch();
    }

    /**
     * 编辑页面
     * @author 
     */
    public function edit($id=0) {
        $title = $id ? "编辑":"新增";
        if (IS_POST) {
            $data =input('post.');
            if(!empty($data)){
                $id   =isset($data['id']) && $data['id']>0 ? $data['id']:false;
                $data['type']='page';
                if ($this->postModel->editData($data,$id)) {
                    $this->success($title.'成功', url('index'));
                } else {
                    $this->error($this->postModel->getError());
                }

            }
        } else {
            if ($id!=0) {
               $info = PostsModel::get($id);
            } else{
                $info = [];
            }
            $authors = db('users')->where(['allow_admin'=>1])->column('nickname','uid');
            Builder::run('Form')
                    ->setMetaTitle($title.'页面')
                    ->addFormItem('id', 'hidden', '')
                    ->addFormItem('title', 'text', '标题')
                    ->addFormItem('content', 'wangeditor', '内容','',['width'=>'100%','height'=>'300px','config'=>'all'])
                    ->addFormItem('author_id', 'select2', '作者', '',$authors)
                    ->addFormItem('tags', 'tags', '标签', '')
                    ->addFormItem('seo_keywords', 'text', 'SEO关键字', '')
                    ->addFormItem('excerpt', 'text', 'SEO描述', '')
                    ->setFormData($info)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
    }

    /**
     * 设置一条或者多条数据的状态
     */
    public function setStatus($model = 'Posts',$script = false) {
        $ids    = input('request.ids');
        $status = input('request.status');
        if (empty($ids)) {
            $this->error('请选择要操作的数据');
        }
        $map['id'] = ['in',$ids];
        switch ($status) {
            case 'delete' :  // 删除条目
                $map['status'] = -1;
                $exist = $this->postModel->get($map);
                if ($exist) {
                    $result = $this->postModel->delete($ids);
                } else {
                    $result = true;
                }
                if ($result) {
                    foreach ($ids as $key => $id) {
                        $term_id = get_term_info($id,'term_id')['term_id'];
                        delete_post_term($id,$term_id);//删除分类
                    }       
                    $this->success('彻底删除成功');

                } else {
                    $this->error('删除失败');
                }
                break;
            default :
                parent::setStatus($model);
                break;
        }
    }
}