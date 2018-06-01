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
namespace app\cms\logic;

use app\common\model\Terms as TermsModel;
use app\common\model\TermRelationships as TermRelationshipsModel;

use eacoo\Tree;

class Category extends Base {

    function _initialize()
    {
        parent::_initialize();

    }

    /**
     * 获取分类
     * @return [type] [description]
     * @date   2017-10-17
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getCategories()
    {
        $map = [
            'taxonomy'=>'post_category',
        ];
        $data_list = db('terms')->where($map)->field('term_id,name,slug,pid')->limit(8)->select();
        return $data_list;
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
                                    <button class="btn btn-primary btn-block submit ajax-post" type="submit" target-form="form-move" data-dismiss="modal">确 定</button>
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
                        updateAlert('请选择需要移动的目标', 'warning');
                    }
                }
            </script>
EOF;
    }

    /**
     * 获取分类选择项
     * @param  string $taxonomy [description]
     * @return [type] [description]
     * @date   2018-02-22
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getOptTerms($taxonomy='post_category')
    {
        $data_list = model('common/Terms')->where(['taxonomy'=>$taxonomy])->column('name','term_id');
        return $data_list;
    }

}