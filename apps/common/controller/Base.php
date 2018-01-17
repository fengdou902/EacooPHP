<?php
// 公共控制器       
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\controller;
use think\Controller;
use think\Cookie;

class Base extends Controller {

	protected $url;
	protected $request;
	protected $module;
	protected $controller;
	protected $action;

	public function _initialize() {
		//获取request信息
		$this->requestInfo();
	}

	/**
	 * request信息
	 * @return [type] [description]
	 */
	protected function requestInfo() {
		
		defined('MODULE_NAME') or define('MODULE_NAME', $this->request->module());
		defined('CONTROLLER_NAME') or define('CONTROLLER_NAME', $this->request->controller());
		defined('ACTION_NAME') or define('ACTION_NAME', $this->request->action());
		defined('IS_POST') or define('IS_POST', $this->request->isPost());
		defined('IS_AJAX') or define('IS_AJAX', $this->request->isAjax());
		defined('IS_PJAX') or define('IS_PJAX', $this->request->isPjax());
		defined('IS_GET') or define('IS_GET', $this->request->isGet());

		//$this->param = $this->request->param();
		$this->urlRule = strtolower($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action());
		$this->ip = $this->request->ip();
		$this->url = $this->request->url(true);//完整url
	}

	/**
     * 加载模板输出
     * @access protected
     * @param string $template 模板文件名
     * @param array  $vars     模板输出变量
     * @param array  $replace  模板替换
     * @param array  $config   模板参数
     * @return mixed
     */
    protected function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
    	if (IS_PJAX) {
    		echo $this->view->fetch($template, $vars, $replace, $config);
    	} else{
    		return $this->view->fetch($template, $vars, $replace, $config);
    	}
        
    }

	/**
	 * 获取输入数据，带默认值
     * @param string    $key 获取的变量名
     * @param mixed     $default 默认值
     * @param string    $filter 过滤方法
	 * @return mixed
	 */
	// public function input($key='',$default=false, $filter = '')
	// {
	// 	if (!$key) return false;
	// 	if ($pos = strpos($key, '.')) {
 //            // 指定参数来源
 //            list($method, $key) = explode('.', $key);
 //            if (!in_array($method, ['get', 'post', 'put', 'patch', 'delete', 'param', 'request', 'session', 'cookie', 'server', 'env', 'path', 'file'])) {
 //                $key    = $method . '.' . $key;
 //                $method = 'param';
 //            }
 //        } else {
 //            // 默认为自动判断
 //            $method = 'param';
 //        }
	// 	return $this->request->param($key, $default, $filter);
	// }

	/**
	 * 获取单个参数的数组形式
	 */
	// protected function getArrayParam($param) {
	// 	if (isset($this->param['id'])) {
	// 		return array_unique((array) $this->param[$param]);
	// 	} else {
	// 		return [];
	// 	}
	// }
}
