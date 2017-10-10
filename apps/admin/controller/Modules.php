<?php
// 模块管理          
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.eacoo123.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use com\Sql;

use app\admin\model\Modules as ModuleModel;
use app\admin\builder\Builder;
use app\admin\model\AuthRule;

class Modules extends Admin {

	protected $moduleModel;
	protected $moduleInstallPath;

	function _initialize()
	{
		parent::_initialize();
		$this->moduleModel = new ModuleModel();
	}

	/**
	 * 模块列表
	 * @param  string $from_type 来源类型
	 * @return [type] [description]
	 * @date   2017-09-21
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function index($from_type = 'local') {
		$tab_list = [
            'local'=>['title'=>'本地','href'=>url('index',['from_type'=>'local'])],
            'oneline'=>['title'=>'模块市场','href'=>url('index',['from_type'=>'oneline'])],
        ];

        if ($from_type == 'local') {
        	$data_list = $this->moduleModel->getAll();

			Builder::run('List')
					->setMetaTitle('模块列表')  // 设置页面标题
					->setTabNav($tab_list,$from_type) 
					->addTopButton('resume')   // 添加启用按钮
					->addTopButton('forbid')   // 添加禁用按钮
					//->addTopButton('sort')  // 添加排序按钮
					->setSearch('请输入ID/标题', url('index'))
					->keyListItem('name', '名称')
					->keyListItem('title', '标题')
					->keyListItem('description', '描述')
					->keyListItem('author', '开发者')
					->keyListItem('version', '版本')
					//->keyListItem('create_time', '创建时间', 'time')
					->keyListItem('status', '状态')
					->keyListItem('right_button', '操作', 'btn')
					->setListData($data_list)     // 数据列表
					->fetch();
        } elseif ($from_type == 'oneline') {
        	$data_list = $this->getAppstoreModules();

			Builder::run('List')
					->setMetaTitle('模块列表')  // 设置页面标题
					->setTabNav($tab_list,$from_type) 
					->keyListItem('name', '标识')
                    ->keyListItem('title', '名称')
                    ->keyListItem('description', '描述')
                    ->keyListItem('author', '作者')
                    ->keyListItem('downloaded', '活跃度')
                    ->keyListItem('version', '版本号')
                    ->keyListItem('publish_time', '最近更新')
					->keyListItem('right_button', '操作', 'btn')
					->setListData($data_list)     // 数据列表
					->fetch();
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
					$module_info = ModuleModel::where($map)->field('version,title')->find();
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
					$plugins_info = db('plugins')->where($map)->field('version,title')->find();
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
	 * 安装模块之前
	 */
	public function install_before($name) {
		Builder::run('Form')
				->setMetaTitle('准备安装模块')  // 设置页面标题
				->setPostUrl(url('install'))     // 设置表单提交地址
				->addFormItem('name', 'hidden', 'name', 'name')
				->addFormItem('clear', 'radio', '是否清除历史数据', '是否清除历史数据', [1 => '是', 0=> '否'])
				->setFormData(['name' => $name])
				->addButton('submit')->addButton('back')    // 设置表单按钮
				->fetch();
	}

