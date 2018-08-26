<?php
// 前台导航
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\logic;

use app\common\model\Nav as NavModel;
use eacoo\Tree;

class Navigation extends AdminLogic {

    protected $navModel;

    protected function initialize()
    {
        parent::initialize();
        $this->navModel = new NavModel;
    }
    
    /**
     * 前台导航菜单管理
     * @return [type] [description]
     * @date   2018-02-12
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getNavMenus(){
        $menus = NavModel::all(function($query)
        {
            $query->field(true)->order('sort asc');
        });
        if (!empty($menus)) {
            $menus = collection($menus)->toArray();
            $tree_obj = new Tree;
            return $menus = $tree_obj->toFormatTree($menus,'title');
        }
        return false;
    }

    /**
     * 构建列表移动配置分组按钮
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function moveMenuHtml(){

        //移动菜单位置
        $menus = $this->getNavMenus();
        $menu_options = [];
        if (!empty($menus)) $menu_options = array_merge([0=>['id'=>0,'title_show'=>'顶级菜单']], $menus);
        $menu_options_str='';
        foreach ($menu_options as $key => $option) {
                if(is_array($option)){
                    $menu_options_str.='<option value="'.$option['id'].'">'.$option['title_show'].'</option>';
                }else{
                    $menu_options_str.='<option value="'.$option['id'].'">'.$option.'</option>';
                }
        }
        $move_url = url('moveMenusPosition');
        return <<<EOF
        <div class="modal fade mt100" id="movemenuPositionModal">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
                        <p class="modal-title">移动至</p>
                    </div>
                    <div class="modal-body">
                        <form action="{$move_url}" method="post" class="form-movemenu">
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
            function move_menu_position(){
                var ids = '';
                $('input[name="ids[]"]:checked').each(function(){
                   ids += ',' + $(this).val();
                });
                if(ids != ''){
                    ids = ids.substr(1);
                    $('input[name="ids"]').val(ids);
                    $('.modal-title').html('移动选中的菜单至：');
                    $('#movemenuPositionModal').modal('show', 'fit')
                }else{
                    updateAlert('请选择需要移动的菜单', 'warning');
                }
            }
        </script>
EOF;
    }

    /**
     * 移动菜单位置
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function moveMenusPosition($ids,$to_pid) {

        cache('front_header_navs',null);//清空前台导航缓存
        cache('front_my_navs',null);//清空前台我的缓存
        $map['id'] = ['in',$ids];
        $data = array('pid' => $to_pid);
        $result = model('nav')->editRow($data, $map);
        return $result;

    }

    /**
     * 添加前台导航菜单
     * @param  array $data 菜单数据
     * @param  integer $pid 父级ID
     * @date   2017-09-15
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function addNavigationMenus($depend_type = '', $depend_flag = '', $data = [], $pid = 0)
    {
        if (!empty($data) && is_array($data)) {
            $navModel = new NavModel;
            //头部导航
            if (!empty($data['header'])) {
                $header_menus = $data['header'];
                foreach ($header_menus as $key => $menu) {
                    $menu['position'] = 'header';
                    $menu['pid'] = $pid;
                    $menu['depend_type'] = $depend_type;
                    $menu['depend_flag'] = $depend_flag;
                    $navModel->allowField(true)->isUpdate(false)->data($menu)->save();
                    
                    //添加子菜单
                    if (!empty($menu['sub_menu'])) {
                        $this->addNavigationMenus($depend_type,$depend_flag,['header'=>$menu['sub_menu']], $navModel->id);
                    }
                }
                cache('front_header_navs',null);//清空前台导航缓存
            }
            
            //个人中心导航
            if (!empty($data['my'])) {
                $my_menus = $data['my'];
                foreach ($my_menus as $key => $menu) {
                    $menu['position'] = 'my';
                    $menu['pid'] = $pid;
                    $menu['depend_type'] = $depend_type;
                    $menu['depend_flag'] = $depend_flag;
                    $navModel->allowField(true)->isUpdate(false)->data($menu)->save();
                    
                    //添加子菜单
                    if (!empty($menu['sub_menu'])) {
                        $this->addNavigationMenus($depend_type,$depend_flag,['my'=>$menu['sub_menu']], $navModel->id);
                    }
                }
                cache('front_my_navs',null);//清空前台我的缓存
            }

            return true;
        }
        return false;
    }

    /**
     * 设置导航菜单
     * @param  string $flag_name [description]
     * @param  boolean $delete [description]
     * @return [type] [description]
     * @date   2017-11-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function setNavigationMenus($depend_type = '', $depend_flag = '', $status='')
    {
        try {
            if (!$depend_type || !$depend_flag || !$status) {
                throw new \Exception("参数不合法", 1);
            }
            $state = 0;
            switch ($status) {
                case 'resume'://启用
                    $state = 1;
                    break;
                case 'forbid'://禁用
                    $state = 0;
                    break;
                case 'delete'://删除
                    $state = 0;
                    break;
                default:
                    # code...
                    break;
            }

            $res = false;
            $map = [
                'depend_type'=>$depend_type,
                'depend_flag'=>$depend_flag
            ];
            if ($status!='delete') {
                $res = NavModel::where($map)->update(['status'=>$state]);
            } elseif($status=='delete') {
                $res = NavModel::where($map)->delete();
            }
            
            if (false === $res) {
                return false;
            } else{
                cache('front_header_navs',null);//清空前台导航缓存
                cache('front_my_navs',null);//清空前台我的缓存
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
        
    }

}