<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.eacoo123.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\Addons as AddonsModel;
use app\admin\model\Hooks;

use app\admin\builder\Builder;
use com\Sql;
/**
 * 扩展后台管理页面
 * 该类参考了OneThink的部分实现
 */
class Addons extends Admin {

    protected $addonModel;
    protected $hooksModel;

    function _initialize()
    {
        parent::_initialize();
        
        $this->addonModel = new AddonsModel();
        $this->hooksModel = new Hooks();
    }

    /**
     * 插件列表
     * @return [type] [description]
     * @date   2017-09-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index() {

        $this->assign('custom_head',['self'=>'<a href="'.url('admin/addons/hooks').'" class="btn btn-primary btn-sm mr-10">钩子管理</a>']);
        // 获取所有插件信息
        //$paged = $this->input('get.p',1);
        $addons = $this->addonModel->getAllAddon();

        // 使用Builder快速建立列表页面。
        Builder::run('List')
                ->setMetaTitle('插件列表')  // 设置页面标题
                ->addTopButton('resume')   // 添加启用按钮
                ->addTopButton('forbid')   // 添加禁用按钮
                ->keyListItem('name', '标识')
                ->keyListItem('title', '名称')
                ->keyListItem('description', '描述')
                ->keyListItem('status', '状态')
                ->keyListItem('author', '作者')
                ->keyListItem('version', '版本')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListData($addons)    // 数据列表
                ->fetch();
    }

    /**
     * 插件设置
     * @return [type] [description]
     * @date   2017-08-30
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function config() {
        if (IS_POST) {
            $id     = input('param.id');
            $config = $this->input('post.config/a');
            $flag   = $this->addonModel->save(['config'=>json_encode($config)],['id'=>$id]);
            if ($flag !== false) {
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        } else {
            $id     = input('param.id');
            if (!$id) {
                $map = ['name'=>input('param.name')];
            } else{
                $map = ['id'=>input('param.id')];
            }
            $addon  = AddonsModel::get($map);
            $addon = $addon ? $addon->toArray() : $addon;
            if (!$addon) {
                $this->error('插件未安装');
            }
            $addon_class = get_addon_class($addon['name']);
            if (!class_exists($addon_class)) {
                trace("插件{$addon['name']}无法实例化,",'ADDONS','ERR');
            }
            $db_config = $addon['config'];

            $addon_obj              = new $addon_class;
            $addon['addon_path']    = $addon_obj->addon_path;
            $addon['custom_config'] = $addon_obj->custom_config;
            $addon['config']        = include $addon_obj->config_file;

            $this->meta_title = '设置-'.$addon_obj->info['title'];
            if ($db_config) {
                $db_config = json_decode($db_config, true);//dump($db_config['sliders']);
                foreach ($addon['config'] as $key => $value) {
                    if ($value['type'] != 'group') {
                        if (isset($db_config[$key])) {
                            $addon['config'][$key]['value'] = $db_config[$key];
                        }
                    } else {
                        foreach ($value['options'] as $gourp => $options) {
                            foreach ($options['options'] as $gkey => $value) {
                                $addon['config'][$key]['options'][$gourp]['options'][$gkey]['value'] = $db_config[$gkey];
                            }
                        }
                    }
                }
            }
            // 构造表单名
            foreach ($addon['config'] as $key => $val) {
                if ($val['type'] == 'group') {
                    foreach ($val['options'] as $key2 => $val2) {
                        foreach ($val2['options'] as $key3 => $val3) {
                            $addon['config'][$key]['options'][$key2]['options'][$key3]['name'] = 'config['.$key3.']';

                            $addon['config'][$key]['options'][$key2]['options'][$key3]['confirm'] = $addon['config'][$key]['options'][$key2]['options'][$key3]['extra_class'] = $addon['config'][$key]['options'][$key2]['options'][$key3]['extra_attr']='';
                        }
                    }
                } else {
                    $addon['config'][$key]['name'] = 'config['.$key.']';

                    $addon['config'][$key]['confirm']     = isset($val['confirm']) ? $val['confirm']:'';
                    $addon['config'][$key]['options']     = isset($val['options']) ? $val['options']:[];
                    $addon['config'][$key]['extra_class'] = isset($val['extra_class']) ? $val['extra_class']:'';
                    $addon['config'][$key]['extra_attr']  = isset($val['extra_attr']) ? $val['extra_attr']:'';

                }
                
            }
             $this->assign('data', $addon);
             $this->assign('form_items', $addon['config']);
            if ($addon['custom_config']) {
                $this->assign('custom_config', $this->fetch($addon['addon_path'].$addon['custom_config']));
                return $this->fetch($addon['addon_path'].$addon['custom_config']);
            } else {
                Builder::run('Form')
                        ->setMetaTitle($this->meta_title)  //设置页面标题
                        ->setPostUrl(url('config')) //设置表单提交地址
                        ->addFormItem('id', 'hidden', 'ID', 'ID')
                        ->setExtraItems($addon['config']) //直接设置表单数据
                        ->setFormData($addon)
                        ->addButton('submit')->addButton('back')    // 设置表单按钮
                        ->fetch();
            }
        }
    }

    /**
     * 安装插件
     */
    public function install() {
        $addon_name = trim(input('addon_name'));
        $class      = get_addon_class($addon_name);
        if (!class_exists($class)) {
            $this->error('插件不存在');
        }
        $addons = new $class;
        $info   = $addons->info;
        $hooks  = $addons->hooks;

        // 检测信息的正确性
        if (!$info || !$addons->checkInfo()){ 
            $this->error('插件信息缺失');
        }
        session('addons_install_error',null);
        $install_flag = $addons->install();
        if (!$install_flag) {
            $this->error('执行插件预安装操作失败'.session('addons_install_error'));
        }

        // 检查该插件所需的钩子
        if ($hooks) {
            foreach ($hooks as $val) {
                $this->hooksModel->existHook($val, ['description' => $info['description']]);
            }
        }

        // 安装数据库
        $sql_file = realpath(config('addon_path').$addon_name).'/sql/install.sql';
        if (file_exists($sql_file)) {
            $sql_status = Sql::execute_sql_from_file($sql_file);
            if (!$sql_status) {
                $this->error('执行插件SQL安装语句失败'.session('addons_install_error'));
            }
        }

        // $data = $this->addonModel->data($info);
        // if (!$data) {
        //     $this->error($this->addonModel->getError());
        // }
        if ($this->addonModel->allowField(true)->isUpdate(false)->data($info)->save()) {
            $config = [
                'config'=>json_encode($addons->getConfig())
            ];
            $this->addonModel->where("name='{$addon_name}'")->update($config);
            $hooks_update = $this->hooksModel->updateHooks($addon_name);
            if ($hooks_update) {
                cache('hooks', null);
                $this->success('安装成功');
            } else {
                $this->addonModel->where("name='{$addon_name}'")->delete();
                $this->error('更新钩子处插件失败,请卸载后尝试重新安装');
            }
        } else {
            $this->error('写入插件数据失败');
        }
    }