	/**
	 * 安装模块
	 * @param  [type] $name 模块名字
	 * @param  boolean $clear 是否清除历史数据
	 * @return [type] [description]
	 * @date   2017-09-15
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function install($name, $clear = 1) {
		$this->moduleInstallPath = realpath(APP_PATH.$name).'/install';
		// 获取当前模块信息
		$info = ModuleModel::getInfoByFile($name);
		if (empty($info) || !$info) {
			$this->error('安装失败');
		}
		// 检查依赖
		if (!empty($info['dependences'])) {
			$result = $this->checkDependence($info['dependences']);
			if (!$result) {
				return false;
			}
		}

		// 安装数据库
		$uninstall_sql_status = true;
		// 清除旧数据
		if ($clear) {
			$sql_file = $this->moduleInstallPath.'/uninstall.sql';
			if(is_file($sql_file)) $uninstall_sql_status = Sql::executeSqlByFile($sql_file, $info['database_prefix']);
		}

		// 安装新数据表
		if (!$uninstall_sql_status) {
			$this->error('安装失败');
		}

		$sql_file = $this->moduleInstallPath.'/install.sql';
		if (is_file($sql_file)) {
			$sql_status = Sql::executeSqlByFile($sql_file, $info['database_prefix']);
			if (!$sql_status) {
				$sql_file = $this->moduleInstallPath.'/uninstall.sql';
				if(is_file($sql_file)) $sql_status = Sql::executeSqlByFile($sql_file, $info['database_prefix']);
				$this->error('安装失败');
			}
		}

		// 处理模块配置
		$config = ModuleModel::getDefaultConfig($name);
		$info['config'] = !empty($config) ? json_encode($config) : '';

		// 写入数据库模块信息
		$res = $this->moduleModel->allowField(true)->isUpdate(false)->data($info)->save();
		if ($res) {
			//后台菜单权限入库
			$admin_menus = ModuleModel::getAdminMenusByFile($name);
			if (!empty($admin_menus) && is_array($admin_menus)) {
				$this->addAdminMenus($admin_menus,$name);
			}
			
			// 安装成功后自动在前台新增导航(待完善)
			$navigation = ModuleModel::getNavigationByFile($name);
			if (!empty($navigation) && is_array($navigation)) {
				
			}

			//静态资源文件
            $static_path = realpath(APP_PATH.$name).'/static';
            if (is_dir($static_path)) {
                if(is_writable(ROOT_PATH.'public/static') && is_writable($static_path)){
                    if (!rename($static_path,ROOT_PATH.'public/static/'.$name)) {
                        trace('模块静态资源移动失败','error');
                    } 
                } else{
                    PluginsModel::where('name',$name)->update(['status'=>0]);
                    $this->error('安装失败，原因：模块静态资源目录不可写');
                }
            }

			$this->success('安装成功', url('index'));
		} else {
			$this->error($this->moduleModel->getError());
		}
	}

	/**
	 * 卸载模块之前
	 */
	public function uninstall_before($id) {
		Builder::run('Form')
				->setMetaTitle('准备卸载模块')  // 设置页面标题
				->setPostUrl(url('uninstall'))     // 设置表单提交地址
				->addFormItem('id', 'hidden', 'ID', 'ID')
				->addFormItem('clear', 'radio', '是否清除数据', '是否清除数据', array(1=> '是', 0=> '否（禁用）'))
				->setFormData(array('id' => $id))
				->addButton('submit')->addButton('back')    // 设置表单按钮
				->fetch();
	}

