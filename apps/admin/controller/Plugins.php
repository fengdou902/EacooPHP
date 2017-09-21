<?php
// 插件后台管理控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.eacoo123.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\Plugins as PluginsModel;
use app\admin\model\Hooks;
use app\admin\model\AuthRule;

use app\admin\builder\Builder;
use com\Sql;

class Plugins extends Admin {

    protected $pluginModel;
    protected $hooksModel;
    protected $pluginDir;

    function _initialize()
    {
        parent::_initialize();
        
        $this->pluginModel = new PluginsModel();
        $this->hooksModel  = new Hooks();
        $this->pluginDir   = PluginsModel::$pluginDir;
    }

    /**
     * 插件列表
     * @return [type] [description]
     * @date   2017-09-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index() {

        $this->assign('custom_head',['self'=>'<a href="'.url('admin/plugins/hooks').'" class="btn btn-primary btn-sm mr-10">钩子管理</a>']);
        // 获取所有插件信息
        //$paged = $this->input('get.p',1);
        $plugins = $this->pluginModel->getAll();

        // 使用Builder快速建立列表页面。
        Builder::run('List')
                ->setMetaTitle('插件列表')  // 设置页面标题
                ->addTopButton('resume')   // 添加启用按钮
                ->addTopButton('forbid')   // 添加禁用按钮
                ->keyListItem('name', '标识')
                ->keyListItem('title', '名称')
                ->keyListItem('description', '描述')
                ->keyListItem('author', '作者')
                ->keyListItem('status', '状态')
                ->keyListItem('version', '版本')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListData($plugins)    // 数据列表
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
            $flag   = $this->pluginModel->save(['config'=>json_encode($config)],['id'=>$id]);
            if ($flag !== false) {
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        } else {
            $id = input('param.id');
            if (!$id) {
                $map = ['name'=>input('param.name')];
            } else{
                $map = ['id'=>input('param.id')];
            }
            $plugin  = PluginsModel::get($map);
            $plugin = $plugin ? $plugin->toArray() : $plugin;
            if (!$plugin) {
                $this->error('插件未安装');
            }

            $plugin_class = get_plugin_class($plugin['name']);
            if (!class_exists($plugin_class)) {
                trace("插件{$plugin['name']}无法实例化,",'Plugin','ERR');
            }
            $db_config = $plugin['config'];
            $options        = PluginsModel::getOptionsByFile($plugin['name']);

            $this->meta_title = '设置-'.$plugin['title'];
            if (!empty($options) && is_array($options)) {
                if (!empty($db_config)) {
                    $db_config = json_decode($db_config, true);//dump($db_config['sliders']);
                    foreach ($options as $key => $value) {
                        if ($value['type'] != 'group') {
                            if (isset($db_config[$key])) {
                                $options[$key]['value'] = $db_config[$key];
                            }
                        } else {
                            foreach ($value['options'] as $gourp => $option) {
                                foreach ($option['options'] as $gkey => $value) {
                                    $options[$key]['options'][$gourp]['options'][$gkey]['value'] = $db_config[$gkey];
                                }
                            }
                        }
                    }
                }
                // 构造表单名
                foreach ($options as $key => $val) {
                    if ($val['type'] == 'group') {
                        foreach ($val['options'] as $key2 => $val2) {
                            foreach ($val2['options'] as $key3 => $val3) {
                                $options[$key]['options'][$key2]['options'][$key3]['name'] = 'config['.$key3.']';

                                $options[$key]['options'][$key2]['options'][$key3]['confirm'] = $options[$key]['options'][$key2]['options'][$key3]['extra_class'] = $options[$key]['options'][$key2]['options'][$key3]['extra_attr']='';
                            }
                        }
                    } else {
                        $options[$key]['name'] = 'config['.$key.']';

                        $options[$key]['confirm']     = isset($val['confirm']) ? $val['confirm']:'';
                        $options[$key]['options']     = isset($val['options']) ? $val['options']:[];
                        $options[$key]['extra_class'] = isset($val['extra_class']) ? $val['extra_class']:'';
                        $options[$key]['extra_attr']  = isset($val['extra_attr']) ? $val['extra_attr']:'';

                    }  
                }
            }
    
            if (!empty($plugin['custom_config'])) {
                $this->assign('data', $plugin);
                $this->assign('form_items', $options);
                $this->assign('custom_config', $this->fetch($plugin['plugin_path'].$plugin['custom_config']));
                return $this->fetch($plugin['plugin_path'].$plugin['custom_config']);
            } else {
                Builder::run('Form')
                        ->setMetaTitle($this->meta_title)  //设置页面标题
                        ->setPostUrl(url('config')) //设置表单提交地址
                        ->addFormItem('id', 'hidden', 'ID', 'ID')
                        ->setExtraItems($options) //直接设置表单数据
                        ->setFormData($plugin)
                        ->addButton('submit')->addButton('back')    // 设置表单按钮
                        ->fetch();
            }
        }
    }

    /**
     * 安装插件
     * @param  string $name 插件名
     * @return [type] [description]
     * @date   2017-09-18
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function install($name='') {
        PluginsModel::$pluginName = $name;

        $info = PluginsModel::getInfoByFile($name);
        // 检测信息的正确性
        if (!$info){ 
            $this->error('插件信息缺失');
        }

        $plugin_class = get_plugin_class($name);
        if (!class_exists($plugin_class)) {
            $this->error('插件实例化文件损坏');
        } else{
            $plugin_class = new $plugin_class;
            // 插件预安装
            if(!$plugin_class->install()) {
                $this->error('插件预安装失败!原因：'. $plugin_class->getError());
            }
        }

        // 依赖性检查
        if (!empty($info['dependences'])) {
            $result = $this->checkDependence($info['dependences']);
            if (!$result) {
                return false;
            }
        }

        $hooks = $this->pluginModel->getDependentHooks($name);//获取依赖钩子
        //预安装检测
        $this->checkInstall($name);

        // 检查该插件所需的钩子
        if (!empty($hooks)) {
            foreach ($hooks as $val) {
                $this->hooksModel->existHook($val, ['description' => $info['description']]);
            }
        }

        // 安装数据库
        $sql_file = realpath($this->pluginDir.$name).'/install/install.sql';
        if (is_file($sql_file)) {
            $sql_status = Sql::executeSqlByFile($sql_file, $info['database_prefix']);
            if (!$sql_status) {
                $this->error('执行插件SQL安装语句失败');
            }
        }
        
        $config = PluginsModel::getDefaultConfig($name);//获取文件中的默认配置值
        $info['config'] = !empty($config) ? json_encode($config) : '';
        if ($this->pluginModel->allowField(true)->isUpdate(false)->data($info)->save()) {
            //更新钩子
            $hooks_update = $this->hooksModel->updateHooks($name);
            if ($hooks_update) {
                cache('hooks', null);

                //后台菜单权限入库
                $admin_menus = PluginsModel::getAdminMenusByFile($name);
                if (!empty($admin_menus) && is_array($admin_menus)) {
                    $this->addAdminMenus($admin_menus,$name);
                }

                //静态资源文件
                $static_path = realpath($this->pluginDir.$name).'/static';
                if (is_dir($static_path)) {
                    if(is_writable(ROOT_PATH.'public/static/plugins') && is_writable($static_path)){
                        if (!rename($static_path,ROOT_PATH.'public/static/plugins/'.$name)) {
                            trace('插件静态资源移动失败','error');
                        } 
                    } else{
                        PluginsModel::where('name',$name)->update(['status'=>0]);
                        $this->error('安装失败，原因：插件静态资源目录不可写');
                    }
                    
                }
                $this->success('安装成功');
            } else {
                $this->pluginModel->where("name='{$name}'")->delete();
                $this->error('更新钩子处插件失败,请卸载后尝试重新安装');
            }
        } else {
            $this->error('写入插件数据失败');
        }
    }

    /**
     * 卸载插件之前
     */
    public function uninstallBefore($id) {
        Builder::run('Form')
                ->setMetaTitle('准备卸载插件')  // 设置页面标题
                ->setPostUrl(url('uninstall'))     // 设置表单提交地址
                ->addFormItem('id', 'hidden', 'ID', 'ID')
                ->addFormItem('clear', 'radio', '是否清除数据', '是否清除数据', array(1=> '是', 0=> '否（禁用）'))
                ->setFormData(array('id' => $id))
                ->addButton('submit')->addButton('back')    // 设置表单按钮
                ->fetch();
    }

