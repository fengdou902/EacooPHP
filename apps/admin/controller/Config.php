<?php
//配置控制器
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\builder\Builder;
use app\common\model\Config as ConfigModel;

/**
 * 系统配置控制器
 */
class Config extends Admin {

    protected $configModel;

    function _initialize()
    {
        parent::_initialize();
        $this->configModel = new ConfigModel();
    }

    /**
     * 配置列表
     * @param $tab 配置分组ID
     */
    public function index($group = 1) {
        // 搜索
        $keyword = input('param.keyword');
        if ($keyword) {
            $this->configModel->where('id|name|title','like','%'.$keyword.'%');
        }

        // 获取所有配置
        $map['status'] = ['egt', '0'];  // 禁用和正常状态
        $map['group']  = ['eq', $group];
        //$map['type']  = ['neq', 'json'];

        list($data_list,$page) = $this->configModel->getListByPage($map,'sort asc,id asc','*',20);
        // 设置Tab导航数据列表
        $config_group_list = config('config_group_list');  // 获取配置分组

        foreach ($config_group_list as $key => $val) {
            $tab_list[$key]['title'] = $val;
            $tab_list[$key]['href']  = url('index', ['group' => $key]);
        }
        //移动按钮属性
        $move_attr['title']   = '<i class="fa fa-exchange"></i> 移动分组';
        $move_attr['class']   = 'btn btn-info btn-sm';
        $move_attr['onclick'] = 'move()';
        $extra_html=$this->moveGroupHtml($config_group_list,$group);//添加移动按钮html
        // 使用Builder快速建立列表页面。

        Builder::run('List')
                ->setMetaTitle('配置列表')  // 设置页面标题
                ->addTopButton('addnew',['href'=>url('edit',['group_id'=>$group])])   // 添加新增按钮
                //->addTopButton('resume',array('title'=>'显示'))   // 添加启用按钮
                //->addTopButton('forbid',array('title'=>'隐藏'))   // 添加禁用按钮
                ->addTopButton('delete')   // 添加删除按钮
                ->addTopButton('self', $move_attr) //添加移动按钮
                ->setSearch('请输入ID/配置名称/配置标题',url('index', array('group' => $group)))
                ->setTabNav($tab_list, $group)  // 设置页面Tab导航
                ->keyListItem('id', 'ID')
                ->keyListItem('name', '名称')
                ->keyListItem('title', '标题')
                ->keyListItem('type', '类型','type')
                //->keyListItem('remark', '说明')
                ->keyListItem('sub_group', '子分组')
                ->keyListItem('sort', '排序')
                ->keyListItem('status', '状态', 'status')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListData($data_list)     // 数据列表
                ->setListPage($page)  // 数据列表分页
                ->setExtraHtml($extra_html)
                ->addRightButton('edit')           // 添加编辑按钮
                ->addRightButton('delete')         // 添加删除按钮
                ->fetch();
    }

