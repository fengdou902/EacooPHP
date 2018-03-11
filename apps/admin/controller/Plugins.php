<?php
// 插件后台管理控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\Plugins as PluginsModel;
use app\admin\model\Hooks;
use app\admin\model\AuthRule;

use eacoo\Sql;
use eacoo\Cloud;

class Plugins extends Admin {

    protected $pluginModel;
    protected $hooksModel;

    function _initialize()
    {
        parent::_initialize();
        
        $this->pluginModel = new PluginsModel();
        $this->hooksModel  = new Hooks();
    }

    /**
     * 插件列表
     * @param  string $from_type 来源类型
     * @return [type] [description]
     * @date   2017-09-21
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index($from_type = 'oneline') {
        if (IS_AJAX) {
            $paged = input('param.paged',1);
            // 获取所有插件信息
            //$paged = input('get.p',1);
            if ($from_type == 'local') {
                //本地插件
                $data_list = $this->pluginModel->getAll();
                $data_list = !empty($data_list) ? array_values($data_list):[];
                $total = 0;
            } elseif($from_type == 'oneline'){
                //线上插件
                list($data_list,$total) = $this->getCloudAppstore($paged);
                
            }
            $return = [
                'code'=>1,
                'msg'=>'成功获取应用',
                'data'=>$data_list,
                'page_content'=>logic('admin/AppStore')->getPaginationHtml($paged,$total)
            ];
            return json($return);
        } else{
            $this->assign('page_config',['self'=>'<a href="'.url('admin/Hook/index').'" class="btn btn-primary btn-sm mr-10">钩子管理</a>']);
            $tab_list = [
                'local'=>['title'=>'本地插件','href'=>url('index',['from_type'=>'local']),'extra_attr'=>''],
                'oneline'=>['title'=>'插件市场','href'=>url('index',['from_type'=>'oneline']),'extra_attr'=>''],
            ];
            $this->assign('tab_list',$tab_list);
            if ($from_type == 'local') {
                $meta_title = '本地插件';
                
            } elseif($from_type == 'oneline'){
                $meta_title = '插件市场';
                
            }
            $this->assign('from_type',$from_type);
            $this->assign('meta_title',$meta_title);
            return $this->fetch('extension/plugins');
        } 
        
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
            $config = input('post.config/a');
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
                $this->error("插件{$plugin['name']}无法实例化,");
            }
            $plugin_obj = new $plugin_class;
            $addon['plugin_path']    = $plugin_obj->pluginPath;
            $plugin['custom_config'] = $plugin_obj->custom_config;
            $db_config = $plugin['config'];
            $extensionObj = new Extension;
            $extensionObj->initInfo('plugin',$plugin['name']);

            $options   = $extensionObj->getOptionsByFile();
            $db_config = json_decode($db_config, true);
            
            //构建表单配置信息
            $options = logic('common/Config')->buildFormByFiled($options,$db_config,true);

            if (!empty($plugin['custom_config'])) {
                $this->assign('data', $plugin);
                $this->assign('form_items', $options);
                $this->assign('custom_config', $this->fetch($plugin['plugin_path'].$plugin['custom_config']));
                return $this->fetch($plugin['plugin_path'].$plugin['custom_config']);
            } else {
                return builder('Form')
                        ->setMetaTitle('设置-'.$plugin['title'])  //设置页面标题
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
     * 安装之前
     */
    public function installBefore($name='') {
        $this->assign('meta_title','准备安装插件');

        if ($this->pluginModel->where('name',$name)->find()) {
            $clear = 0;
        } else{
            $clear = 1;
        }
        $info=['name'=>$name,'clear'=>$clear];
        $fieldList = [
                ['name'=>'name','type'=>'hidden','title'=>'名称'],
                ['name'=>'clear','type'=>'radio','title'=>'清除数据：','description'=>'是否清除数据，默认否','options'=>[1=> '是', 0=> '否']],
            ];
        foreach ($fieldList as $key => &$val) {
            if ($val['name']!='self_html') {
                $val['value']=isset($info[$val['name']])? $info[$val['name']]:'';
            }
            
        }
        $this->assign('fieldList',$fieldList);
        $this->assign('post_url',url('install'));
        return $this->fetch('extension/install_before');
    }

