<?php
// 模块管理          
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use eacoo\Sql;

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

        $this->assign('tab_list',$tab_list);
        $this->assign('from_type',$this->request->param('from_type','local'));

        if ($from_type == 'local') {
        	$data_list = $this->moduleModel->getAll();
        	$meta_title = '本地模块';

        } elseif ($from_type == 'oneline') {
        	$data_list = $this->getAppstoreModules();
        	$meta_title = '模块市场';

        }
		 $this->assign('meta_title',$meta_title);
		 $this->assign('data_list',$data_list);
        return $this->fetch('extension/modules');
	}

	/**
	 * 安装模块之前
	 */
	public function installBefore($name) {
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
		$extensionObj = new Extension;
        $extensionObj->initInfo('module',$name);
        $result = $extensionObj->install($name);
        if ($result['code']==1) {
        	$this->success('安装成功', url('index'));
        } else{
        	$this->error($result['msg'], '');
        }

	}

	/**
	 * 卸载模块之前
	 */
	public function uninstallBefore($id) {
		$this->assign('meta_title','准备卸载模块');
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
			$extensionObj = new Extension;
            $extensionObj->initInfo('module',$name);
            // 删除后台菜单
            $extensionObj->removeAdminMenus($name,$clear);
			if ($clear) {
		        //执行卸载sql
				$sql_file   = APP_PATH.$name.'/install/uninstall.sql';
				if (is_file($sql_file)) {
					$info       = $extensionObj->getInfoByFile();
					$sql_status = Sql::executeSqlByFile($sql_file, $info['database_prefix']);
					if (!$sql_status) {
						 $this->error('执行模块SQL卸载语句失败');
					}
				}
				
	            $_static_path = PUBLIC_PATH.'static/'.$name;
	            if (is_dir($_static_path)) {
	                if(is_writable(PUBLIC_PATH.'static') && is_writable(APP_PATH.$name)){
	                	$static_path = APP_PATH.$name.'/static';
	                    if (!rename($_static_path,$static_path)) {
	                        trace('模块静态资源移动失败：'.'public/static/'.$name.'->'.$static_path,'error');
	                    } 
	                } else{
	                    ModuleModel::where('name',$name)->setField('status',0);
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
	public function updateInfo($id=0) {
		try {
			if (!$id || $id<1) {
				throw new \Exception("参数ID错误");
			}
			$module_db_info = ModuleModel::where('id',$id)->field('name,config')->find();
			if (empty($module_db_info)) {
				throw new \Exception("数据不存在");
			}
			$name = $module_db_info['name'];

			$extensionObj = new Extension;
	        $extensionObj->initInfo('module',$name);
			// 获取当前模块信息
			$info = $extensionObj->getInfoByFile();

			// 读取数据库已有配置
			$db_moduel_config = json_decode($module_db_info['config'], true);

			// 处理模块配置
			$options = $extensionObj->getOptionsByFile($name);
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
				if ($name!='user' && $name!='admin') {
					// 删除后台该菜单
				    $extensionObj->removeAdminMenus($name,true);
					//后台菜单入库
					$admin_menus = $extensionObj->getAdminMenusByFile($name);
					if (!empty($admin_menus) && is_array($admin_menus)) {
						$extensionObj->addAdminMenus($admin_menus,$name);
					}

					// 更新后自动在前台新增导航(待完善)
					$navigation = $extensionObj->getNavigationByFile($name);
					if (!empty($navigation) && is_array($navigation)) {
						
					} 
				}

			} else {
				throw new Exception($this->moduleModel->getError());
			}
		} catch (\Exception $e) {
			$this->error($e->getMessage());
		}	
		$this->success('更新成功', url('index'));
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
		$ids = $this->request->param('ids');
		$status = $this->request->param('status');
		if (is_array($ids)) {
			foreach ($ids as $id) {
				$info = model($model)->where('id',$id)->field('name,is_system')->find();
				if ($info['is_system']) {
					$this->error('系统模块不允许操作');
				}
			}
		} else {
			$info = model($model)->where('id',$ids)->field('name,is_system')->find();
			if ($info['is_system']) {
				$this->error('系统模块不允许操作');
			}

			$extensionObj = new Extension;
            $extensionObj->initInfo('module',$info['name']);
            $extensionObj->switchAdminMenus($info['name'],$status);
			
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
            $url        = config('eacoo_api_url').'/server_appstore_modules';
            $params = [
                'paged'=>$paged
            ];
            $result = curl_post($url,$params);
            $result = json_decode($result,true);
            $store_data = $result['data'];
            cache('eacoo_appstore_modules_'.$paged,$store_data,3600);
        }
        if (!empty($store_data)) {
        	$extensionObj = new Extension();
            $local_modules = $extensionObj->localApps('module');
            foreach ($store_data as $key => &$val) {

                $val['publish_time'] = friendly_date($val['publish_time']);
                $val['right_button'] = '<a class="btn btn-primary btn-sm app-online-install" data-name="'.$val['name'].'" data-type="module" href="javascript:void(0);">现在安装</a> ';
                if (!empty($local_modules)) {
                    foreach ($local_modules as $key => $row) {
                        if ($row['name']==$val['name']) {
                            if ($row['version']<$val['version']) {
                                $val['right_button'] = '<a class="btn btn-success btn-sm" href="javascript:void(0);" onclick="layer.alert(\'暂不支持在线安装\n请加QQ群：436491685\', {icon:6});">升级</a> ';
                            } else{
                                $val['right_button'] = '<a class="btn btn-default btn-sm" href="'.url('index',['from_type'=>'local']).'">已安装</a> ';
                            }
                            
                        }
                    }
                }

                //$val['right_button'] .= '<a class="btn btn-info btn-sm" href="http://www.eacoo123.com" target="_blank">更多详情</a> ';
            }
        }
        return $store_data;
    }
}
