<?php
// 扩展中心         
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\logic;

use eacoo\Cloud;
use eacoo\Sql;
use eacoo\EacooAccredit;

use think\Exception;
use app\admin\model\AuthRule as AuthRuleModel;
use app\common\model\Nav as NavModel;
use app\admin\model\Hooks as HooksModel;
use app\admin\model\Plugins as PluginsModel;
use app\admin\model\Modules as ModuleModel;
use app\admin\model\Theme as ThemeModel;

class Extension extends AdminLogic {

    protected $type;//类型：plugin,module
    protected $appsPath;//应用目录
    protected $appName;
    public  $appExtensionPath;//应用具体扩展目录
    public  $info;
    protected $hooksModel;
    protected $appExtensionModel;
    protected $uid;
    public $depend_type;

    function _initialize()
    {
        parent::_initialize();
        $this->type = $this->request->param('apptype');
        $this->initInfo($this->type);
        $option = [
            'type'=>$this->type,
            'eacoophp_v'=>EACOOPHP_V
        ];
        $this->cloudService = new Cloud($option);
        $this->hooksModel  = new HooksModel();
        $this->uid = is_admin_login();
    }

    /**
     * 初始化信息
     * @param  string $type [description]
     * @return [type] [description]
     * @date   2017-10-31
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function initInfo($type='',$name='')
    {
        $this->type = $type;
        switch ($type) {
            case 'module':
                $this->appsPath = APP_PATH;
                $this->depend_type =1;
                $this->appExtensionModel = new ModuleModel;
                break;
            case 'plugin':
                $this->appsPath = PLUGIN_PATH;
                $this->depend_type = 2;
                $this->appExtensionModel = new PluginsModel;
                break;
            case 'theme':
                $this->appsPath = THEME_PATH;
                $this->depend_type =3;
                $this->appExtensionModel = new ThemeModel;
                break;
            default:
                # code...
                break;
        }

        if ($name!='') {
            $this->appName = $name;
            $this->appExtensionPath = $this->appsPath . $name . DS;
        }
        
    }

    /**
     * 本地安装
     * @return [type] [description]
     * @date   2017-10-25
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function localInstall()
    {
        $file = $this->request->file('file');
        $appTmpDir = RUNTIME_PATH . $this->type . DS;
        if (!is_dir($appTmpDir))
        {
            @mkdir($appTmpDir, 0755, true);
        }
        $file = $file->rule('uniqid')->validate(['size' => 10240000, 'ext' => 'zip'])->move($appTmpDir);
        if ($file)
        {
            $tmpName   = substr($file->getFilename(), 0, stripos($file->getFilename(), '.'));
            $tmpAppDir = $this->appsPath . $tmpName . DS;
            $tmpFile   = $appTmpDir . $file->getSaveName();
            try {

                $this->cloudService->unzip($tmpName);
                @unlink($tmpFile);
                $info_file = $tmpAppDir . 'install/info.json';
                if (!is_file($info_file))
                {
                    throw new \Exception('应用信息文件不存在');
                }
                $check_res = $this->checkInfoFile($info_file);
                
                if ($check_res['code']==0) {
                    throw new \Exception($check_res['msg']);
                }
                $name = $check_res['data']['name'];
                $newAppDir = $this->appsPath . $name . DS;
                if (is_dir($newAppDir))
                {
                    throw new \Exception('该应用已存在'.$newAppDir);
                }
                $this->appName = $name;
                //重命名应用文件夹
                rename($tmpAppDir, $newAppDir);
                $return = $this->install();
                return json($return);
            } catch (\Exception $e) {
                @unlink($tmpFile);
                @rmdirs($tmpAppDir);
                return json([
                    'code'=>0,
                    'msg'=>$e->getMessage(),
                    'data'=>''
                ]);
            }
            
        }
    }

    /**
     * 在线安装，包含在线升级
     * @return [type] [description]
     * @date   2017-10-27
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function onlineInstall()
    {
        try {
            $eacoo_identification = cache('eacoo_identification');
            if (empty($eacoo_identification)) {
                throw new \Exception('请先进行身份验证', 2);
            }
            
            $uid            = $eacoo_identification['uid'];
            $access_token   = $eacoo_identification['access_token'];
            
            $name           = $this->request->param('name');
            $install_method = $this->request->param('install_method');
            $only_download  = $this->request->param('only_download',0);
            //验证身份
            $res = EacooAccredit::eacooIdentification();
            if ($res['code']!=1) {
                throw new \Exception($res['msg'], $res['code']);
            }

            $tmp_app_file = $this->cloudService->download($name,['uid'=>$uid,'token'=>$access_token]);
            if (is_file($tmp_app_file))
            {
                if ($install_method=='upgrade') {//如果是升级，先备份
                    $this->_upgradeAction($name);
                }

                $tmpName   = $name;
                $tmpAppDir = $this->appsPath . $tmpName . DS;

                $this->cloudService->unzip($tmpName);
                @unlink($tmp_app_file);
                $info_file = $tmpAppDir . 'install/info.json';
                if (!is_file($info_file))
                {
                    throw new \Exception('应用信息文件不存在',0);
                }
                $check_res = $this->checkInfoFile($info_file);
                
                if ($check_res['code']==0) {
                    throw new \Exception($check_res['msg'],0);
                }
                $name = $check_res['data']['name'];
                $newAppDir = $this->appsPath . $name . DS;
                
                if (!is_dir($newAppDir))
                {
                    @mkdirs($newAppDir);
                    rename($tmpAppDir, $newAppDir);
                }
                $this->appName = $name;
                if($only_download!=1){
                  $return = $this->install();  
                } else{
                    //仅仅下载
                    $return = ['code'=>1,'msg'=>'下载完成','data'=>[]];
                } 
                $call_url = '';
                if ($this->type=='plugin') {
                    $call_url = url('admin/Plugins/index',['from_type'=>'local']);
                } elseif ($this->type=='module') {
                    $call_url = url('admin/Modules/index',['from_type'=>'local']);
                } elseif ($this->type=='theme') {
                    $call_url = url('admin/Theme/index',['from_type'=>'local']);
                }
                
                $return['url'] = $call_url;
                $this->refresh($this->type);
                return json($return);
                
            }
        } catch (\Exception $e) {
            @unlink($tmp_app_file);
            @rmdirs($tmpAppDir);//清理缓存目录
            return json([
                    'code'=>$e->getCode(),
                    'msg'=>$e->getMessage(),
                    'data'=>''
                ]);
        }
    
        
    }
    
    /**
     * 应用安装
     * @return [type] [description]
     * @date   2017-10-26
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function install($name='',$clear = 1)
    {
        $install_method = $this->request->param('install_method','install');
        if($name==''){
            $name = $this->appName;
        } else{
            $this->appName = $name;
        }
        try {
            $fflag = $this->request->param('flag',false);
            //安装前检测
            $this->checkInstall($name);
            $info = $this->info;

            $uninstall_sql_status = true;
            if ($clear && $install_method!='upgrade') {
                $sql_file = $this->appExtensionPath.'install/uninstall.sql';
                if(is_file($sql_file)) $uninstall_sql_status = Sql::executeSqlByFile($sql_file, $info['database_prefix']);
                if (!$uninstall_sql_status) {
                    throw new \Exception('安装失败，清除旧的数据未成功');
                }
                $sql_file = $this->appExtensionPath.'install/install.sql';
                if (is_file($sql_file)) {
                    $sql_status = Sql::executeSqlByFile($sql_file, $info['database_prefix']);
                    if (!$sql_status) {
                        throw new \Exception('执行应用SQL安装语句失败');
                    }
                }
            }
            //获取默认配置
            $config = $this->getDefaultConfig($name);
            $info['config'] = !empty($config) ? json_encode($config) : '';
            if ($this->appExtensionModel->where('name',$info['name'])->find()) {
                $res = $this->appExtensionModel
                            ->where('name',$info['name'])
                            ->update([
                                'version'=>$info['version'],
                                'status'=>1
                            ]);
                $res = true;
            } else{
                $res = $this->appExtensionModel
                            ->allowField(true)
                            ->isUpdate(false)
                            ->data($info)
                            ->save();
            }

            if ($res && ($fflag!='local' || $clear)) {
                //获取依赖的钩子
                $hooks = $this->getDependentHooks();
                if (!empty($hooks)) {
                    $hooksLogic = logic('Hooks');
                    foreach ($hooks as $val) {
                        $hooksLogic->existHook($val, ['description' => $info['description']]);
                    }

                    $hooks_update = $hooksLogic->updateHooks($this->type,$name,$hooks);
                    if (!$hooks_update) {
                        $this->appExtensionModel->where('name',$name)->delete();
                        throw new \Exception('更新钩子失败,请卸载后尝试重新安装');
                    } else{
                        cache('hooks', null);
                    }
                }
  
                //设置后台菜单，升级前先清空菜单
                if ($install_method=='upgrade') {
                    $this->setAdminMenus($name,'delete');
                }
                
                $admin_menus = $this->getAdminMenusByFile($name);
                if (!empty($admin_menus) && is_array($admin_menus)) {
                    $this->addAdminMenus($admin_menus,$name);
                }
                //设置前台导航菜单
                $navigation_menus = $this->getNavigationByFile($name);
                if (!empty($navigation_menus) && is_array($navigation_menus)) {
                    logic('admin/Navigation')->addNavigationMenus($this->depend_type,$name,$navigation_menus);
                }
                $static_path = $this->appExtensionPath.'static';
                if (is_dir($static_path)) {
                    if ($this->type=='plugin') {
                        $type_path = '/plugins';
                    } elseif ($this->type=='module') {
                        $type_path = '';
                    } elseif ($this->type=='theme') {
                        $type_path = '/themes';
                    }
                    $_static_path = PUBLIC_PATH.'static'.$type_path.'/'.$name;

                    if (is_dir($_static_path)) {
                        @rmdirs($_static_path);//防止路径报错，前先清理静态资源目录
                    }
                    
                    if (!copydirs($static_path,$_static_path)) {
                        setAppLog('应用静态资源移动失败'.PUBLIC_PATH.'static'.$type_path.'/'.$name,'error');
                    } 
                }
 
            } elseif (!$clear && $fflag=='local') {
                // 重置菜单
                $this->setAdminMenus($name,'resume');
                logic('admin/Navigation')->setNavigationMenus($this->depend_type,$name,'resume');
                cache('hooks', null);
            } else {

                throw new \Exception('写入应用数据失败');
            }
            return ['code'=>1,'msg'=>'安装成功','data'=>''];
        } catch (\Exception $e) {
            setAppLog($e,'error');
            //卸载安装的数据库
            $sql_file = $this->appExtensionPath.'install/uninstall.sql';
            if(is_file($sql_file) && isset($info['database_prefix'])) Sql::executeSqlByFile($sql_file, $info['database_prefix']);
            return [
                    'code'=>0,
                    'msg'=>$e->getMessage(),
                    'data'=>''
                ];
        }
        
    }

    /**
     * 升级操作
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2018-01-18
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function _upgradeAction($name='')
    {
        if($name==''){
            $name = $this->appName;
        } else{
            $this->appName = $name;
        }

        //如果是升级，先备份
        if ($this->type=='plugin') {
            $type_path = 'plugins/';
        } elseif ($this->type=='module') {
            $type_path = '';
        } elseif ($this->type=='theme') {
            $type_path = '/themes';
        }
        $_static_path = PUBLIC_PATH.'static/'.$type_path.$name;
        
        $static_path = $this->appsPath.$name.'/static';
        if (is_dir($_static_path)) {
            @rmdirs($_static_path);//升级前先清理静态资源目录
            if(is_writable(PUBLIC_PATH.'static/'.$type_path) && is_writable($this->appsPath.$name)){
                if (!copydirs($_static_path,$static_path)) {
                    setAppLog('静态资源复制失败：'.$static_path.'复制到'.$_static_path,'error');
                } 
            }
        }
        
        $newAppDir = $this->appsPath . $name . DS;
        //备份路径
        $backup_path = ROOT_PATH.'data/backups/'.$this->type.'s/'. $name.'-'.date('YmdHis') . DS;
        mkdirs($backup_path);
        if(rename($newAppDir, $backup_path)){
            @unlink($newAppDir);
        }
            
        return true;
    }


    /**
     * 检测安装
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-10-26
     * @author 心云间、凝听 <981248356@qq.com>
     */
    private function checkInstall($name='')
    {
        if($name=='') $name = $this->appName;
        $this->appExtensionPath = $this->appsPath . $name . DS;
        $info_file = $this->appExtensionPath . 'install/info.json';
        $result = $this->checkInfoFile($info_file);
        $info = $result['data'];
        if ($this->type=='plugin') {
            $app_class = get_plugin_class($name);
            if (!class_exists($app_class)) {
                throw new \Exception('应用实例化文件损坏');
            } else{
                $app_class = new $app_class;
                if(!$app_class->install()) {
                    throw new \Exception('应用预安装失败!原因：'. $app_class->getError());
                }
            }
        }

        if (!empty($info['dependences'])) {
            $result = $this->checkDependence($info['dependences']);
            if (!$result) {
                return false;
            }
        }

        $static_path = $this->appExtensionPath.'static';
        if (is_dir($static_path)) {
            if ($this->type=='plugin') {
                $type_path = '/plugins';
            } elseif ($this->type=='module') {
                $type_path = '';
            } elseif ($this->type=='theme') {
                $type_path = '/themes';
            }
            if(!is_writable(PUBLIC_PATH.'static'.$type_path) || !is_writable($static_path)){
                $error_msg = '';
                if (!is_writable(PUBLIC_PATH.'static'.$type_path)) {
                    $error_msg.='public/static'.$type_path;
                }
                if (!is_writable($static_path)) {
                    $error_msg.=','.$static_path;
                }
                throw new \Exception($error_msg.'目录操作权限不足');
            }
        }
    }