    /**
     * 安装插件
     * @param  string $name 插件名
     * @param  boolean $clear 是否清除历史数据
     * @return [type] [description]
     * @date   2017-09-18
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function install($name='',$clear = 1) {
        $extensionObj = new Extension;
        $extensionObj->initInfo('plugin');
        $result = $extensionObj->install($name,$clear);
        if ($result['code']==1) {
            $this->success('安装成功', url('index',['from_type'=>'local']));
        } else{
            $this->error($result['msg'], '');
        }
        
    }

    /**
     * 卸载插件之前
     */
    public function uninstallBefore($id) {
        $this->assign('meta_title','准备卸载插件');
        $info=['id'=>$id];
        $fieldList = [
                ['name'=>'id','type'=>'hidden','title'=>'ID'],
                ['name'=>'clear','type'=>'radio','title'=>'清除数据：','description'=>'是否清除数据，默认否','options'=>[1=> '是', 0=> '否（等于禁用）']],
            ];
        foreach ($fieldList as $key => &$val) {
            if ($val['name']!='self_html') {
                $val['value']=isset($info[$val['name']])? $info[$val['name']]:'';
            }
            
        }
        $this->assign('fieldList',$fieldList);
        $post_url = url('uninstall');
        $this->assign('post_url',$post_url);
        return $this->fetch('extension/uninstall');
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
        try {
            if ($id>0) {

                $plugin_info = $this->pluginModel->where('id',$id)->field('name')->find();
                $name        = $plugin_info['name'];
                $_static_path = PUBLIC_PATH.'static/plugins/'.$name;
                if (is_dir($_static_path)) {
                    if(!is_writable(PUBLIC_PATH.'static/plugins') || !is_writable(PLUGIN_PATH.$name)){
                        $error_msg = '';
                        if (!is_writable(PUBLIC_PATH.'static/plugins')) {
                            $error_msg.=','.PUBLIC_PATH.'static/plugins';
                        }
                        if (!is_writable(PLUGIN_PATH.$name)) {
                            $error_msg.=','.PLUGIN_PATH.$name;
                        }
                        throw new \Exception($error_msg.'目录写入权限不足',0);
                    }
                }
                $hooks_update = $this->hooksModel->removeHooks($name);
                if ($hooks_update === false) {
                    throw new \Exception("卸载插件所挂载的钩子数据失败", 0);
                }
                cache('hooks', null);
                if ($clear) {
                    $result = PluginsModel::where('id',$id)->delete();
                } else{
                    $result = PluginsModel::where('id',$id)->update(['status'=>-1]);
                }
                if ($result) {
                    $extensionObj = new Extension;
                    $extensionObj->initInfo('plugin',$name);
                    // 删除后台菜单
                    $extensionObj->removeAdminMenus($name,$clear);
                    if ($clear) {
                        // 卸载数据库
                        $sql_file = PLUGIN_PATH.$name.'/install/uninstall.sql';
                        if (is_file($sql_file)) {
                            $info       = $extensionObj->getInfoByFile();
                            $sql_status = Sql::executeSqlByFile($sql_file, $info['database_prefix']);
                            if (!$sql_status) {
                                throw new \Exception("执行插件SQL卸载语句失败", 0);
                            }
                        }
                    }

                    if (is_dir($_static_path)) {
                        $static_path = PLUGIN_PATH.$name.'/static';
                        if (!rename($_static_path,$static_path)) {
                            trace('插件静态资源移动失败：'.'public/static/plugins/'.$name.'->'.$static_path,'error');
                        }
                    }
                    
                }
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('卸载成功',url('index',['from_type'=>'local']));

    }

    /**
     * 刷新缓存
     * @return [type] [description]
     * @date   2017-10-30
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function refresh()
    {
        Extension::refresh('plugin');
        $this->success('操作成功','');
    }

    /**
     * 删除
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-11-07
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function delPlugin($name='')
    {
        if ($name) {
            if (!is_writable(PLUGIN_PATH.$name)) {
                $this->error('目录权限不足，请手动删除目录');
            }
            @rmdirs(PLUGIN_PATH.$name);
            Extension::refresh('plugin');
            $this->success('删除插件成功');
        }
        $this->error('删除插件失败');
    }

    /**
     * 设置一条或者多条数据的状态
     */
    public function setStatus($model = CONTROLLER_NAME,$script=false){
        $ids = $this->request->param('ids');
        $status = $this->request->param('status');

        if (!empty($ids)) {
            $extensionObj = new Extension;
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    $info = model($model)->where('id',$id)->field('name')->find();
                    $extensionObj->initInfo('plugin',$info['name']);
                    $extensionObj->switchAdminMenus($info['name'],$status);
                }
            } else {
                $info = model($model)->where('id',$ids)->field('name')->find();

                $extensionObj->initInfo('plugin',$info['name']);
                $extensionObj->switchAdminMenus($info['name'],$status);
                
            }
        }
        
        parent::setStatus($model);
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
     * 获取插件市场数据
     * @return [type] [description]
     * @date   2017-09-21
     * @author 心云间、凝听 <981248356@qq.com>
     */
    private function getCloudAppstore($paged = 1)
    {
        $total = 20;
        $store_data = cache('eacoo_appstore_plugins_'.$paged);
        if (empty($store_data) || !$store_data) {
            $url        = config('eacoo_api_url').'/api/appstore/plugins';
            $params = [
                'paged'=>$paged,
                'eacoophp_version'=>EACOOPHP_V
            ];
            $result = curl_post($url,$params);
            $result = json_decode($result,true);
            $store_data = $result['data'];
            $total = 20;
            cache('eacoo_appstore_plugins_'.$paged,$store_data,3600);
            cache('eacoo_appstore_plugins_info',['total'=>$total],3600);
        }
        if (!empty($store_data)) {
            $extensionObj = new Extension();
            $local_plugins = $extensionObj->localApps('plugin');
            foreach ($store_data as $key => &$val) {
                $val['from_type']    = 'oneline';
                $val['publish_time'] = friendly_date($val['publish_time']);
                $val['right_button'] = '<button class="btn btn-primary btn-sm app-online-install" data-name="'.$val['name'].'" data-type="plugin" href="javascript:void(0);" data-install-method="install">现在安装</button> ';
                if (!empty($local_plugins)) {
                    foreach ($local_plugins as $k => $row) {
                        if ($row['name']==$val['name']) {
                            if ($row['version']<$val['version']) {
                                $val['right_button'] = '<a class="btn btn-success btn-sm app-online-install" data-name="'.$val['name'].'" data-type="plugin" href="javascript:void(0);" data-install-method="upgrade">升级</a> ';
                            } elseif(isset($row['status']) && $row['status']==3){
                                $val['right_button'] = '<a class="btn btn-default btn-sm" href="'.url('index',['from_type'=>'local']).'">已下载</a> ';
                            } else{
                                $val['right_button'] = '<a class="btn btn-default btn-sm" href="'.url('index',['from_type'=>'local']).'">已安装</a> ';
                            }
                            
                        }
                    }
                }
                $val['status'] = '<i class="fa fa-ban">可安装</i>';

                //$val['right_button'] .= '<a class="btn btn-info btn-sm" href="http://www.eacoo123.com" target="_blank">更多详情</a> ';
            }
        }
        return [$store_data,$total];
    }
}
