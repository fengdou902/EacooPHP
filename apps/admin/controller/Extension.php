<?php
// 扩展中心         
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use eacoo\Cloud;
use eacoo\Sql;
use eacoo\EacooAccredit;

use think\Exception;
use app\admin\model\AuthRule;
use app\admin\model\Hooks;
use app\admin\model\Plugins as PluginsModel;
use app\admin\model\Modules as ModuleModel;

class Extension extends Admin {

	protected $type;//类型：plugin,module
	protected $appsPath;//应用目录
	protected $appName;
	protected $appExtensionPath;//应用具体扩展目录
	protected $info;
	protected $hooksModel;
	protected $appExtensionModel;
    protected $uid;

	function _initialize()
	{
		parent::_initialize();
		$this->type = $this->request->param('type');
		$this->initInfo($this->type);
		$option = [
			'type'=>$this->type,
		];
		$this->cloudService = new Cloud($option);
		$this->hooksModel  = new Hooks();
        $this->uid = is_login();
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
            case 'plugin':
                $this->appsPath = PLUGIN_PATH;
                $this->depend_type = 2;
                $this->appExtensionModel = new PluginsModel;
                break;
            case 'module':
                $this->appsPath = APP_PATH;
                $this->depend_type =1;
                $this->appExtensionModel = new ModuleModel;
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
                    throw new Exception('应用信息文件不存在');
                }
                $check_res = $this->checkInfoFile($info_file);
                