	/**
	 * 卸载模块
	 * @param  [type] $id 模块ID
	 * @param  boolean $clear 是否清除数据
	 * @return [type] [description]
	 * @date   2017-09-16
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function uninstall($id, $clear = false) {
		$module_info = ModuleModel::get($id);
		$name = $module_info['name'];
		if ($module_info['is_system'] == 1) {
			$this->error('系统模块不允许卸载！');
		}
		if ($clear) {
			$result = ModuleModel::destroy($id);
		} else{
			$result = ModuleModel::where('id',$id)->update(['status'=>0]);
		}
		
		if ($result) {
			// 删除后台菜单
		    $this->removeAdminMenus($name,$clear);
			if ($clear) {
		        //执行卸载sql
				$sql_file   = realpath(APP_PATH.$name).'/install/uninstall.sql';
				if (is_file($sql_file)) {
					$info       = ModuleModel::getInfoByFile($name);
					$sql_status = Sql::executeSqlByFile($sql_file, $info['database_prefix']);
					if (!$sql_status) {
						 $this->error('执行模块SQL卸载语句失败');
					}
				}
				
	            $_static_path = ROOT_PATH.'public/static/'.$name;
	            if (is_dir($_static_path)) {
	                if(is_writable(ROOT_PATH.'public/static') && is_writable(realpath(APP_PATH.$name))){
	                	$static_path = realpath(APP_PATH.$name).'/static';
	                    if (!rename($_static_path,$static_path)) {
	                        trace('插件静态资源移动失败：'.'public/static/'.$name.'->'.$static_path,'error');
	                    } 
	                } else{
	                    PluginsModel::where('name',$name)->setField('status',0);
	                    $this->error('卸载失败，原因：模块静态资源目录不可写');
	                }
	            }
	            $this->success('卸载成功',url('index'));
			} else {
				$this->success('卸载成功，相关数据未卸载！', url('index'));
			}
		} else {
			$this->error('卸载失败', url('index'));
		}
	}

	/**
	 * 更新模块信息
	 * @param  [type] $id [description]
	 * @return [type] [description]
	 * @date   2017-09-16
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function updateInfo($id) {
		$name = ModuleModel::where('id',$id)->value('name');

		// 获取当前模块信息
		$info = ModuleModel::getInfoByFile($name);

		// 读取数据库已有配置
		$db_moduel_config = ModuleModel::where('id',$id)->value('config');
		$db_moduel_config = json_decode($db_moduel_config, true);

		// 处理模块配置
		$options = ModuleModel::getOptionsByFile($name);
		if (!empty($options) && is_array($options)) {
			$config= [];
			foreach ($options as $key => $value) {
				if ($value['type'] == 'group') {
					foreach ($value['options'] as $gkey => $gvalue) {
						foreach ($gvalue['options'] as $ikey => $ivalue) {
							$config[$ikey] = $ivalue['value'];
						}
					}
				} else {
					if (isset($db_moduel_config[$key])) {
						$config[$key] = $db_moduel_config[$key];
					} else {
						$config[$key] = $options[$key]['value'];
					}
				}
			}
			$info['config'] = json_encode($config);
		} else {
			$info['config'] = '';
		}

		$result = $this->moduleModel->allowField(true)->save($info,['id'=>$id]);
		if ($result) {
			// 删除后台菜单
		    $this->removeAdminMenus($name,true);
			//后台菜单入库
			$admin_menus = ModuleModel::getAdminMenusByFile($name);
			if (!empty($admin_menus) && is_array($admin_menus)) {
				$this->addAdminMenus($admin_menus,$name);
			}

			// 更新后自动在前台新增导航(待完善)
			$navigation = ModuleModel::getNavigationByFile($name);
			if (!empty($navigation) && is_array($navigation)) {
				
			} 
			$this->success('更新成功', url('index'));
		} else {
			$this->error($this->moduleModel->getError());
		}

	}
    
	/**
	 * 添加后台菜单
	 * @param  array $data 菜单数据
	 * @param  integer $pid 父级ID
	 * @param  string $flag_name 模块名
	 * @date   2017-09-15
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
    private function addAdminMenus($data = [], $flag_name = '', $pid = 0)
    {
    	if (!empty($data) && is_array($data) && $flag_name!='') {
    		
    		$authRuleModel = new AuthRule;
			foreach ($data as $key => $menu) {
				$pid = isset($menu['pid']) ? (int)$menu['pid'] : $pid;
				
				$menu['depend_type'] = 1;//1代表module
				$menu['depend_flag'] = $flag_name;
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
                'depend_type' => 1,
                'depend_flag' => $flag_name
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
     * 更新后台菜单
     * @param  array $data [description]
     * @param  string $flag_name [description]
     * @param  integer $pid [description]
     * @return [type] [description]
     * @date   2017-09-16
     * @author 心云间、凝听 <981248356@qq.com>
     */
    private function updateAdminMenus($data = [], $flag_name = '', $pid = 0)
    {
   //  	if (!empty($data) && is_array($data) && $flag_name!='') {
   //  		$authRuleModel = new AuthRule;
			// foreach ($data as $key => $menu) {
			// 	$menu['module'] = $flag_name;
			// 	$menu['pid']    = $pid;
			// 	$menu['sort']   = isset($menu['sort']) ? $menu['sort'] : 99;
			// 	$authRuleModel->allowField(true)->isUpdate(false)->data($menu)->save();
			// 	//添加子菜单
			// 	if (isset($menu['sub_menu'])) {
			// 		if (!empty($menu['sub_menu'])) {
			// 			$this->updateAdminMenus($menu['sub_menu'], $flag_name, $authRuleModel->id);
			// 		}
	                
	  //           }
			// }
			// cache('admin_sidebar_menus',null);//清空后台菜单缓存
			// return true;
   //  	}
    	return false;
    }