    /**
     * 编辑配置
     */
    public function edit($id=0){
        $title = $id>0 ? "编辑" : "新增";
        if ($id>0) {
            $Config_data = $this->configModel->where('id',$id)->find();
        } elseif ($id==0) {
            $Config_data['group'] = input('param.group_id');
        }
        if (IS_POST) {
            $data = input('post.');
            $id   = isset($data['id']) && $data['id']>0 ? $data['id']:false;
            $result = $this->validateData($data,
                                [
                                    ['group','require|number|>=:0','请选择配置分组|分组必须为数字|分组格式不正确'],
                                    ['sub_group','number|>=:0','子分组必须为数字|子分组格式不正确'],
                                    ['name','require|alphaDash','配置名称不能为空|配置名称只限字母、数字、下划线'],
                                    ['title','require|chsDash','标题不能为空|配置标题只限汉字、字母、数字和下划线_及破折号-'],
                                ]);
            if ($this->configModel->editData($data,$id)) {
                if ($id != 0) {
                    cache('db_'.$Config_data['name'].'_options',null);
                    cache('DB_CONFIG_DATA',null);
                }
                $this->success($title.'成功',url('index',['group'=>$data['group']]));
            } else {
                $this->error($this->configModel->getError());
            }

        } else {
            // 获取Builder表单类型转换成一维数组
            $switch_function_html=<<<EOF
<script type="text/javascript">
 $(function () {
        var type = $('#switch_function').find("option:selected").attr("data-type");
        switch_form_item_function(type);
    $('#switch_function').on('change',function(){
        var type = $('#switch_function').find("option:selected").attr("data-type");
        switch_form_item_function(type);
    });
})
//事件方法
function switch_form_item_function(type){
        type=parseInt(type);
    if(type == 1){
        $('.item_function').show();
        $('.item_options').hide();
        $('.item_function input').val('role_type');
    }else if(type == 2){
        $('.item_function').show();
        $('.item_options').hide();
        $('.item_function input').val('');
    }else{
        $('.item_options').show();
        $('.item_function').hide();
    }
}
</script>
EOF;
            $switch_function_arg=[
                'role_type'=>['title'=>'角色类型(role_type)','data-type'=>'1'],
                'custom_function'=>['title'=>'自定义函数','data-type'=>'2']
                ];
            // 使用FormBuilder快速建立表单页面。

            $builder = Builder::run('Form');
            $builder->setMetaTitle($title.'配置')  // 设置页面标题
                    //->setPostUrl(url('edit'))    // 设置表单提交地址
                    ->addFormItem('id', 'hidden', 'ID', 'ID')
                    ->addFormItem('group', 'select', '配置分组', '配置所属的分组', config('config_group_list'))
                    ->addFormItem('sub_group','number','配置子分组','先对大分组创建一个子分组，一般不填写')
                    ->addFormItem('type', 'select', '配置类型', '配置类型的分组',config('form_item_type'))
                    ->addFormItem('switch_function','select','关联函数','可选(关联一个函数返回值，生成选项值)',$switch_function_arg)
                    ->addFormItem('name', 'text', '配置名称', '配置名称')
                    ->addFormItem('title', 'text', '配置标题', '配置标题')
                    ->addFormItem('value', 'textarea', '配置值', '配置值')
                    ->addFormItem('options', 'textarea', '配置项', '如果是单选、多选、下拉等类型 需要配置该项')
                    ->addFormItem('function', 'text', '关联函数', '确保函数已创建，并且函数具有返回值')
                    ->addFormItem('remark', 'textarea', '配置说明', '配置说明')
                    ->addFormItem('sort', 'number', '排序', '用于显示的顺序')
                    //->addFormItem('status', 'radio', '是否显示', '显示或隐藏',array(0=>'否',1=>'是'))
                    ->setFormData($Config_data)
                    ->setExtraHtml($switch_function_html)
                    //->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
    }

    /**
     * 获取某个分组的配置参数
     */
    public function group($group = 1){
        //根据分组获取配置
        $map=[
            'status'=>['egt', '1'],
            'group' =>['eq', $group]
        ];
        $data_list =$this->configModel->getList($map,'*','sort asc,id asc');

        // 设置Tab导航数据列表
        $config_group_list = config('config_group_list');  // 获取配置分组
        unset($config_group_list[6]);//去除不显示的分组
        //unset($config_group_list[7]);//用户
        //unset($config_group_list[5]);
        unset($config_group_list[8]);
        foreach ($config_group_list as $key => $val) {
            $tab_list[$key]['title'] = $val;
            $tab_list[$key]['href']  = url('group', ['group' => $key]);
        }

        // 构造表单名、解析options
        foreach ($data_list as &$data) {
            $data['name']        = 'config['.$data['name'].']';
            $data['description'] = $data['remark'];
            $data['confirm']     = $data['extra_class'] = $data['extra_attr']='';
            if ($data['function']!='0'&&$data['function']) {
                $data['options'] = call_user_func_array($data['function'],array('1'));
            }else{
                $data['options'] = parse_config_attr($data['options']);
            }
            
        }

        $builder = Builder::run('Form');
        switch ($group) {
            case 5:
                $builder->setPageTips('请在官网<a href="http://www.eacoo123.com/register" target="_blank">注册账户</a>，然后填写下方注册信息');
                break;
            
            default:
                # code...
                break;
        }
        $builder->setMetaTitle('系统设置')       // 设置页面标题
                ->setTabNav($tab_list, $group)  // 设置Tab按钮列表
                ->setExtraItems($data_list)     // 直接设置表单数据
                ->addButton('submit','确认',url('groupSave'))->addButton('back') // 设置表单按钮
                ->fetch();
    }

    /**
     * 批量保存配置
     */
    public function groupSave($config) {
        if ($config && is_array($config)) {
            foreach ($config as $name => $value) {
                $map = ['name' => $name];
                // 如果值是数组则转换成字符串，适用于复选框等类型
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                if ($name=='develop_mode') {
                    cache('admin_sidebar_menus',null);//清空后台菜单缓存
                }
                $this->configModel->where($map)->update(['value'=>$value]);
            }
        }
        cache('DB_CONFIG_DATA',null);
        $this->success('保存成功！');
    }

    /**
     * 网站信息设置
     * @param  integer $sub_group [description]
     * @return [type] [description]
     * @date   2017-10-17
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function website($sub_group=0)
    {
        //根据分组获取配置
        $map['status'] = ['egt', '0'];  // 禁用和正常状态
        $map['group']  = 6;//6是大分组网站信息
        $map['sub_group'] = $sub_group;
        $data_list = $this->configModel->getList($map,'*','sort asc,id asc');

        // 设置Tab导航数据列表
        $config_subgroup_list = config('website_group');  // 获取配置分组
        foreach ($config_subgroup_list as $key => $val) {
            $tab_list[$key]['title'] = $val;
            $tab_list[$key]['href']  = url('website', array('sub_group' => $key));
        }

        // 构造表单名、解析options
        foreach ($data_list as &$data) {
            $data['name']    = 'config['.$data['name'].']';
            $data['description'] = $data['remark'];
            $data['confirm'] = $data['extra_class'] = $data['extra_attr']='';
            $data['options'] = parse_config_attr($data['options']);
        }

        // 使用FormBuilder快速建立表单页面。

        Builder::run('Form')
                ->setMetaTitle('网站设置')       // 设置页面标题
                ->SetTabNav($tab_list, $sub_group)  // 设置Tab按钮列表
                ->setPostUrl(url('groupSave'))    // 设置表单提交地址
                ->setExtraItems($data_list)     // 直接设置表单数据
                ->addButton('submit','确认',url('groupSave'))->addButton('back')    // 设置表单按钮
                ->fetch();
    }

    /**
     * 移动配置分组
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function moveGroup() {
        if (IS_POST) {
            $ids      = input('post.ids');
            $from_gid = input('post.from_gid');
            $to_gid   = input('post.to_gid');
            if ($from_gid === $to_gid) {
                $this->error('目标分类与当前分类相同');
            }
            if ($to_gid) {
                $map['id'] = array('in',$ids);
                $data      = array('group' => $to_gid);
                $this->editRow('config', $data, $map, array('success'=>'移动成功','error'=>'移动失败',url('index')));

            } else {
                $this->error('请选择目标配置组');
            }
        }
    }
    /**
     * 构建列表移动配置分组按钮
     * @author 心云间、凝听 <981248356@qq.com>
     */
    protected function moveGroupHtml($config_group_list,$group_id){
            //构造移动文档的目标分类列表
            $options = '';
            foreach ($config_group_list as $key => $val) {
                $options .= '<option value="'.$key.'">'.$val.'</option>';
            }
            //文档移动POST地址
            $move_url = url('moveGroup');

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
                                    <select name="to_gid" class="form-control">{$options}</select>
                                </div>
                                <div class="form-group">
                                    <input type="hidden" name="ids">
                                    <input type="hidden" name="from_gid" value="{$group_id}">
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
                        $('.modal-title').html('移动选中的配置至：');
                        $('#moveModal').modal('show', 'fit')
                    }else{
                        updateAlert('请选择需要移动的配置', 'warning');
                    }
                }
            </script>
EOF;
    }
}