                if ($check_res['code']==0) {
                	throw new Exception($check_res['msg']);
                }
                $name = $check_res['data']['name'];
                $newAppDir = $this->appsPath . $name . DS;
                if (is_dir($newAppDir))
                {
                    throw new Exception('该应用已存在'.$newAppDir);
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
     * 在线安装
     * @return [type] [description]
     * @date   2017-10-27
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function onlineInstall()
    {
        try {
            $name = $this->request->param('name');
            $extend = ['uid'=>1,'token'=>'fasfar3q9we9jfwejfq39ur9j'];
            //验证身份
            $res = EacooAccredit::eacooIdentification();
            if ($res['code']!=1) {
                throw new \Exception($res['msg'], $res['code']);
            }
            $tmp_app_file = $this->cloudService->download($name,$extend);
            if (is_file($tmp_app_file))
            {
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
                    //重命名应用文件夹
                    rename($tmpAppDir, $newAppDir);
                }
                $this->appName = $name;

                $return = $this->install();
                $call_url = '';
                if ($this->type=='plugin') {
                    $call_url = url('admin/Plugins/index',['from_type'=>'local']);
                } elseif ($this->type=='module') {
                    $call_url = url('admin/Modules/index',['from_type'=>'local']);
                }
                
                $return['url']=$call_url;
                return json($return);
                
            }
        } catch (\Exception $e) {
            @unlink($tmp_app_file);
            @rmdirs($tmpAppDir);
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
		if($name==''){
            $name = $this->appName;
        } else{
            $this->appName = $name;
        }
		try {
			//预安装检测
	        $this->checkInstall();

	        $info = $this->info;

	        $hooks = $this->getDependentHooks();//获取依赖钩子
	        // 检查该插件所需的钩子
	        if (!empty($hooks)) {
	            foreach ($hooks as $val) {
	                $this->hooksModel->existHook($val, ['description' => $info['description']]);
	            }
	        }
            // 安装数据库
            $uninstall_sql_status = true;
            // 清除旧数据
            if ($clear) {
                $sql_file = $this->appExtensionPath.'/install/uninstall.sql';
                if(is_file($sql_file)) $uninstall_sql_status = Sql::executeSqlByFile($sql_file, $info['database_prefix']);
            }

            // 安装新数据表
            if (!$uninstall_sql_status) {
             $this->error('安装失败');
            }
            
	        // 安装数据库
	        $sql_file = $this->appExtensionPath.'/install/install.sql';
	        if (is_file($sql_file)) {
	            $sql_status = Sql::executeSqlByFile($sql_file, $info['database_prefix']);
	            if (!$sql_status) {
	            	throw new Exception('执行应用SQL安装语句失败');
	            }
	        }

	        $config = $this->getDefaultConfig($name);//获取文件中的默认配置值
	        $info['config'] = !empty($config) ? json_encode($config) : '';
	        if ($this->appExtensionModel->allowField(true)->isUpdate(false)->data($info)->save()) {
                if ($this->type=='plugin') {
                    //更新钩子
                    $hooks_update = $this->hooksModel->updateHooks($name);
                    if (!$hooks_update) {
                        $this->appExtensionModel->where('name',$name)->delete();
                        throw new Exception('更新钩子失败,请卸载后尝试重新安装');
                    } else{
                        cache('hooks', null);
                    } 
                    
                } 
                //后台菜单权限入库
                $admin_menus = $this->getAdminMenusByFile($name);
                if (!empty($admin_menus) && is_array($admin_menus)) {
                    $this->addAdminMenus($admin_menus,$name);
                }
                //静态资源处理
                $static_path = $this->appExtensionPath.'/static';
                if (is_dir($static_path)) {
                    if ($this->type=='plugin') {
                        $type_path = '/plugins';
                    } elseif ($this->type=='module') {
                        $type_path = '';
                    }
                    if(is_writable(PUBLIC_PATH.'static'.$type_path) && is_writable($static_path)){
                        if (!rename($static_path,PUBLIC_PATH.'static'.$type_path.'/'.$name)) {
                            trace('应用静态资源移动失败','error');
                        } 
                    } else{
                        $this->appExtensionModel->where('name',$name)->update(['status'=>0]);
                        throw new Exception('安装失败，原因：应用静态资源目录不可写');
                    }
                }

                return ['code'=>1,'msg'=>'安装成功','data'=>''];
	            
	        } else {

	            throw new Exception('写入插件数据失败');
	        }
		} catch (\Exception $e) {
			return [
                	'code'=>0,
                	'msg'=>$e->getMessage(),
                	'data'=>''
                ];
		}
        
	}

    /**
     * 会员信息
     * @return [type] [description]
     * @date   2017-11-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function userinfo()
    {
        $eacoo_identification = cache('eacoo_identification');
        if (IS_POST) {
            try {
                $from = $this->request->param('from');
                if ($from=='iframe') {
                    $return = EacooAccredit::eacooIdentification();
                    if ($return['code']!=1) {
                        throw new \Exception($return['msg'], $return['code']);
                    }

                } elseif ($from=='login') {
                    $identification = $this->request->param('account');
                    $password = $this->request->param('password');
                    $result = curl_request(config('eacoo_api_url').'/api/token',['identification'=>$identification,'password'=>$password]);
                    $return = json_decode($result['content'],true);
                    if ($return['code']==1) {
                        $eacoo_identification = $return['data'];
                        cache('eacoo_identification',$eacoo_identification,$eacoo_identification['expired']);
                    } else{
                        throw new \Exception($return['msg'], 2);
                    }
                    
                } elseif ($from=='logout') {
                    $uid = $eacoo_identification['uid'];
                    $access_token = $eacoo_identification['access_token'];
                    $result = curl_request(config('eacoo_api_url').'/api/token/logout',['uid'=>$uid,'token'=>$access_token]);
                    $return = json_decode($result['content'],true);
                    if ($return['code']==1) {
                        cache('eacoo_identification',null);
                    }
                }
                return json($return);
            } catch (\Exception $e) {
                cache('eacoo_identification',null);
                return json([
                    'code'=>$e->getCode(),
                    'msg'=>$e->getMessage(),
                    'data'=>[],
                ]);
            }
             
        } else{
            $this->assign('eacoo_identification',$eacoo_identification);//dump($eacoo_identification);
            $this->assign('eacoo_userinfo',$eacoo_identification['userinfo']);
            return $this->fetch('extension/userinfo');
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
    public function addAdminMenus($data = [], $flag_name = '', $pid = 0)
    {
        if (!empty($data) && is_array($data) && $flag_name!='') {
            $authRuleModel = new AuthRule;
            foreach ($data as $key => $menu) {
                $pid = isset($menu['pid']) ? (int)$menu['pid'] : $pid;

                $menu['depend_type'] = $this->depend_type;//2代表plugin
                $menu['depend_flag'] = $flag_name;
                $menu['pid']    = $pid;
                $menu['sort']   = isset($menu['sort']) ? $menu['sort'] : 99;
                $authRuleModel->allowField(true)->isUpdate(false)->data($menu)->save();
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
     * 移除后台菜单
     * @param  string $flag_name 模块名
     * @param  boolean $delete 是否删除数据
     * @return [type] [description]
     * @date   2017-09-18
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function removeAdminMenus($flag_name='' ,$delete=true)
    {
        if ($flag_name!='') {
            $map = [
                'depend_type'=>$this->depend_type,
                'depend_flag'=>$flag_name
            ];
            if ($delete) {
                $res = AuthRule::where($map)->delete();
            } else{
                $res = AuthRule::where($map)->update(['status'=>0]);
            }
            if (false === $res) {
                //$this->error('菜单删除失败，请重新卸载');
                return false;
            } else{
                cache('admin_sidebar_menus_'.$this->uid,null);//清空后台菜单缓存
                return true;
            }
        }
        return false;
    }

    /**
     * 启用和禁用后台菜单
     * @param  string $flag_name [description]
     * @param  boolean $delete [description]
     * @return [type] [description]
     * @date   2017-11-02
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function switchAdminMenus($flag_name='' ,$status='')
    {
        if($flag_name=='') $flag_name = $this->appName;
        if ($flag_name!='') {
            if ($status!='resume' && $status!='forbid') return false;
            $map = [
                'depend_type'=>$this->depend_type,
                'depend_flag'=>$flag_name
            ];
            $state = 0;
            if ($status=='resume') {
                $state = 1;
            } elseif ($status=='forbid') {
                $state = 0;
            }
            $menus = AuthRule::where($map)->update(['status'=>$state]);
            
            if (false === $menus) {
                return false;
            } else{
                cache('admin_sidebar_menus_'.$this->uid,null);//清空后台菜单缓存
                return true;
            }
        }
        return false;
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
    	try {
    		$this->appExtensionPath = $this->appsPath . $name . DS;
			$info_file = $this->appExtensionPath . 'install/info.json';
    		if (!is_file($info_file))
            {
                throw new Exception('应用信息文件不存在');
            }
            $info = $this->getInfoByFile();
	        // 检测信息的正确性
	        if (!$info){ 
	            throw new Exception('应用信息缺失');
	        }

            if ($this->type=='plugin') {
                $app_class = get_plugin_class($name);
                if (!class_exists($app_class)) {
                    throw new Exception('应用实例化文件损坏');
                } else{
                    $app_class = new $app_class;
                    // 插件预安装
                    if(!$app_class->install()) {
                        throw new Exception('应用预安装失败!原因：'. $app_class->getError());
                    }
                }
            }

	        // 依赖性检查
	        if (!empty($info['dependences'])) {
	            $result = $this->checkDependence($info['dependences']);
	            if (!$result) {
	                return false;
	            }
	        }

            $flag = $this->checkInfoFile($info_file);//检测安装信息

    	} catch (\Exception $e) {
    		return json([
                	'code'=>0,
                	'msg'=>$e->getMessage(),
                	'data'=>''
                ]);
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
    	try {
    		
    		if (!is_file($info_file))
	        {
	            throw new Exception('应用信息文件不存在');
	        }
	        $info_check_keys = ['name', 'title', 'description', 'author', 'version'];
	        foreach ($info_check_keys as $value) {
	            if (!array_key_exists($value, $this->getInfoByFile($info_file))) {
	                throw new Exception('应用信息缺失');
	            }

	        }
	        return ['code'=>1,'msg'=>'ok','data'=>$this->getInfoByFile($info_file)];
    	} catch (\Exception $e) {
    		return [
    			'code'=>0,
    			'msg'=>$e->getMessage(),
    			'data'=>''
    		];
    	}
    	
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
        $plugin_class = get_plugin_class($name);//获取插件名
        if (!class_exists($plugin_class)) {
            $this->error = "未实现{$name}插件的入口文件";
            return false;
        }
        $plugin_obj = new $plugin_class;
        // $info = self::getInfoByFile($name);
        // $dependent_hooks = !empty($info['dependences']['hooks']) ? $info['dependences']['hooks']:'';
        $dependent_hooks = $plugin_obj->hooks;
        return $dependent_hooks;
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
                            foreach ($gvalue['options'] as $ikey => $ivalue) {
                                $config[$ikey] = $ivalue['value'];
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
                        $this->appExtensionPath = $this->appsPath . $name . DS;
                        $info_file = $this->appExtensionPath . 'install/info.json';
                        $info = $this->getInfoByFile($info_file);
                        $info_flag = $this->checkInfoFile($info_file);
                        if (!$info || !$info_flag) {
                            \think\Log::record('应用'.$name.'的信息缺失！');
                            continue;
                        }

                        $list[$name] = $info;
                    }
                    cache('local_'.$type.'s_list',$list,600);
                } 
            }
        }
        
        return $list;
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
        $file_ext = ['png','jpg','jpeg','svg'];
        $logo = '';
        if ($type=='plugin') {
            $type_path = 'plugins/';
        } elseif ($type=='module') {
            $type_path = '';
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
