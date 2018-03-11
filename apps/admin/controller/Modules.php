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
use app\admin\logic\Module as ModuleLogic;
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
	public function index($from_type = 'oneline') {
        //$this->assign('page_config',['self'=>logic('admin/AppStore')->getAppsCenterTabList('module')]);
        if (IS_AJAX) {
            $paged = input('param.paged',1);
             if ($from_type == 'local') {
                $data_list = ModuleLogic::getAll();
                $total = 0;
            } elseif ($from_type == 'oneline') {
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
            $tab_list = [
                'local'=>['title'=>'本地模块','href'=>url('index',['from_type'=>'local'])],
                'oneline'=>['title'=>'模块市场','href'=>url('index',['from_type'=>'oneline'])],
            ];
             if ($from_type == 'local') {
                $meta_title = '本地模块';

            } elseif ($from_type == 'oneline') {
                $meta_title = '模块市场';

            }
            $this->assign('tab_list',$tab_list);
            $this->assign('from_type',$this->request->param('from_type','oneline'));
            $this->assign('meta_title',$meta_title);
            return $this->fetch('extension/modules');
        }
		

       
		 
	}

	/**
     * 模块设置
     * @return [type] [description]
     * @date   2017-08-30
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function config() {
        if (IS_POST) {
			$data   = $this->request->param();
			$id     = $data['id'];
			$config = $data['config'];
            $res   = $this->moduleModel->save(['config'=>json_encode($config)],['id'=>$id]);
            if ($res !== false) {
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
            $app  = ModuleModel::get($map);
            $app = $app ? $app->toArray() : $app;
            if (!$app) {
                $this->error('模块未安装');
            }

            $db_config = $app['config'];
            $extensionObj = new Extension;
            $extensionObj->initInfo('module',$app['name']);

            $options   = $extensionObj->getOptionsByFile();
            $db_config = json_decode($db_config, true);
            
            $options = logic('common/Config')->buildFormByFiled($options,$db_config,true);
    
            if (!empty($app['custom_config'])) {
                $this->assign('data', $app);
                $this->assign('form_items', $options);
                $this->assign('custom_config', $this->fetch($app['plugin_path'].$app['custom_config']));
                return $this->fetch($app['plugin_path'].$app['custom_config']);
            } else {
                return builder('Form')
                        ->setMetaTitle('设置-'.$app['title'])  //设置页面标题
                        ->setPostUrl(url('config')) //设置表单提交地址
                        ->addFormItem('id', 'hidden', 'ID', 'ID')
                        ->setExtraItems($options) //直接设置表单数据
                        ->setFormData($app)
                        ->addButton('submit')->addButton('back')    // 设置表单按钮
                        ->fetch();
            }
        }
    }

	/**
	 * 安装模块之前
	 */
	public function installBefore($name) {
		$this->assign('meta_title','准备安装模块');
        $info=['name'=>$name,'clear'=>1];
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
        $post_url = url('install');
        $this->assign('post_url',$post_url);
        return $this->fetch('extension/install_before');

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
        $result = $extensionObj->install($name,$clear);
        if ($result['code']==1) {
        	$this->success('安装成功', '');
        } else{
        	$this->error($result['msg'], '');
        }

	}

	/**
     * 卸载模块之前
     * @param  [type] $id [description]
     * @return [type] [description]
     * @date   2018-02-07
     * @author 心云间、凝听 <981248356@qq.com>
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
			$result = ModuleModel::where('id',$id)->update(['status'=>-1]);
		}
		
		if ($result) {
			$extensionObj = new Extension;
            $extensionObj->initInfo('module',$name);
            // 删除后台菜单
            $extensionObj->removeAdminMenus($name,$clear);
            $extensionObj->removeNavigationMenus($clear);
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
	            $this->success('卸载成功',url('index',['from_type'=>'local']));
			} else {
				$this->success('卸载成功，相关数据未卸载！',url('index',['from_type'=>'local']));
			}
		} else {
			$this->error('卸载失败', '');
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
				throw new \Exception($this->moduleModel->getError());
			}
		} catch (\Exception $e) {
			$this->error($e->getMessage());
		}	
		$this->success('更新成功', url('index',['from_type'=>'local']));
	}
    
    /**
     * 刷新缓存
     * @return [type] [description]
     * @date   2017-10-30
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function refresh()
    {
        Extension::refresh('module');
        $this->success('操作成功','');
    }

    /**
     * 删除
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-11-07
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function delapp($name='')
    {
        if ($name!='') {
            @rmdirs(APP_PATH.$name);
            Extension::refresh('module');
            $this->success('删除模块成功');
        }
        $this->error('删除模块失败');
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
        $builder = builder('Sort');
        if (IS_POST) {
            $builder->doSort('module', $ids);
        } else {
            $map['status'] = array('egt', 0);
            $list = $this->moduleModel->getList($map, 'sort asc', 'id,title,sort');
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
    private function getCloudAppstore($paged = 1)
    {
        $total = 12;
        $store_data = cache('eacoo_appstore_modules_'.$paged);
        if (empty($store_data) || !$store_data) {
            $url        = config('eacoo_api_url').'/api/appstore/modules';
            $params = [
                'paged'=>$paged,
                'eacoophp_version'=>EACOOPHP_V
            ];
            $result = curl_post($url,$params);
            $result = json_decode($result,true);
            $store_data = $result['data'];
            $total = 12;
            cache('eacoo_appstore_modules_'.$paged,$store_data,3600);
            cache('eacoo_appstore_modules_info',['total'=>$total],3600);
        }
        if (!empty($store_data)) {
        	$extensionObj = new Extension();
            $local_modules = $extensionObj->localApps('module');
            foreach ($store_data as $key => &$val) {
                $val['from_type']    = 'oneline';
                $val['publish_time'] = friendly_date($val['publish_time']);
                $val['right_button'] = '<a class="btn btn-primary btn-sm app-online-install" data-name="'.$val['name'].'" data-type="module" href="javascript:void(0);" data-install-method="install">现在安装</a> ';
                if (!empty($local_modules)) {
                    foreach ($local_modules as $key => $row) {
                        if ($row['name']==$val['name']) {
                            if ($row['version']<$val['version']) {
                                $val['right_button'] = '<a class="btn btn-success btn-sm app-online-install"  data-name="'.$val['name'].'" data-type="module" href="javascript:void(0);" data-install-method="upgrade">升级</a> ';
                            } elseif(isset($row['status']) && $row['status']==3){
                                $val['right_button'] = '<a class="btn btn-default btn-sm" href="'.url('index',['from_type'=>'local']).'">已下载</a> ';
                            } else{
                                $val['right_button'] = '<a class="btn btn-default btn-sm" href="'.url('index',['from_type'=>'local']).'">已安装</a> ';
                            }
                            
                        }
                    }
                }

                //$val['right_button'] .= '<a class="btn btn-info btn-sm" href="http://www.eacoo123.com" target="_blank">更多详情</a> ';
            }
        }
        return [$store_data,$total];
    }
}