    /**
     * 卸载插件
     */
    public function uninstall() {
        $id        = trim(input('id'));
        $addon_info = $this->addonModel->field('name')->find($id);
        $class     = get_addon_class($addon_info['name']);

        $this->assign('jumpUrl',url('index'));

        if (!$addon_info || !class_exists($class)) {
            $this->error('插件不存在');
        }
        session('addons_uninstall_error',null);

        $addons = new $class;
        $uninstall_flag = $addons->uninstall();
        if (!$uninstall_flag) {
            $this->error('执行插件预卸载操作失败'.session('addons_uninstall_error'));
        }
        $hooks_update = $this->hooksModel->removeHooks($addon_info['name']);
        if ($hooks_update === false) {
            $this->error('卸载插件所挂载的钩子数据失败');
        }
        cache('hooks', null);
        $delete = $this->addonModel->where("name='{$addon_info['name']}'")->delete();

        // 卸载数据库
        $sql_file = realpath(config('addon_path').$addon_info['name']).'/sql/uninstall.sql';
        if (file_exists($sql_file)) {
            $sql_status = Sql::execute_sql_from_file($sql_file);
            if (!$sql_status) {
                $this->error('执行插件SQL卸载语句失败'.session('addons_uninstall_error'));
            }
        }

        if ($delete === false) {
            $this->error('卸载插件失败');
        } else {
            $this->success('卸载成功');
        }
    }

