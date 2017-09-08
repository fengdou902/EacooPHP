<?php
// cms控制器      
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.eacoo123.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\cms\admin;
use app\admin\controller\Admin;
use app\admin\controller\Terms as TermsController;

use app\cms\model\Posts as PostsModel;
use app\admin\model\Terms;

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
            'post_category' =>['title'=>'文章分类','href'=>url('postTerm')],
            'post_tag'      =>['title'=>'标签','href'=>url('postTerm',['taxonomy'=>'post_tag'])],
        ];
    }
    
    //文章管理
    public function index($term_id=0){

        // 搜索
        $keyword =$this->input('keyword');
        if ($keyword) {
            $this->config_model->where('id|title','like','%'.$keyword.'%');
        }

        if($term_id){
            $post_ids = db('term_relationships')->where(['term_id'=>$term_id,'table'=>'posts'])->select();
            if(count($post_ids)){
                $post_ids   = array_column($post_ids,'object_id');
                //$post_ids = array_merge(array($post_ids),$post_ids);
                $map['id']  = array('in',$post_ids);
            }
        }
        // 获取所有文章
        $map['status'] = ['egt', '0']; // 禁用和正常状态
        $map['type']='post';
        $data_list = $this->postModel->where($map)->field(true)->order('create_time desc')->paginate(20);
        //遍历posts遍历的数据
        foreach($data_list as $k=>$post){
            $data_list[$k]['category_name'] = get_term_info($post['id'],'name')['name']?:'暂无';//获得term名称
            $data_list[$k]['author']        = get_user_info($post['author_id'])['nickname'];//获取用户名
        }

        $optCategory = [0=>'全部分类']+$this->optCategory;
        unset($val);
        //移动按钮属性
        $move_attr['title'] = '<i class="fa fa-exchange"></i> 移动分类';
        $move_attr['class'] = 'btn btn-info btn-sm';
        $move_attr['onclick'] = 'move()';
        $extra_html = $this->moveCategoryHtml($optCategory,$term_id);//添加移动按钮html
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
            ->addTopButton('addnew')  // 添加新增按钮
            ->addTopButton('resume',array('model'=>'posts'))  // 添加启用按钮
            ->addTopButton('forbid',array('model'=>'posts'))  // 添加禁用按钮
            ->addTopButton('recycle',array('model'=>'posts')) //添加回收按钮
            ->addTopButton('self', $move_attr) //添加移动按钮
            ->addTopButton('self', $top_attr)
            ->addTopButton('self', $recommended_attr)
            ->addSelect('分类','term_id',$optCategory)//添加分类筛选
            //->addSelect('作者','author_id',array_merge(array(array('id'=>0,'value'=>'所有作者')),$optCategory))//添加分类筛选
            ->setSearch('搜索文章','')
            ->keyListItem('id', 'ID')
            ->keyListItem('title', '标题','link',url('edit',array('id'=>'__data_id__')))
            ->keyListItem('category_name','分类')
            ->keyListItem('views','浏览量')
            ->keyListItem('author','作者','author')
            ->keyListItem('create_time','发布时间', 'time')
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

    //编辑
    public function edit($id = 0){

        $title =$id ? "编辑":"新增"; 
        
        $this->assign('hide_panel',true);//隐藏base模板面板
        $this->assign('meta_title',$title.'文章');

        $post_obj=['content'=>'','img'=>''];
        if ($id>0) {
            $post_obj=$this->postModel->find($id);
            $this->assign('category_id',get_the_category($id));
            $this->assign('tag_ids',get_the_category($id));
        } else {
            $this->assign('category_id',0);
            $this->assign('tag_ids',0);
        }
        $this->assign('post_obj',$post_obj);

        $this->assign('form_url',url('edit',['id'=>$id]));
        //修改
        if(IS_POST){
            if ($id) {
               $data['id']=$id ? $id : $this->input('post.id');
            }
            $data['author_id']    = is_login();
            $data['type']         = 'post';
            $data['title']        = $this->input('post.title');
            $data['content']      = htmlspecialchars_decode($this->input('post.content'));
            $data['excerpt']      = $this->input('post.excerpt');
            $data['seo_keywords'] = $this->input('post.seo_keywords');
            $data['img']          = $this->input('post.img');
            $data['status']       = $this->input('post.status');
            $data['istop']        = $this->input('istop',false);
            $data['recommended']  = $this->input('post.recommended',false);
            $data['sort']         = $this->input('post.sort');
            //$data['fields']     =$this->input('fields');
            $data=$this->param;
            $id=$data['id'];
            $result = $this->postModel->editData($data,$id);
            if($result){
                update_post_term($id,$this->input('post.category_id',false));
                $this ->success($title.'成功');
            }else{
                $this ->error($this->postModel->getError());
            }

            return;
        }

        $this->assign('post_category',$this->optCategory);
        $this->assign('post_tags',$this->optTags);
        $this->assign('tag_id',1);//测试
        return $this->fetch();
    }

     /**
     * 分类
     * @param  int $id id
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function postTerm($taxonomy='post_category',$fromTable='posts'){
        $tab_obj=[
            'tab_list'=>$this->tab_list,
            'current'=>$taxonomy
            ];
        $controller = new TermsController;
        $controller->index($taxonomy,$fromTable,$tab_obj,url('termEdit',['id'=>'__data_id__','taxonomy'=>$taxonomy]));
        //action('admin/Terms/index',[$taxonomy,$fromTable,$tab_obj,url('termEdit',['id'=>'__data_id__','taxonomy'=>$taxonomy])],'Controller');

    }   
    /**
     * 文章分类编辑
     * @param  int $id id
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function termEdit($id=0,$taxonomy='post_category'){
        $id      = intval($id);
        $tab_obj = [
            'tab_list'=>$this->tab_list,
            'current'=>$taxonomy
            ];
        $controller = new TermsController;
        $controller->edit($id,$taxonomy,$tab_obj);
        //action('admin/Terms/edit',array($id,$taxonomy,$tab_obj),'Controller');

    }
    //页面
    public function page(){
        // 搜索
        $keyword =$this->input('keyword');
        if ($keyword) {
            $this->postModel->where('id|title','like','%'.$keyword.'%');
        }
        // 获取所有文章
        $map['status'] = ['egt', '0']; // 禁用和正常状态
        $map['type']   = 'page';
        list($data_list,$totalCount) = $this->postModel->getListByPage($map,'create_time desc','*',20);
        //遍历posts遍历的数据
        foreach($data_list as $k=>$page){
            $data_list[$k]['author']=get_user_info($page['author_id'])['nickname'];//获取用户名
        }

        Builder::run('List')
                ->setMetaTitle('页面管理') // 设置页面标题
                ->addTopButton('addnew',array('href'=>url('page_edit')))  // 添加新增按钮
                ->addTopButton('recycle',array('model'=>'posts'))  // 添加删除按钮
                ->setSearch('搜索页面', url('page'))
                ->keyListItem('title', '标题')
                ->keyListItem('views','浏览量')
                ->keyListItem('author','作者','author')
                ->keyListItem('create_time','发布时间', 'time')
                ->keyListItem('status', '状态', 'status')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListDataKey('id')
                ->setListData($data_list)    // 数据列表
                ->setListPage($totalCount,20) // 数据列表分页
                ->addRightButton('edit',array('href'=>url('page_edit',array('id'=>'__data_id__'))))  // 添加编辑按钮
                ->addRightButton('recycle')        // 添加删除按钮
               ->fetch();
    }
    /**
     * 编辑页面
     * @author 
     */
    public function page_edit($id=0) {
        $title=$id?"编辑":"新增";
        if (IS_POST) {
            $data = $this->postModel->create();
            if($data){
                $data['type']='page';
                $result = $this->postModel->editData($data);
                if ($result) {
                    $this->success($title.'成功', url('page'));
                } else {
                    $this->error($title.'失败');
                }
            }else{
                $this->error($this->postModel->getError());
            }
        }else {
            if ($id!=0) {
               $page_data =$this->postModel->find($id);
            } else{
                $page_data=[];
            }
            
            Builder::run('Form')
                    ->setMetaTitle($title.'页面')
                    ->addFormItem('id', 'hidden', '')
                    ->addFormItem('title', 'text', '标题')
                    ->addFormItem('content', 'ueditor', '内容','',['width'=>'100%','height'=>'200px','config'=>''])
                    ->addFormItem('author_id', 'number', '作者', '')
                    ->addFormItem('tags', 'tags', '标签', '')
                    ->addFormItem('seo_keywords', 'text', 'SEO关键字', '')
                    ->setFormData($page_data)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
    }

    /**
     * 移动分类
     */
    public function moveCategory() {
        if (IS_POST) {
            $ids      = $this->input('post.ids');
            $from_cid = $this->input('post.from_cid');
            $to_cid   = $this->input('post.to_cid');
            if ($from_cid === $to_cid) {
                $this->error('目标分类与当前分类相同');
            }
            if ($to_cid) {
                $map['object_id'] = ['in',$ids];
                $data             = ['term_id' => $to_cid];
                $this->editRow('term_relationships', $data, $map, array('success'=>'移动成功','error'=>'移动失败',url('index')));

            } else {
                $this->error('请选择目标分类');
            }
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
            $data_list[$k]['category_name']=get_term_info($data['id'],'name')['name']?:'暂无';//获得term名称
            $data_list[$k]['author']=get_user_info($data['author_id'],'nickname')['nickname'];//获取用户名
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
                ->keyListItem('create_time','发布时间', 'time')
                ->keyListItem('status', '状态', 'status')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListDataKey('id')
                ->setListData($data_list)    // 数据列表
                ->setListPage($totalCount,20) // 数据列表分页
                ->addRightButton('edit',array('href'=>url('page_edit',['id'=>'__data_id__'])))  // 添加编辑按钮
                ->addRightButton('delete')  // 添加删除按钮
               ->fetch();
    }
    
    /**
     * 构建列表移动分类按钮
     * @author 心云间、凝听 <981248356@qq.com>
     */
    protected function moveCategoryHtml($optCategory,$cid){
            //构造移动文档的目标分类列表
            $options = '';
            foreach ($optCategory as $key => $val) {
                $options .= '<option value="'.$key.'">'.$val.'</option>';
            }
            //文档移动POST地址
            $move_url = url('moveCategory');

            return <<<EOF
            <div class="modal fade mt100" id="moveModal">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
                            <p class="modal-title">移动至</p>
                        </div>
                        <div class="modal-body">
                            <form action="{$move_url}" method="post" class="form-move">
                                <div class="form-group">
                                    <select name="to_cid" class="form-control">{$options}</select>
                                </div>
                                <div class="form-group">
                                    <input type="hidden" name="ids">
                                    <input type="hidden" name="from_cid" value="{$cid}">
                                    <button class="btn btn-primary btn-block submit ajax-post" type="submit" target-form="form-move">确 定</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <script type="text/javascript">
                function move(){
                    var ids = '';
                    $('input[name="ids[]"]:checked').each(function(){
                       ids += ',' + $(this).val();
                    });
                    if(ids != ''){
                        ids = ids.substr(1);
                        $('input[name="ids"]').val(ids);
                        $('.modal-title').html('移动选中的的文章至：');
                        $('#moveModal').modal('show', 'fit')
                    }else{
                        updateAlert('请选择需要移动的文章', 'warning');
                    }
                }
            </script>
EOF;
    }
    /**
     * 设置一条或者多条数据的状态
     */
    public function setStatus($model = 'Posts',$script = false) {
        $ids    = $this->input('request.ids');
        $status = $this->input('request.status');
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