<?php
// 文章控制器      
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
use app\cms\model\Postmeta as PostmetaModel;
use app\common\model\Terms;
use app\common\model\TermRelationships;
use app\cms\admin\Category;

use app\admin\builder\Builder;

class Posts extends Admin {

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
     * 文章管理
     * @param  integer $term_id [description]
     * @return [type] [description]
     * @date   2017-09-29
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index($term_id=0){

        // 搜索
        $keyword = input('param.keyword');
        if ($keyword) {
            $this->postModel->where('id|title','like','%'.$keyword.'%');
        }

        if($term_id>0){
            $post_ids = TermRelationships::where(['term_id'=>$term_id,'table'=>'posts'])->select();
            if(count($post_ids)){
                $post_ids   = array_column($post_ids,'object_id');
                //$post_ids = array_merge(array($post_ids),$post_ids);
                $map['id']  = ['in',$post_ids];
            } else{
                $map['id']  = 0;
            }
        }
        // 获取所有文章
        $map['status'] = 1; // 禁用和正常状态
        $map['type']='post';
        $data_list = $this->postModel->where($map)->field(true)->order('create_time desc')->paginate(20);
        //遍历posts遍历的数据
        foreach($data_list as &$row){
            $row['category_name'] = get_term_info($row['id'],'name')['name'] ? :'暂无';//获得term名称
            $row['author']        = get_user_info($row['author_id'])['nickname'];//获取用户名
        }

        $optCategory = [0=>'全部分类']+$this->optCategory;
        unset($val);
        //移动按钮属性
        $move_attr['title'] = '<i class="fa fa-exchange"></i> 移动分类';
        $move_attr['class'] = 'btn btn-info btn-sm';
        $move_attr['onclick'] = 'move()';
        $extra_html = Category::moveCategoryHtml($optCategory,$term_id);//添加移动按钮html
        //置顶按钮属性
        $top_attr['title'] = '<i class="fa fa-long-arrow-up"></i> 置顶';
        $top_attr['class'] = 'btn btn-info btn-sm';
        $top_attr['onclick'] = '';
        //$extraTop_html=$this->moveCategoryHtml($optCategory,$term_id);//添加移动按钮html
        //推荐按钮属性
        $recommended_attr['title'] = '<i class="fa fa-thumbs-o-up"></i> 推荐';
        $recommended_attr['class'] = 'btn btn-info btn-sm';
        $recommended_attr['onclick'] = '';
        //$extraTop_html=$this->moveCategoryHtml($optCategory,$term_id);//添加移动按钮html

        Builder::run('List')
            ->setMetaTitle('文章管理') // 设置页面标题
            ->setTabNav($this->tab_list,'index') // 设置页面Tab导航
            ->addTopButton('addnew',['data-pjax'=>'true'])  // 添加新增按钮
            ->addTopButton('resume',array('model'=>'posts'))  // 添加启用按钮
            ->addTopButton('forbid',array('model'=>'posts'))  // 添加禁用按钮
            ->addTopButton('recycle',array('model'=>'posts')) //添加回收按钮
            ->addTopButton('self', $move_attr) //添加移动按钮
            //->addTopButton('self', $top_attr)
            //->addTopButton('self', $recommended_attr)
            ->addSelect('分类','term_id',$optCategory)//添加分类筛选
            //->addSelect('作者','author_id',array_merge(array(array('id'=>0,'value'=>'所有作者')),$optCategory))//添加分类筛选
            ->setSearch('输入关键字','')
            ->keyListItem('id', 'ID')
            ->keyListItem('img','缩略图','picture')
            ->keyListItem('title', '标题','link',['link'=>url('edit',['id'=>'__data_id__'])])
            ->keyListItem('category_name','分类')
            ->keyListItem('views','浏览量')
            ->keyListItem('author','作者','author')
            ->keyListItem('publish_time','发布时间')
            ->keyListItem('istop', '置顶', 'status')
            ->keyListItem('sort', '排序')
            ->keyListItem('status', '状态', 'status')
            ->keyListItem('right_button', '操作', 'btn')
            ->setListData($data_list)    // 数据列表
            ->setListPage($data_list->render()) // 数据列表分页
            ->setExtraHtml($extra_html)
            ->addRightButton('edit')->addRightButton('recycle')
            ->fetch();
    }

