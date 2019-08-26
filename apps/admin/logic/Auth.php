<?php
// 授权管理控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2019 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\logic;

use app\admin\model\AuthRule as AuthRuleModel;
use app\admin\model\AuthGroup as AuthGroupModel;
use app\admin\model\AuthGroupAccess as AuthGroupAccessModel;
use app\admin\model\AdminUser as AdminUserModel;

use eacoo\Tree;
use think\Cookie;

class Auth extends AdminLogic {

    protected $authRuleModel;
    protected $authGroupModel;
    protected $userModel;

    protected function initialize()
    {
        parent::initialize();

        $this->authRuleModel  = new AuthRuleModel;
        $this->authGroupModel = new AuthGroupModel;
        $this->userModel      = new AdminUserModel;

    }
    
    /**
     * 后台菜单管理(规则)
     * @return [type] [description]
     */
    public function getAdminMenu($depend_flag=null){
        $map = [];
        if ($depend_flag!='all' && $depend_flag) {
            $map['depend_flag']=$depend_flag;
        }
        $menus = model('AuthRule')->getList($map,true,'sort asc,id asc');

        $menus = collection($menus)->toArray();
        $tree_obj = new Tree;
        return $menus = $tree_obj->toFormatTree($menus,'title');
    }

    /**
     * 获取构建器使用的tablist
     * @return [type] [description]
     * @date   2018-02-07
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getTabList()
    {             
        $module_list = logic('Module')->getModules();
        unset($module_list['home']);
        $tab_list = ['all'=>['title'=>'全部','href'=>url('index')]];
        foreach ($module_list as $key => $row) {
            $tab_list[$key] = ['title'=>$row,'href'=>url('index',['depend_flag'=>$key])];
        }
        return $tab_list;
    }

    /**
     * 获取表单的菜单关联组
     * @param  integer $depend_type [description]
     * @return [type] [description]
     * @date   2018-02-20
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getDependFlags($depend_type=0)
    {
        if (!$depend_type) {
            return false;       
        }
        switch ($depend_type) {
            case 1://模块
                $data_list = logic('Module')->getModules();
                break;
            case 2://插件
                $data_list = model('admin/Plugins')->where('status',1)->column('title','name');
                break;
            case 3://主题
                $data_list = model('admin/Theme')->where('status',1)->column('title','name');
                break;
            default:
                # code...
                break;
        }
        return $data_list;
    }

    /**
     * 获取后台菜单表单的html
     * @return [type] [description]
     * @date   2018-02-20
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getFormMenuHtml()
    {
        $html = <<<EOF
<script type="text/javascript">
 $(function () {
    var depend_type = $('#depend_type').find("option:selected").val();
    var depend_flag = $('#depend_flag').find("option:selected").val();
    switch_select_dependflag_function(depend_type,depend_flag);
    $('#depend_type').on('change',function(){
        var depend_type = $('#depend_type').find("option:selected").val();
        switch_select_dependflag_function(depend_type,0);
    });
})
//事件方法
function switch_select_dependflag_function(type,depend_flag){
    $.get(url("admin/Menu/getSelectDependFlags"),{depend_type:type},function(result){
        if(type == 1){//模块
            var oname = '模块';
        } else if(type == 2){//插件
            var oname = '插件';
        } else{//主题
            var oname = '主题';
        }
        var append_html='<option value="0">请选择一个'+oname+'</option>';
        $.each(result,function(name,value) {
            var selected='';
            if(depend_flag==name){
                var selected = 'selected';
            }
            append_html+='<option value="'+name+'" '+selected+'>'+value+'（'+name+'）</option>';
        });
        $('#depend_flag').html(append_html);
      });
    
}
</script>
EOF;
    return $html;
    }

    /**
     * 标记菜单
     * @return [type] [description]
     * @date   2017-08-27
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function markerMenu(){
        //是否标记菜单：0否，1是
        $model='AuthRule';
        if (IS_POST) {

            cache('admin_sidebar_menus_'.$this->currentUser['uid'],null);//清空后台菜单缓存

            $ids    = input('post.ids/a');
            $status = input('param.status');
            if (empty($ids)) {
                $this->error('请选择要操作的数据');
            }

            $map['id'] = ['in',$ids];
            switch ($status) {
                case 0 :  
                    $data = ['is_menu' => 0];
                    $this->editRow(
                        $model,
                        $data,
                        $map,
                        array('success'=>'标记成功','error'=>'标记失败')
                    );
                    break;
                case 1 :  
                    $data = ['is_menu' => 1];
                    $this->editRow(
                        $model,
                        $data,
                        $map,
                        ['success'=>'标记成功','error'=>'标记失败']
                    );
                    break;
                default :
                    $this->error('参数错误');
                    break;
            }
        }
    }

    /**
     * 移动菜单所属模块
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function moveModule() {
        if (IS_POST) {
            $ids       = input('param.ids');
            $to_module = input('param.to_module');
            if ($to_module) {
                $map['id'] = ['in',$ids];
                $data      = ['depend_flag' => $to_module];
                $this->editRow('auth_rule', $data, $map, array('success'=>'移动成功','error'=>'移动失败',U('index')));

            } else {
                $this->error('请选择目标模块');
            }
        }
    }

    /**
     * 移动菜单位置
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function moveMenusPosition($ids,$to_pid) {

        cache('admin_sidebar_menus_'.$this->currentUser['uid'],null);
        $map['id'] = ['in',$ids];
        $data = array('pid' => $to_pid);
        $result = model('auth_rule')->editRow($data, $map);
        return $result;

    }

    /**
     * 构建列表移动配置分组按钮
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function moveMenuHtml(){

        //移动菜单位置
        $menus = db('auth_rule')->select();
        $tree_obj = new Tree;
        $menus = $tree_obj->toFormatTree($menus,'title');
        $menu_options = array_merge(array(0=>array('id'=>0,'title_show'=>'顶级菜单')), $menus);
        $menu_options_str='';
        foreach ($menu_options as $key => $option) {
                if(is_array($option)){
                    $menu_options_str.='<option value="'.$option['id'].'">'.$option['title_show'].'</option>';
                }else{
                    $menu_options_str.='<option value="'.$option['id'].'">'.$option.'</option>';
                }
        }
        $move_position_url = url('moveMenusPosition');
        return <<<EOF
        <div class="modal fade mt100" id="movemenuPositionModal">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
                        <p class="modal-title">移动至</p>
                    </div>
                    <div class="modal-body">
                        <form action="{$move_position_url}" method="post" class="form-movemenu">
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
            function move_menuposition(){
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
     * 检测规则权限
     * @return [type] [description]
     * @date   2017-10-17
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function checkAuth($rule_name = '' ,$depend_type = 1, $depend_flag ='')
     {
        if (is_administrator($this->adminUid)) return true;
        $auth = new \org\util\Auth();
        $name = !empty($rule_name) ? $rule_name : $this->urlRule;
        //执行check的模式
        $mode = 'url';
        //'or' 表示满足任一条规则即通过验证;
        //'and'则表示需满足所有规则才能通过验证
        $relation = 'and';

        if(!$auth->check($name, $this->adminUid, 1, $mode, $relation) && $name!='admin/dashboard/index'){//允许进入仪表盘
            return false;
        }
        return true;
     }
}