<?php
// 插件控制器       
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\home\admin;
use app\admin\controller\Admin;

class Plugin extends Admin
{
    protected $name             = '';
    protected $pluginPath       = '';

    public function _initialize() {
        parent::_initialize();

        $name = input('param._plugin', '', 'trim');
        if ($name) {
            $this->name = $name;
            $this->pluginPath = PLUGIN_PATH.$name.DS;
        } else{
            $class = get_class($this);
            $path = strstr($class,substr($class, strrpos($class, '\\') + 1),true);
            $this->pluginPath = ROOT_PATH.str_replace('\\','/',$path);
        }
    }

    /**
    * 执行插件内部方法
    */
    public function execute()
    {
        $plugin     = input('param._plugin');
        $controller = input('param._controller');
        $action     = input('param._action');
        $params     = $this->request->except(['_plugin', '_controller', '_action'], 'param');

        if (empty($plugin) || empty($controller) || empty($action)) {
            $this->error('没有指定插件名称、控制器名称或操作名称');
        } else{
            if (!is_array($params)) {
                $params = (array)$params;
            }
            $class = "plugins\\{$plugin}\\admin\\{$controller}";
            $obj = new $class;
            return call_user_func_array([$obj, $action], $params);
        }

    }

    /**
     * 插件模版输出
     * @param  string $templateFile 模板文件名
     * @param  array  $vars         模板输出变量
     * @param  array  $replace      模板替换
     * @param  array  $config       模板参数
     * @param  array  $render       是否渲染内容
     * @return [type]               [description]
     */
    public function fetch($template='', $vars = [], $replace = [], $config = [] ,$render=false) {
        $plugin_name = input('param.plugin_name');

        if ($plugin_name != '') {
            $plugin     = $plugin_name;
            $controller = input('param._controller');
            $action     = 'index';
        } else {
            $plugin     = input('param._plugin');
            $controller = input('param._controller');
            $action     = input('param._action');
        }
        $template = $template == '' ? $action : $template;
        if (MODULE_MARK === 'admin') {
            $template = 'admin/'.$controller.'/'.$template;
        }
        if ($template != '') {
            if (!is_file($template)) {
                $template = $this->pluginPath. 'view/'. $template . '.' .config('template.view_suffix');
                if (!is_file($template)) {
                    throw new \Exception('模板不存在：'.$template, 5001);
                }
            }
            //$template = config('template.view_path').$template . '.' .config('template.view_suffix');
            
            echo $this->view->fetch($template, $vars, $replace, $config, $render);
        }
    }

    /**
     * 设置一条或者多条数据的状态
     * @param $script 严格模式要求处理的纪录的uid等于当前登陆用户UID
     */
    public function setStatus($model = null, $script = false) {
        $ids = $this->request->param('ids/a');
        $status = $this->request->param('status');
        if (empty($ids)) {
            $this->error('请选择要操作的数据');
        }
        if (!$model) {
            $model = input('param._controller');;
        }
        //在插件中，先优先查找插件中的类
        $model_class = "\\plugins\\$this->name\\model\\$model";
        if (class_exists($model_class)) {
            $model = new $model_class();
        } else{
            $model = model($model);
        }
        
        $model_primary_key = $model->getPk();
        $map[$model_primary_key] = ['in',$ids];
        if ($script===true) {
            $map['uid'] = ['eq', is_login()];
        }
        switch ($status) {
            case 'forbid' :  // 禁用条目
                $data = ['status' => 0];
                $this->editRow(
                    $model,
                    $data,
                    $map,
                    ['success'=>'禁用成功','error'=>'禁用失败']
                );
                break;
            case 'resume' :  // 启用条目
                $data = ['status' => 1];
                $map  = array_merge(['status' => 0], $map);
                $this->editRow(
                    $model,
                    $data,
                    $map,
                    array('success'=>'启用成功','error'=>'启用失败')
                );
                break;
            case 'hide' :  // 隐藏条目
                $data = array('status' => 1);
                $map  = array_merge(array('status' => 2), $map);
                $this->editRow(
                    $model,
                    $data,
                    $map,
                    array('success'=>'隐藏成功','error'=>'隐藏失败')
                );
                break;
            case 'show' :  // 显示条目
                $data = array('status' => 2);
                $map  = array_merge(array('status' => 1), $map);
                $this->editRow(
                   $model,
                   $data,
                   $map,
                   array('success'=>'显示成功','error'=>'显示失败')
                );
                break;
            case 'recycle' :  // 移动至回收站
                $data['status'] = -1;
                $this->editRow(
                    $model,
                    $data,
                    $map,
                    array('success'=>'成功移至回收站','error'=>'删除失败')
                );
                break;
            case 'restore' :  // 从回收站还原
                $data = ['status' => 1];
                $map  = array_merge(['status' => -1], $map);
                $this->editRow(
                    $model,
                    $data,
                    $map,
                    array('success'=>'恢复成功','error'=>'恢复失败')
                );
                break;
            case 'delete'  :  // 删除条目
                //action_log(0, is_login(), ['param'=>$this->request->param()],'删除操作');
                $result = $model->where($map)->delete();
                if ($result) {
                    $this->success('删除成功，不可恢复！');
                } else {
                    $this->error('删除失败');
                }
                break;
            default :
                $this->error('参数错误');
                break;
        }
    }

}