    /**
     * 新增或编辑
     * @param  integer $id [description]
     * @return [type] [description]
     * @date   2017-10-15
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function edit($id = 0){

        $title = $id>0 ? "编辑":"新增"; 
        
        //修改
        if(IS_POST){
            $data = input('post.');
    
            $data['author_id']    = is_login();
            $data['type']         = 'post';
            $data['content']      = htmlspecialchars_decode(input('post.content'));
            //$data['fields']     =input('fields');
            
            //验证数据
            $this->validateData($data,'Post.edit');
            $id   =isset($data['id']) && $data['id']>0 ? $data['id']:false;
            $result = $this->postModel->editData($data,$id);
            if($result){
                $postmeta = input('post.postmeta/a');
                if (!empty($postmeta)) {
                    $post_meta_model = new PostmetaModel;
                    $meta_keys = $post_meta_model->where('post_id',$id)->column('meta_key');
                     /* 提交过来的 跟数据库中比较 不存在 删除*/
                     $del_metas = [];
                     $postmeta_keys = array_column($postmeta,'meta_key');
                     if (!empty($meta_keys)) {
                         foreach ($meta_keys as $key => $value) {
                            if(!in_array($value,$postmeta_keys)) $del_metas[] = $value; 
                        }
                     }
                     //这是元数据
                    foreach ($postmeta as $key => $val) {
                        if (!empty($val['meta_value'])) {
                            $post_meta_model->setMeta($id,$val['meta_key'],$val['meta_value']);
                        }
                        
                    }
                    //删除不存在的
                    foreach ($del_metas as $key => $value) {
                        $post_meta_model->deleteMeta($id,$value);
                    }
                }
                update_post_term($id,input('post.category_id',false));
                $this ->success($title.'成功','');
            } else{
                $this ->error($this->postModel->getError());
            }

        } else{
            $this->assign('page_config',['disable_panel'=>true]);
            $this->assign('meta_title',$title.'文章');

            $info = [
                'content'=>'',
                'img'=>''
            ];
            if ($id>0) {
                $info = PostsModel::get($id);
                $this->assign('category_id',get_the_category($id));
                $this->assign('tag_ids',get_the_category($id));
                $this->assign('meta_list',PostmetaModel::getMetas($id));
            } else {
                $this->assign('category_id',0);
                $this->assign('tag_ids',0);
            }
            $this->assign('info',$info);
            

            $this->assign('form_url',url('edit',['id'=>$id]));

            $this->assign('post_category',$this->optCategory);
            $this->assign('post_tags',$this->optTags);
            $this->assign('tag_id',1);//测试
            return $this->fetch();
        }
        
    }

    /**
     * 回收站页面
     * @author 心云间、凝听 <981248356@qq.com> 
     */
    function trash(){
        $this->meta_title ='回收站';
        // 获取所有文章
        $map['status'] = ['in',[-1,0]]; // 禁用和正常状态
        list($data_list,$totalCount) = $this->postModel->getListByPage($map,'create_time desc','*',20);
        //遍历posts遍历的数据
        foreach($data_list as $k=>$data){
            $data_list[$k]['category_name'] = get_term_info($data['id'],'name')['name'] ? : '暂无';//获得term名称
            $data_list[$k]['author'] = get_user_info($data['author_id'],'nickname')['nickname'];//获取用户名
        }

        Builder::run('List')
                ->setMetaTitle('回收站') // 设置页面标题
                ->addTopButton('restore',['model'=>'posts'])  // 添加启用按钮
                ->addTopButton('delete',['model'=>'posts'])  // 添加删除按钮
                ->setSearch('输入标题','')
                ->keyListItem('title', '标题')
                ->keyListItem('category_name','分类')
                ->keyListItem('type','类型','array',['post'=>'文章','page'=>'页面'])
                ->keyListItem('author','作者','author')
                ->keyListItem('publish_time','发布时间')
                ->keyListItem('status', '状态', 'status')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListDataKey('id')
                ->setListData($data_list)    // 数据列表
                ->setListPage($totalCount,20) // 数据列表分页
                ->addRightButton('edit',array('href'=>url('page/edit',['id'=>'__data_id__'])))  // 添加编辑按钮
                ->addRightButton('delete')  // 添加删除按钮
               ->fetch();
    }

    /**
     * 设置一条或者多条数据的状态
     */
    public function setStatus($model = 'Posts',$script = false) {
        $ids    = input('param.ids/a');
        $status = input('param.status');
        if (empty($ids)) {
            $this->error('请选择要操作的数据');
        }
        $map['id'] = ['in',$ids];
        switch ($status) {
            case 'delete' :  // 删除条目
                $map['status'] = -1;
                $exist = $this->postModel->where($map)->get();
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