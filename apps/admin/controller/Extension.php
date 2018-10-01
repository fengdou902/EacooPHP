<?php
// 扩展中心         
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
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
use app\admin\model\AuthRule as AuthRuleModel;
use app\common\model\Nav as NavModel;
use app\admin\model\Hooks as HooksModel;
use app\admin\model\Plugins as PluginsModel;
use app\admin\model\Modules as ModuleModel;
use app\admin\model\Theme as ThemeModel;

use app\admin\logic\Extension as ExtensionLogic;

class Extension extends Admin {

    protected $type;//类型：plugin,module
    protected $appsPath;//应用目录
    protected $appName;
    public  $appExtensionPath;//应用具体扩展目录
    public  $info;
    protected $hooksModel;
    protected $appExtensionModel;
    protected $uid;

	function _initialize()
	{
		parent::_initialize();
		$this->type = $this->request->param('apptype');
        //实例化逻辑层
        $this->extensionLogic = new ExtensionLogic();
		$this->extensionLogic->initInfo($this->type);
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
                $check_res = $this->extensionLogic->checkInfoFile($info_file);
                
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
                $return = $this->extensionLogic->install();
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
     * 在线安装之前
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-11-07
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function onlineInstallBefore($name='')
    {
        $install_method = $this->request->param('install_method');
        $this->assign('install_method',$install_method);
        return $this->fetch('extension/online_install_before');
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
                    $this->extensionLogic->_upgradeAction($name);
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
                $check_res = $this->extensionLogic->checkInfoFile($info_file);
                
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
                  $return = $this->extensionLogic->install($name);
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
                ExtensionLogic::refresh($this->type);
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
                    $vali_msg = $this->validate(['account'=>$identification,'password'=>$password],
                      [
                          ['account','require|email','账号不能为空|请用邮箱账号登录'],
                          ['password','require','密码不能为空'],
                      ]);
                      if(true !== $vali_msg){
                          // 验证失败 输出错误信息
                          throw new \Exception($vali_msg,0);
                      }
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

}