    /**
     * 卸载插件
     * @param  [type] $id 插件ID
     * @param  boolean $clear 是否清除数据
     * @return [type] [description]
     * @date   2017-09-16
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function uninstall($id, $clear = false) {
        $plugin_info = $this->pluginModel->where('id',$id)->field('name')->find();

        $hooks_update = $this->hooksModel->removeHooks($plugin_info['name']);
        if ($hooks_update === false) {
            $this->error('卸载插件所挂载的钩子数据失败');
        }
        cache('hooks', null);
        if ($clear) {
            $result = PluginsModel::destroy($id);
        } else{
            $result = PluginsModel::where('id',$id)->update(['status'=>0]);
        }
        if ($result) {
            // 删除后台菜单
            $this->removeAdminMenus($plugin_info['name'],$clear);
            // 卸载数据库
            $sql_file = realpath(PluginsModel::$pluginDir.$plugin_info['name']).'/install/uninstall.sql';
            if (is_file($sql_file)) {
                $info       = PluginsModel::getInfoByFile($plugin_info['name']);
                $sql_status = Sql::executeSqlByFile($sql_file, $info['database_prefix']);
                if (!$sql_status) {
                    $this->error('执行插件SQL卸载语句失败');
                }
            }
            $this->success('卸载成功',url('index'));
        } else{
            $this->error('卸载插件失败');
        }

    }

    /**
     * 模块依赖性检查
     * @param  [type] $dependences 模块
     * @return [type] [description]
     * @date   2017-09-15
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function checkDependence($dependences) {
        if (is_array($dependences)) {
            $modules = $dependences['modules'];//依赖的模块
            $plugins = $dependences['plugins'];//依赖的插件
            //模块
            if (!empty($modules) && is_array($modules)) {
                foreach ($modules as $key => $val) {
                    if ($key=='admin' || $key=='user') {
                        continue;
                    }
                    $map = [
                        'name'=>$key,
                    ];
                    $module_info = db('modules')->where($map)->field('version,title')->find();
                    if (!$module_info) {
                        $this->error('该模块依赖'.$key.'模块');
                    }
                    //版本号检查
                    $module_version = explode('.', $module_info['version']);
                    $need_version   = explode('.', $val);

                    if (($module_version[0] - $need_version[0]) >= 0) {
                        if (($module_version[1] - $need_version[1]) >= 0) {
                            if (($module_version[2] - $need_version[2]) >= 0) {
                                continue;
                            }
                        }
                    }
                    $this->error($module_info['title'].'模块版本不得低于v'.$val);
                }
            }
            //插件
            if (!empty($plugins) && is_array($plugins)) {
                foreach ($modules as $key => $val) {
                    $map = [
                        'name'=>$key,
                    ];
                    $plugins_info = PluginsModel::where($map)->field('version,title')->find();
                    if (!$plugins_info) {
                        $this->error('该模块依赖'.$key.'插件');
                    }
                    //版本号检查
                    $module_version = explode('.', $plugins_info['version']);
                    $need_version   = explode('.', $val);

                    if (($module_version[0] - $need_version[0]) >= 0) {
                        if (($module_version[1] - $need_version[1]) >= 0) {
                            if (($module_version[2] - $need_version[2]) >= 0) {
                                continue;
                            }
                        }
                    }
                    $this->error($plugins_info['title'].'插件版本不得低于v'.$val);
                }

            }
            
            return true;
        }
    }

    /**
     * 检测安装
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-09-18
     * @author 心云间、凝听 <981248356@qq.com>
     */
    private function checkInstall($name='')
    {
        if ($name) {
            $flag = PluginsModel::checkInfoFile($name);//检测安装信息
            if (!$flag) {
                $this->error('插件信息缺失');
            }
        }
    }

