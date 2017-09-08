<?php
// 公共控制器       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2016 http://www.eacoomall.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author: 心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\controller;
use think\Controller;
use think\Cookie;
use think\Hook;

class Base extends Controller {

	protected $url;
	protected $request;
	protected $module;
	protected $controller;
	protected $action;

	public function _initialize() {
		//获取request信息
		$this->requestInfo();
		Cookie::set('__forward__',$this->url,3600);
		//自定义基础控制器钩子
		Hook::listen('base_controller_init', $this, $this->request, true);
		//验证安装文件
		if (!is_file(APP_PATH . 'install.lock') || !is_file(APP_PATH . 'database.php')) {
			$this->redirect('install/index/index');
		}
	}

	// public function execute($mc = null, $op = '', $ac = null) {
	// 	$op = $op ? $op : $this->request->module();
	// 	if (\think\Config::get('url_case_insensitive')) {
	// 		$mc = ucfirst(parse_name($mc, 1));
	// 		$op = parse_name($op, 1);
	// 	}

	// 	if (!empty($mc) && !empty($op) && !empty($ac)) {
	// 		$ops    = ucwords($op);
	// 		$class  = "\\addons\\{$mc}\\controller\\{$ops}";
	// 		$addons = new $class;
	// 		$addons->$ac();
	// 	} else {
	// 		$this->error('没有指定插件名称，控制器或操作！');
	// 	}
	// }

	/**
	 * request信息
	 * @return [type] [description]
	 */
	protected function requestInfo() {
		$this->param = $this->request->param();
		defined('MODULE_NAME') or define('MODULE_NAME', $this->request->module());
		defined('CONTROLLER_NAME') or define('CONTROLLER_NAME', $this->request->controller());
		defined('ACTION_NAME') or define('ACTION_NAME', $this->request->action());
		defined('IS_POST') or define('IS_POST', $this->request->isPost());
		defined('IS_AJAX') or define('IS_AJAX', $this->request->isAjax());
		defined('IS_GET') or define('IS_GET', $this->request->isGet());
		defined('IS_MOBILE') or define('IS_MOBILE', $this->request->isMobile());
		$this->simple_url = strtolower($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action());
		$this->ip = $this->request->ip();
		$this->url = $this->request->url(true);//完整url
	}

	/**
	 * 获取输入数据，带默认值
     * @param string    $key 获取的变量名
     * @param mixed     $default 默认值
     * @param string    $filter 过滤方法
	 * @return mixed
	 */
	public function input($key='',$default=false, $filter = '')
	{
		if (!$key) return false;
		if ($pos = strpos($key, '.')) {
            // 指定参数来源
            list($method, $key) = explode('.', $key);
            if (!in_array($method, ['get', 'post', 'put', 'patch', 'delete', 'param', 'request', 'session', 'cookie', 'server', 'env', 'path', 'file'])) {
                $key    = $method . '.' . $key;
                $method = 'param';
            }
        } else {
            // 默认为自动判断
            $method = 'param';
        }
		return $this->request->param($key, $default, $filter);
	}

	/**
	 * 获取单个参数的数组形式
	 */
	protected function getArrayParam($param) {
		if (isset($this->param['id'])) {
			return array_unique((array) $this->param[$param]);
		} else {
			return [];
		}
	}
}