    /**
     * 检测信息文件
     * @param  string $name 信息
     * @return [type] [description]
     * @date   2017-10-26
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function checkInfoFile($info_file='')
    {
        if($info_file=='') $info_file = $this->appExtensionPath . 'install/info.json';

        if (!is_file($info_file))
        {
            throw new \Exception('应用信息文件不存在或文件权限不足');
        }
        $info_check_keys = ['name', 'title', 'description', 'author', 'version'];
        $app_info = $this->getInfoByFile($info_file);
        foreach ($info_check_keys as $value) {
            if (!array_key_exists($value, $app_info)) {
                throw new \Exception('应用信息缺失');
            }

        }
        return ['code'=>1,'msg'=>'ok','data'=>$app_info];
        
    }

    /**
     * 获取插件依赖的钩子
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-09-18
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getDependentHooks($name='')
    {
        if($name=='') $name = $this->appName;
        if ($name=='' || !$name) {
            return false;
        }
        if ($this->type=='plugin') {
            $hook_class = get_plugin_class($name);//获取插件名
            if (!class_exists($hook_class)) {
                $this->error = "未实现{$name}插件的入口文件";
                return false;
            }
            
        } elseif ($this->type=='module') {
            $hook_class = "\\app\\" . $name . "\\widget\\Hooks";
            if (!class_exists($hook_class)) {
                $this->error = "未实现{$name}模块的钩子文件";
                return false;
            }
        } 
        $dependent_hooks = [];
        if (isset($hook_class)) {
            $hook_obj = new $hook_class;
            $dependent_hooks = $hook_obj->hooks;
        }
        
        return $dependent_hooks;
    }

    /**
     * 模块依赖性检查
     * @param  [type] $dependences 模块
     * @return [type] [description]
     * @date   2017-09-15
     * @author 心云间、凝听 <981248356@qq.com>
     */
    private function checkDependence($dependences) {
        if (is_array($dependences)) {
            $core_version = !empty($dependences['core']) ? $dependences['core']:'';//依赖的核心版本
            $modules = !empty($dependences['modules']) ? $dependences['modules']:'';//依赖的模块
            $plugins = !empty($dependences['plugins']) ? $dependences['plugins']:'';//依赖的插件

            if ($core_version!='') {
                $eacoo_version = explode('.', EACOOPHP_V);
                $need_version   = explode('.', $core_version);
                $meet_core_version = false;
                $compare_version0 = $eacoo_version[0] - $need_version[0];
                $compare_version1 = $eacoo_version[1] - $need_version[1];
                $compare_version2 = $eacoo_version[2] - $need_version[2];
                if ($compare_version0 >= 0) {
                    if ($compare_version1 >= 0) {
                        if ($compare_version2 >= 0) {
                            $meet_core_version = true;
                        } elseif ($compare_version1>0) {
                            $meet_core_version = true;
                        }
                    }
                }
                if ($meet_core_version==false) {
                    throw new \Exception('EacooPHP版本不得低于v'.$core_version);
                }
                
            }

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
                        throw new \Exception('该应用依赖'.$key.'模块');
                    }

                    $module_version = explode('.', $module_info['version']);
                    $need_version   = explode('.', $val);

                    if (($module_version[0] - $need_version[0]) >= 0) {
                        if (($module_version[1] - $need_version[1]) >= 0) {
                            if (($module_version[2] - $need_version[2]) >= 0) {
                                continue;
                            }
                        }
                    }
                    throw new \Exception($module_info['title'].'模块版本不得低于v'.$val);
                }
            }
            //插件
            if (!empty($plugins) && is_array($plugins)) {
                foreach ($plugins as $key => $val) {
                    $map = [
                        'name'=>$key,
                    ];
                    $plugins_info = PluginsModel::where($map)->field('version,title')->find();
                    if (!$plugins_info) {
                        throw new \Exception('该模块依赖'.$key.'插件');
                    }
                    //版本号检查
                    $plugin_version = explode('.', $plugins_info['version']);
                    $need_version   = explode('.', $val);

                    if (($plugin_version[0] - $need_version[0]) >= 0) {
                        if (($plugin_version[1] - $need_version[1]) >= 0) {
                            if (($plugin_version[2] - $need_version[2]) >= 0) {
                                continue;
                            }
                        }
                    }
                    throw new \Exception($plugins_info['title'].'插件版本不得低于v'.$val);
                }

            }
            
            return true;
        }
    }

    /**
     * 文件获取信息
     * @param  [type] $info_file 
     * @return [type] [description]
     * @date   2017-09-15
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getInfoByFile($info_file = '')
    {
        if($info_file=='') $info_file = $this->appExtensionPath . 'install/info.json';

        if (is_file($info_file)) {
            $info = file_get_contents($info_file);
            $this->info = json_decode($info,true);
            
            return $this->info;
        } else {
            return [];
        }

    }

    /**
     * 文件获取安装信息的后台菜单
     * @param  string $name 模块名
     * @return [type] [description]
     * @date   2017-09-16
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getAdminMenusByFile($name='')
    {
        if($name=='') $name = $this->appName;
        $file = $this->appExtensionPath . 'install/menus.php';

        if (is_file($file)) {

            $menus = include $file;

            return !empty($menus['admin_menus']) ? $menus['admin_menus'] : false;

        } else {
            return false;
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
    public function addAdminMenus($data = [], $flag_name = '', $pid = 0,$position='')
    {
        if (!empty($data) && is_array($data) && $flag_name!='') {
            $authRuleModel = new AuthRuleModel;
            foreach ($data as $key => $menu) {
                $pid = isset($menu['pid']) ? (int)$menu['pid'] : $pid;

                $menu['depend_type'] = $this->depend_type;
                $menu['depend_flag'] = $flag_name;
                if ($position=='') {
                    if ($this->depend_type==1) {
                        $position = $flag_name;
                    } else{
                        $position = 'admin';
                    }
                }
                $menu['position'] = $position;
                $menu['pid']    = $pid;
                $menu['sort']   = isset($menu['sort']) ? $menu['sort'] : 99;
                if ($authRuleModel->where(['name'=>$menu['name'],'depend_type'=>$this->depend_type,'depend_flag'=>$flag_name])->find()) {
                    $authRuleModel->where(['name'=>$menu['name'],'depend_type'=>$this->depend_type,'depend_flag'=>$flag_name])->update(['status'=>1]);
                } else{
                    $authRuleModel->allowField(true)->isUpdate(false)->data($menu)->save();
                }
                
                //添加子菜单
                if (!empty($menu['sub_menu'])) {
                    $this->addAdminMenus($menu['sub_menu'], $flag_name, $authRuleModel->id);
                }
            }
            cache('admin_sidebar_menus_'.$this->uid,null);//清空后台菜单缓存
            return true;
        }
        return false;
    }

    /**
     * 设置后台菜单
     * @param  string $flag_name [description]
     * @param  boolean $delete [description]
     * @return [type] [description]
     * @date   2017-11-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function setAdminMenus($flag_name='' ,$status='')
    {
        if($flag_name=='') $flag_name = $this->appName;
        if ($flag_name!='') {
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
                'depend_type'=>$this->depend_type,
                'depend_flag'=>$flag_name
            ];
            if ($status!='delete') {
                $res = AuthRuleModel::where($map)->update(['status'=>$state]);
            } elseif($status=='delete') {
                $res = AuthRuleModel::where($map)->delete();
            }
            
            if (false === $res) {
                return false;
            } else{
                cache('admin_sidebar_menus_'.$this->uid,null);
                return true;
            }
        }
        return false;
    }

    /**
     * 文件获取安装的前台导航
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-09-16
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getNavigationByFile($name='')
    {
        if($name=='') $name = $this->appName;
        $file = $this->appExtensionPath . 'install/menus.php';

        if (is_file($file)) {

            $menus = include $file;

            return !empty($menus['navigation']) ? $menus['navigation'] : false;

        } else {
            return false;
        }
    }

    /**
     * 文件获取安装的后台选项
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-09-16
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getOptionsByFile($name ='')
    {
        if($name=='') $name = $this->appName;
        $file = $this->appExtensionPath . 'install/options.php';

        if (is_file($file)) {
            $module_menus = include $file;
            return $module_menus;

        } else {
            return false;
        }
    }

    /**
     * 获取插件默认配置
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-09-18
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getDefaultConfig($name ='')
    {
        if($name=='') $name = $this->appName;

        $config = [];
        if ($name) {
            $options = $this->getOptionsByFile($name);
            if (!empty($options) && is_array($options)) {
                $config = [];
                foreach ($options as $key => $value) {
                    if ($value['type'] == 'group') {
                        foreach ($value['options'] as $gkey => $gvalue) {
                            if ($gvalue['type']=='group') {
                                foreach ($gvalue['options'] as $ikey => $ivalue) {
                                    $config[$key][$gkey][$ikey] = $ivalue['value'];
                                }
                            } else{
                                $config[$key][$gkey] = $gvalue['value'];
                            }
                            
                        }
                    } else {
                        $config[$key] = $options[$key]['value'];
                    }
                }
            }
        }
        return $config;
    }

    /**
     * 本地应用
     * @return [type] [description]
     * @date   2017-09-21
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function localApps($type='')
    {
        $this->initInfo($type);

        $list = cache('local_'.$type.'s_list');
        if (empty($list) || !$list) {
            $dirs = array_map('basename', glob($this->appsPath.'*', GLOB_ONLYDIR));
            if ($dirs == false || !file_exists($this->appsPath)) {
                $this->error = '应用目录不可读或者不存在';
                return false;
            } else{
                if (!empty($dirs)) {
                    foreach ($dirs as $name) {
                        if (in_array($name, ['admin','install','common','functions'])) {
                            continue;
                        }
                        $this->appExtensionPath = $this->appsPath . $name . DS;
                        $info_file = $this->appExtensionPath . 'install/info.json';
                        
                        try {
                            $info      = $this->getInfoByFile($info_file);
                            $info_flag = $this->checkInfoFile($info_file);
                            if (!$info || !$info_flag) {
                                throw new \Exception('应用'.$name.'的信息缺失！', 0);
                            }
                        } catch (\Exception $e) {
                            setAppLog($e->getMessage(),'Extension','info');
                            continue;
                        }
                        
                        if (!$this->appExtensionModel->where('name',$name)->find()) $info['status']=3;

                        $list[$name] = $info;
                    }
                    cache('local_'.$type.'s_list',$list,600);
                } 
            }
        }
        
        return $list;
    }

    /**
     * 刷新缓存
     * @param  string $type [description]
     * @return [type] [description]
     * @date   2018-03-05
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function refresh($type='')
    {
        $page_num = 3;
        $paged = 1;
        for ($i=0; $i < 3; $i++) { 
            $paged = $i+1;
            cache('eacoo_appstore_'.$type.'s_'.$paged,null);
        }
        
        cache('local_'.$type.'s_list',null);
        return true;
    }
    /**
     * 获取logo
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-10-31
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getLogo($name='', $type='')
    {
        $file_ext = ['png','jpg','jpeg','svg','gif'];
        $logo = '';
        if ($type=='plugin') {
            $type_path = 'plugins/';
        } elseif ($type=='module') {
            $type_path = '';
        }elseif ($type=='theme') {
            $type_path = 'themes/';
        }
        foreach ($file_ext as $key => $ext) {
            $tmp_logo_dir = 'runtime/images/logos/'.$type_path;
            if (!is_dir($tmp_logo_dir))
            {
                @mkdir($tmp_logo_dir, 0755, true);
            }
            $tmp_logo = $tmp_logo_dir.$name.'.'.$ext;
            $tmp_logo_file = PUBLIC_PATH.$tmp_logo;
            if (is_file($tmp_logo_file)) {
                return '/'.$tmp_logo;
                break;
            } else{
                //从public目录找
                $logo = 'static/'.$type_path.$name.'/logo.'.$ext;
                $logo_file = PUBLIC_PATH.'static/'.$type_path.$name.'/logo.'.$ext;
                if (is_file($logo)) {
                    if (is_writable(PUBLIC_PATH.$logo)) {
                        if (copy($logo_file,PUBLIC_PATH.$tmp_logo)){
                            $logo = $tmp_logo;
                        }
                    }
                    
                    return '/'.$logo;
                    break;
                } else{
                    //从原目录中找
                    if ($type=='plugin') {
                        $original_logo_file = PLUGIN_PATH.$name.'/static/logo.'.$ext;
                    } elseif ($type=='module') {
                        $original_logo_file = APP_PATH.$name.'/static/logo.'.$ext;
                    } elseif ($type=='theme') {
                        $original_logo_file = THEME_PATH.$name.'/cover.'.$ext;
                    }
                    if (is_file($original_logo_file)) {
                        if (is_writable(PUBLIC_PATH.$tmp_logo_dir)) {
                            if (copy($original_logo_file,PUBLIC_PATH.$tmp_logo)){
                                return '/'.$tmp_logo;
                            }
                        }
                        
                    }
                    
                }
            }   
            
        }
        return false;
    }
}
