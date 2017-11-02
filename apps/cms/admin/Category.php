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
use app\admin\controller\Terms as TermsController;

use app\common\model\Terms;
use app\common\model\TermRelationships;

use app\admin\builder\Builder;

class Category extends Admin {

    protected $termsModel;
    protected $tab_list;

    function _initialize()
    {
        parent::_initialize();
        $this->termsModel = new Terms();
        //实例化terms
        $this->optCategory = $this->termsModel->where(['taxonomy'=>'post_category'])->column('name','term_id');
        $this->optTags = $this->termsModel->where(['taxonomy'=>'post_tag'])->column('name','term_id');

        $this->tab_list= [
            'index'         =>['title'=>'文章管理','href'=>url('Posts/index')],
            'post_category' =>['title'=>'文章分类','href'=>url('index')],
            'post_tag'      =>['title'=>'标签','href'=>url('index',['taxonomy'=>'post_tag'])],
        ];
    }
    
    
    /**
     * 分类管理
     * @param  string $taxonomy 分类法
     * @param  string $fromTable 关联表
     * @return [type] [description]
     * @date   2017-09-29
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index($taxonomy='post_category',$fromTable='posts'){
        $tab_obj=[
            'tab_list'=>$this->tab_list,
            'current'=>$taxonomy
            ];
        $controller = new TermsController;
        $controller->index($taxonomy,$fromTable,$tab_obj,url('edit',['id'=>'__data_id__','taxonomy'=>$taxonomy]));
        //action('admin/Terms/index',[$taxonomy,$fromTable,$tab_obj,url('termEdit',['id'=>'__data_id__','taxonomy'=>$taxonomy])],'Controller');

    }   

    /**
     * 分类编辑
     * @param  integer $id [description]
     * @param  string $taxonomy [description]
     * @return [type] [description]
     * @date   2017-09-29
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function edit($id=0,$taxonomy='post_category'){
        $id      = intval($id);
        $tab_obj = [
            'tab_list'=>$this->tab_list,
            'current'=>$taxonomy
            ];
        $controller = new TermsController;
        $controller->edit($id,$taxonomy,$tab_obj);
        //action('admin/Terms/edit',array($id,$taxonomy,$tab_obj),'Controller');

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
                        $res = TermRelationships::where($map)->count();
                        if ($res>0) {
                            TermRelationships::where($map)->update($data);
                        } else{
                            $data = [
                                'object_id' => $id,
                                'table'     => 'posts',
                                'term_id' => $to_cid
                            ];
                            TermRelationships::create($data);
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

    
    /**
     * 构建列表移动分类按钮
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function moveCategoryHtml($optCategory,$cid){
            //构造移动文档的目标分类列表
            $options = '';
            foreach ($optCategory as $key => $val) {
                $options .= '<option value="'.$key.'">'.$val.'</option>';
            }
            //文档移动POST地址
            $move_url = url('category/moveCategory');

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

}