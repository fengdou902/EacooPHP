<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.eacoo123.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use com\Sql;

use app\admin\model\Module as ModuleModel;
use app\admin\builder\Builder;
//模块管理
class Module extends Admin {

	protected $moduleModel;

	function _initialize()
	{
		parent::_initialize();
		$this->moduleModel = new ModuleModel();
	}

	public function index() {

		$data_list = $this->moduleModel->getAll();

		Builder::run('List')
				->setMetaTitle('模块列表')  // 设置页面标题
				->addTopButton('resume')   // 添加启用按钮
				->addTopButton('forbid')   // 添加禁用按钮
				->addTopButton('sort')  // 添加排序按钮
				->setSearch('请输入ID/标题', url('index'))
				->keyListItem('name', '名称')
				->keyListItem('title', '标题')
				->keyListItem('description', '描述')
				->keyListItem('developer', '开发者')
				->keyListItem('version', '版本')
				//->keyListItem('create_time', '创建时间', 'time')
				->keyListItem('status_icon', '状态', 'text')
				->keyListItem('right_button', '操作', 'btn')
				->setListData($data_list)     // 数据列表
				->fetch();
	}

	/**
	 * 检查模块依赖
	 */
	public function checkDependence($dependences) {
		if (is_array($dependences)) {
			foreach ($dependences as $key => $val) {
				if ($key=='admin') {
					continue;
				}
				$con['name'] = $key;
				$module_info = $this->moduleModel->where($con)->find();
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
			return true;
		}
	}

	/**
	 * 安装模块之前
	 */
	public function install_before($name) {
		// 使用FormBuilder快速建立表单页面。
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
	 */
	public function install($name, $clear = true) {
		// 获取当前模块信息
		$info_file = realpath(APP_PATH.$name).'/info/'.$this->moduleModel->info_file();
		if (!$info_file) {
			$this->error('安装失败');
		}
		$config_info = include $info_file;
		$data = $config_info['info'];

		// 处理模块配置
		if ($config_info['config']) {
			$temp_arr = $config_info['config'];
			foreach ($temp_arr as $key => $value) {
				if ($value['type'] == 'group') {
					foreach ($value['options'] as $gkey => $gvalue) {
						foreach ($gvalue['options'] as $ikey => $ivalue) {
							$config[$ikey] = $ivalue['value'];
						}
					}
				} else {
					$config[$key] = $temp_arr[$key]['value'];
				}
			}
			$data['config'] = json_encode($config);
		} else {
			$data['config'] = '';
		}

		// 检查依赖
		if (isset($data['dependences']) && $data['dependences']) {
			$result = $this->checkDependence($data['dependences']);
			if (!$result) {
				return false;
			}
		}

		// 获取后台菜单
		if (!empty($config_info['admin_menu'])) {
			$AdminMenuData =$config_info['admin_menu'];
		}

		// 获取用户中心导航
		if (isset($data['user_nav']) && $config_info['user_nav']) {
			$data['user_nav'] = json_encode($config_info['user_nav']);
		} else {
			$data['user_nav'] = '';
		}

		// 安装数据库
		$uninstall_sql_status = true;
		// 清除旧数据
		if ($clear) {
			$sql_file = realpath(APP_PATH.$name).'/info/uninstall.sql';
			$uninstall_sql_status = $this->execute_sql_from_file($sql_file);
		}
		// 安装新数据表
		if (!$uninstall_sql_status) {
			$this->error('安装失败');
		}
		$sql_file = realpath(APP_PATH.$name).'/info/install.sql';
		$sql_status = $this->execute_sql_from_file($sql_file);

		if ($sql_status) {
			// 写入数据库记录

			$id = $this->moduleModel->allowField(true)->isUpdate(false)->data($data)->save();
			if ($id) {
				//后台菜单入库
				if ($AdminMenuData) {
					foreach ($AdminMenuData as $key => $menu) {
						$menu_data['title']=$menu['title'];
						//$result =model('auth_rule')->editData($menu_data);
		                // if ($result) {
		                //     cache('admin_menu',null);//清空后台菜单缓存
		                // }
					}
				}
				// 安装成功后自动在前台新增导航
				/*
				$nav_data['name']  = strtolower($data['name']);
				$nav_data['title'] = $data['title'];
				$nav_data['type']  = 'module';
				$nav_data['value'] = $data['name'];
				$nav_data['icon']  = $data['icon'] ? : '';
				$nav_object = D('Nav');
				$nav_data_created = $nav_object->create($nav_data);
				if ($nav_data_created) {
					$nav_add_result = D('Nav')->add($nav_data_created);
				}*/
				$this->success('安装成功', url('index'));
			} else {
				$this->error($this->moduleModel->getError());
			}
		} else {
			$sql_file = realpath(APP_PATH.$name).'/info/uninstall.sql';
			$sql_status = $this->execute_sql_from_file($sql_file);
			$this->error('安装失败');
		}
	}

	/**
	 * 卸载模块之前
	 */
	public function uninstall_before($id) {
		// 使用FormBuilder快速建立表单页面。

		Builder::run('Form')
				->setMetaTitle('准备卸载模块')  // 设置页面标题
				->setPostUrl(url('uninstall'))     // 设置表单提交地址
				->addFormItem('id', 'hidden', 'ID', 'ID')
				->addFormItem('clear', 'radio', '是否清除数据', '是否清除数据', array('1' => '是', '0' => '否'))
				->setFormData(array('id' => $id))
				->addButton('submit')->addButton('back')    // 设置表单按钮
				->fetch();
	}

	/**
	 * 卸载模块
	 */
	public function uninstall($id, $clear = false) {
		$module_info = $this->moduleModel->find($id);
		if ($module_info['is_system'] === '1') {
			$this->error('系统模块不允许卸载！');
		}
		$result =$this->moduleModel->destroy($id);
		if ($result) {
			if ($clear) {
				$sql_file = realpath(APP_PATH.$module_info['name']).'/info/uninstall.sql';
				$sql_status = $this->execute_sql_from_file($sql_file);
				if ($sql_status) {
					$this->success('卸载成功，相关数据彻底删除！', url('index'));
				}
			} else {
				$this->success('卸载成功，相关数据未卸载！', url('index'));
			}
		} else {
			$this->error('卸载失败', url('index'));
		}
	}

	/**
	 * 更新模块信息
	 */
	public function updateInfo($id) {
		$name = $this->moduleModel->getFieldById($id, 'name');
		$info_file = realpath(APP_PATH.$name).'/Info/'.$this->moduleModel->info_file();
		if (!$info_file) {
			$this->error('不存在安装文件');
		}
		$config_info = include $info_file;
		$data = $config_info['info'];

		// 读取数据库已有配置
		$db_moduel_config = $this->moduleModel->getFieldByName($name, 'config');
		$db_moduel_config = json_decode($db_moduel_config, true);

		// 处理模块配置
		if ($config_info['config']) {
			$temp_arr = $config_info['config'];
			foreach ($temp_arr as $key => $value) {
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
						$config[$key] = $temp_arr[$key]['value'];
					}
				}
			}
			$data['config'] = json_encode($config);
		} else {
			$data['config'] = '';
		}

		// 获取后台菜单
		if ($config_info['admin_menu']) {
			// 将key值赋给id
			foreach ($config_info['admin_menu'] as $key => &$val) {
				$val['id'] = (string)$key;
			}
			$data['admin_menu'] = json_encode($config_info['admin_menu']);
		}

		// 获取用户中心导航
		if ($config_info['user_nav']) {
			$data['user_nav'] = json_encode($config_info['user_nav']);
		} else {
			$data['user_nav'] = '';
		}

		$result = $this->moduleModel->save($data,['id'=>$id]);
		if ($result) {
			$this->success('更新成功', url('index'));
		} else {
			$this->error($this->moduleModel->getError());
		}

	}
    /**
     * 对链接进行排序
     * @author 赵俊峰<981248356@qq.com>
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
     * 执行文件中SQL语句函数
     * @param string $file sql语句文件路径
     * @param string $tablepre  自己的前缀
     * @return multitype:string 返回最终需要的sql语句
     */
    private function execute_sql_from_file($file) {
        $sql_data = file_get_contents($file);
        if (!$sql_data) {
            return true;
        }
        $sql_format = $this->sql_split($sql_data, config('database.type'));
        $counts = count($sql_format);
        for ($i = 0; $i < $counts; $i++) {
            $sql = trim($sql_format[$i]);
            model()->execute($sql);
        }
        return true;
    }

	/**
	 * 设置一条或者多条数据的状态
	 */
	public function setStatus($model = CONTROLLER_NAME,$script=false){
		$ids = $this->input('request.ids/a');
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
}