    /**
     * 排序
     * @param  [type] $ids [description]
     * @return [type] [description]
     * @date   2017-09-15
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function sort($ids = null)
    {
        $builder = Builder::run('Sort');
        if (IS_POST) {
            $builder->doSort('module', $ids);
        } else {
            $map['status'] = array('egt', 0);
            $list = $this->moduleModel->selectByMap($map, 'sort asc', 'id,title,sort');
            foreach ($list as $key => $val) {
                $list[$key]['title'] = $val['title'];
            }
            $builder->setMetaTitle('模块排序')
                    ->setListData($list)
                    ->addButton('submit')->addButton('back')
                    ->fetch();
        }
    }

	/**
	 * 设置一条或者多条数据的状态
	 */
	public function setStatus($model = CONTROLLER_NAME,$script=false){
		$ids = input('param.ids/a');
		cache('module_menus',null);//清空后台菜单缓存
		if (is_array($ids)) {
			foreach ($ids as $id) {
				$is_system = model($model)->where('id',$id)->value('is_system');
				if ($is_system) {
					$this->error('系统模块不允许操作');
				}
			}
		} else {
			$is_system = model($model)->where('id',$id)->value('is_system');
			if ($is_system) {
				$this->error('系统模块不允许操作');
			}
		}
		parent::setStatus($model);
	}

	/**
     * 获取插件市场数据
     * @return [type] [description]
     * @date   2017-09-21
     * @author 心云间、凝听 <981248356@qq.com>
     */
    private function getAppstoreModules($paged = 1)
    {
        $store_data = cache('eacoo_appstore_modules_'.$paged);
        if (empty($store_data) || !$store_data) {
            $url        = 'http://www.eacoo123.com/server_appstore_modules';
            $params = [
                'paged'=>$paged
            ];
            $result     = curl_post($url,$params);
            $result = json_decode($result,true);
            $store_data = $result['data'];
            cache('eacoo_appstore_modules_'.$paged,$store_data,3600);
        }
        if (!empty($store_data)) {
            foreach ($store_data as $key => &$val) {
                $local_data = $this->moduleModel->localModules();

                $val['downloaded'] = '<i class="fa fa-star color-warning"></i> '.$val['downloaded'];
                $val['publish_time'] = friendly_date($val['publish_time']);
                $val['right_button'] = '<a class="label label-primary" href="javascript:void(0);" onclick="layer.alert(\'暂不支持在线安装\n请加QQ群：436491685\', {icon:6});">现在安装</a> ';
                if (!empty($local_data)) {
                    foreach ($local_data as $key => $row) {
                        if ($row['name']==$val['name']) {
                            if ($row['version']<$val['version']) {
                                $val['right_button'] = '<a class="label label-success" href="javascript:void(0);" onclick="layer.alert(\'暂不支持在线安装\n请加QQ群：436491685\', {icon:6});">升级</a> ';
                            } else{
                                $val['right_button'] = '<a class="label label-default" href="#">已安装</a> ';
                            }
                            
                        }
                    }
                }

                $val['right_button'] .= '<a class="label label-info " href="http://www.eacoo123.com" target="_blank">更多详情</a> ';
            }
        }
        return $store_data;
    }
}