    /**
     * 添加后台菜单
     * @param  array $data 菜单数据
     * @param  integer $pid 父级ID
     * @param  string $flag_name 插件名
     * @date   2017-09-15
     * @author 心云间、凝听 <981248356@qq.com>
     */
    private function addAdminMenus($data = [], $flag_name = '', $pid = 0)
    {
        if (!empty($data) && is_array($data) && $flag_name!='') {
            $authRuleModel = new AuthRule;
            foreach ($data as $key => $menu) {
                $pid = isset($menu['pid']) ? (int)$menu['pid'] : $pid;

                $menu['from_type'] = 2;//2代表plugin
                $menu['from_flag'] = $flag_name;
                $menu['pid']    = $pid;
                $menu['sort']   = isset($menu['sort']) ? $menu['sort'] : 99;
                $authRuleModel->allowField(true)->isUpdate(false)->data($menu)->save();
                //添加子菜单
                if (!empty($menu['sub_menu'])) {
                    $this->addAdminMenus($menu['sub_menu'], $flag_name, $authRuleModel->id);
                }
            }
            cache('admin_sidebar_menus',null);//清空后台菜单缓存
            return true;
        }
        return false;
    }

    /**
     * 移除后台菜单
     * @param  string $flag_name 模块名
     * @param  boolean $delete 是否删除数据
     * @return [type] [description]
     * @date   2017-09-18
     * @author 心云间、凝听 <981248356@qq.com>
     */
    private function removeAdminMenus($flag_name='' ,$delete=true)
    {
        if ($flag_name!='') {
            $map = [
                'from_type'=>2,
                'from_flag'=>$flag_name
            ];
            if ($delete) {
                $res = AuthRule::where($map)->delete();
            } else{
                $res = AuthRule::where($map)->update(['status'=>0]);
            }
            if (false === $res) {
                $this->error('菜单删除失败，请重新卸载');
            } else{
                cache('admin_sidebar_menus',null);//清空后台菜单缓存
                return true;
            }
        }
        return false;
    }