    /**
     * 外部执行插件方法
     * @param  [type] $_addons [description]
     * @param  [type] $_controller [description]
     * @param  [type] $_action [description]
     * @return [type] [description]
     * @date   2017-08-31
     * @author 心云间、凝听 <981248356@qq.com>
     */
    // public function execute($_addons = null, $_controller = null, $_action = null) {
    //     if (config('url_case_insensitive')) {
    //         $_addons     = ucfirst(parse_name($_addons, 1));
    //         $_controller = parse_name($_controller,1);
    //     }

    //     $TMPL_PARSE_STRING = config('TMPL_PARSE_STRING');
    //     $TMPL_PARSE_STRING['__ADDONROOT__'] = __ROOT__ . "/addons/{$_addons}";
    //     config('TMPL_PARSE_STRING', $TMPL_PARSE_STRING);

    //     if (!empty($_addons) && !empty($_controller) && !empty($_action)) {
    //         $Addons = action("Addons://{$_addons}/{$_controller}")->$_action();
    //     } else {
    //         $this->error('没有指定插件名称，控制器或操作！');
    //     }
    // }
    
    public function execute($mc = null, $op = '', $ac = null) {
        $op = $op ? $op : $this->request->module();
        if (\think\Config::get('url_case_insensitive')) {
            $mc = ucfirst(parse_name($mc, 1));
            $op = parse_name($op, 1);
        }

        if (!empty($mc) && !empty($op) && !empty($ac)) {
            $ops    = ucwords($op);
            $class  = "\\addons\\{$mc}\\controller\\{$ops}";
            $addons = new $class;
            $addons->$ac();
        } else {
            $this->error('没有指定插件名称，控制器或操作！');
        }
    }

    /**
     * 插件后台显示页面
     * @param string $name 插件名
     */
    public function adminManage($name='', $action ='index',$controller='') {
        // 获取插件实例
       $addon_class = get_addon_class($name);
        if (!class_exists($addon_class)) {
            $this->error('插件不存在');
        } else {
            if ($controller=='') {
                $controller='Admin'.$name;
            }
            $class_name = "\\addons\\{$name}\\controller\\{$controller}";
            $addon_controller = new $class_name;
            return $addon_controller->$action();
            //return controller("addons:/{$name}/controller/{$controller}")->$action();
        }

    }

    /**
     * 插件后台数据增加
     * @param string $name 插件名
     */
     public function adminAdd($name, $tab){
        // 获取插件实例
        $addon_class = get_addon_class($name);
        if (!class_exists($addon_class)) {
            $this->error('插件不存在');
        } else {
            $addon = new $addon_class();
        }

        // 获取插件的$admin_list配置
        $admin_list = $addon->admin_list;
        $admin = $admin_list[$tab];
        $addonModel = model('Addons://'.$name.'/'.$admin['model']);
        $param = $addonModel->adminList;
        if ($param) {
            if (IS_POST) {   

                $data = $this->param;
                $id = $data['id'];
                if ($addonModel->editData($data,$id)) {

                    $this->success('新增成功', url('admin/Addons/adminlist', ['name' => $name,'tab' => $tab]));
                } else {
                    $this->error($addonModel->getError());
                }

            } else {
                // 使用FormBuilder快速建立表单页面。
                Builder::run('Form')
                        ->setMetaTitle('新增数据')  //设置页面标题
                        ->setPostUrl(url('Admin/Addons/adminAdd', array('name' => $name, 'tab' => $tab))) // 设置表单提交地址
                        ->setExtraItems($param['field'])
                        ->fetch();
            }
        } else {
            $this->error('插件列表信息不正确');
        }
     }