    /**
     * 外部执行插件方法
     * @param  [type] $_plugins [description]
     * @param  [type] $_controller [description]
     * @param  [type] $_action [description]
     * @return [type] [description]
     * @date   2017-08-31
     * @author 心云间、凝听 <981248356@qq.com>
     */
    // public function execute($_plugins = null, $_controller = null, $_action = null) {
    //     if (config('url_case_insensitive')) {
    //         $_plugins     = ucfirst(parse_name($_plugins, 1));
    //         $_controller = parse_name($_controller,1);
    //     }

    //     $TMPL_PARSE_STRING = config('TMPL_PARSE_STRING');
    //     $TMPL_PARSE_STRING['__ADDONROOT__'] = __ROOT__ . "/plugins/{$_plugins}";
    //     config('TMPL_PARSE_STRING', $TMPL_PARSE_STRING);

    //     if (!empty($_plugins) && !empty($_controller) && !empty($_action)) {
    //         $Plugins = action("Plugins://{$_plugins}/{$_controller}")->$_action();
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
            $class  = "\\plugins\\{$mc}\\controller\\{$ops}";
            $plugins = new $class;
            $plugins->$ac();
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
       $plugin_class = get_plugin_class($name);
        if (!class_exists($plugin_class)) {
            $this->error('插件不存在');
        } else {
            if ($controller=='') {
                $controller='Admin'.$name;
            }
            $class_name = "\\plugins\\{$name}\\controller\\{$controller}";
            $plugin_controller = new $class_name;
            return $plugin_controller->$action();
            //return controller("plugins:/{$name}/controller/{$controller}")->$action();
        }

    }

    /**
     * 插件后台数据增加
     * @param string $name 插件名
     */
     public function adminAdd($name, $tab){
        // 获取插件实例
        $plugin_class = get_plugin_class($name);
        if (!class_exists($plugin_class)) {
            $this->error('插件不存在');
        } else {
            $plugin = new $plugin_class();
        }

        // 获取插件的$admin_list配置
        $admin_list = $plugin->admin_list;
        $admin = $admin_list[$tab];
        $pluginModel = model('Plugins://'.$name.'/'.$admin['model']);
        $param = $pluginModel->adminList;
        if ($param) {
            if (IS_POST) {   

                $data = $this->param;
                $id = $data['id'];
                if ($pluginModel->editData($data,$id)) {

                    $this->success('新增成功', url('admin/Plugins/adminlist', ['name' => $name,'tab' => $tab]));
                } else {
                    $this->error($pluginModel->getError());
                }

            } else {
                // 使用FormBuilder快速建立表单页面。
                Builder::run('Form')
                        ->setMetaTitle('新增数据')  //设置页面标题
                        ->setPostUrl(url('admin/Plugins/adminAdd', array('name' => $name, 'tab' => $tab))) // 设置表单提交地址
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
        $plugin_class = get_plugin_class($name);
        if (!class_exists($plugin_class)) {
            $this->error('插件不存在');
        } else {
            $plugin = new $plugin_class();
        }

        // 获取插件的$admin_list配置
        $admin_list = $plugin->admin_list;
        $admin = $admin_list[$tab];
        $pluginModel_object = model('Plugins://'.$name.'/'.$admin['model']);
        $param = $pluginModel_object->adminList;
        if ($param) {
            if (IS_POST) {
                $data = $this->input('post.');
                $id   = isset($data['id']) && $data['id']>0 ? $data['id']:false;
                if ($pluginModel_object->editData($data,$id)) {
                    $result = $pluginModel_object->save($data);
                } else {
                    $this->error($pluginModel_object->getError());
                }
                if ($result) {
                    $this->success('更新成功', url('admin/Plugin/adminlist', array('name' => $name, 'tab' => $tab)));
                } else {
                    $this->error('更新错误');
                }
            } else {
                Builder::run('Form')
                        ->setMetaTitle('编辑数据')  // 设置页面标题
                        ->setPostUrl(url('admin/plugin/adminedit', array('name' => $name, 'tab' => $tab))) // 设置表单提交地址
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
            $template =T('Plugins://'.$name.'@'.CONTROLLER_NAME.'/'.ACTION_NAME);
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