    /**
     * 插件后台数据编辑
     * @param string $name 插件名
     */
     public function adminEdit($name, $tab, $id) {
        // 获取插件实例
        $addon_class = get_addon_class($name);
        if (!class_exists($addon_class)) {
            $this->error('插件不存在');
        } else {
            $addon = new $addon_class();
        }

        // 获取插件的$admin_list配置
        $admin_list = $addon->admin_list;
        $admin = $admin_list[$tab];
        $addonModel_object = model('Addons://'.$name.'/'.$admin['model']);
        $param = $addonModel_object->adminList;
        if ($param) {
            if (IS_POST) {
                $data = $this->input('post.');
                $id   = isset($data['id']) && $data['id']>0 ? $data['id']:false;
                if ($addonModel_object->editData($data,$id)) {
                    $result = $addonModel_object->save($data);
                } else {
                    $this->error($addonModel_object->getError());
                }
                if ($result) {
                    $this->success('更新成功', url('admin/Addon/adminlist', array('name' => $name, 'tab' => $tab)));
                } else {
                    $this->error('更新错误');
                }
            } else {
                Builder::run('Form')
                        ->setMetaTitle('编辑数据')  // 设置页面标题
                        ->setPostUrl(url('admin/addon/adminedit', array('name' => $name, 'tab' => $tab))) // 设置表单提交地址
                        ->addFormItem('id', 'hidden', 'ID', 'ID')
                        ->setExtraItems($param['field'])
                        ->setFormData(db($param['model'])->get($id))
                        ->fetch();
            }
        } else {
            $this->error('插件列表信息不正确');
        }
    }

    /*
    *插件模板
    *
    */
    public function fetch($templateFile='',$vars = [], $replace ='', $config = ''){
        if ($template = '') {
            $template =T('Addons://'.$name.'@'.CONTROLLER_NAME.'/'.ACTION_NAME);
        }     
        return $this->fetch($template);
        
    }

    /**
     * 钩子列表
     */
    public function hooks(){
        $this->assign('custom_head',['back'=>true]);
        // 获取所有钩子
        $map['status'] = ['egt', '0'];  // 禁用和正常状态
        list($data_list,$page) = $this->hooksModel->getListByPage($map,'create_time desc','*',20);
        Builder::run('List')
                ->setMetaTitle('钩子列表')  // 设置页面标题
                ->addTopButton('addnew',array('href'=>url('edithook'),'title'=>'<i class="fa fa-plus"></i> 新增钩子','class'=>'btn bg-purple margin'))    // 添加新增按钮
                ->keyListItem('id', 'ID')
                ->keyListItem('name', '名称')
                ->keyListItem('description', '描述')
                ->keyListItem('type', '类型', 'array', config('hooks_type'))
                ->keyListItem('right_button', '操作', 'btn')
                ->setListData($data_list)     // 数据列表
                ->setListPage($page)  // 数据列表分页
                ->addRightButton('edit',['href'=>url('edithook',['id'=>'__data_id__'])])           // 添加编辑按钮
                ->addRightButton('delete')  // 添加删除按钮
                ->fetch();
    }

    /**
     * 钩子出编辑挂载插件页面
     * @param  integer $id [description]
     * @return [type] [description]
     * @date   2017-09-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function edithook($id=0){
        $title=$id ? "编辑" : "新增";
        if (IS_POST) {
            $data = $this->input('post.');
            //验证数据
            $this->validateData('Hook',$data);
            $id = isset($data['id']) && $data['id']>0 ? $data['id']:false;
            if ($this->hooksModel->editData($data,$id)) {
                $this->success($title.'成功', url('hooks'));
            } else {
                $this->error($this->hooksModel->getError());
            }

        } else {
            $info = [];
            if ($id!=0) {
                $info = Hooks::get($id);
            }
            $builder = Builder::run('Form');
            $builder->setMetaTitle($title.'钩子'); // 设置页面标题
                if ($id!=0) {
                    $builder->addFormItem('id', 'hidden', 'ID', '');
                }
            $builder->addFormItem('name', 'text', '名称', '需要在程序中先添加钩子，否则无效')
                    ->addFormItem('description', 'textarea', '描述', '钩子的描述信息')
                    ->addFormItem('type', 'radio', '类型', '链接类型',config('hooks_type'))
                    ->setFormData($info)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
        }
    }
    
    /**
     * 检测钩子是否存在
     * @param  [type] $name [description]
     * @param  [type] $data [description]
     * @return [type] [description]
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function existHook($name, $data){
        return $this->hooksModel->existHook($name, $data);
    }
    
    /**
     * 超级管理员删除钩子
     * @param  [type] $id [description]
     * @return [type] [description]
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function delhook($id){
        if(Hooks::destroy($id)){
            $this->success('删除成功');
        } else{
            $this->error('删除失败');
        }
    }

}
