<?php
// Eacoo云服务
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace eacoo;
use think\Db;
use think\Cache;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use think\Exception;
use ZipArchive;

/**
 * 云服务类
 */
class Cloud {

    const EACOO_APPSTORE_DOWNLOAD_URL = 'http://www.eacoo123.com/api/appstore/download';

    /**
     * 构造函数
     * @param Request $request Request对象
     * @access public
     */
    public function __construct($options = [])
    {
    	$this->appType = $options['type'];
    	switch ($this->appType) {
			case 'plugin':
				$this->appPath = PLUGIN_PATH;
				break;
			case 'module':
				$this->appPath = APP_PATH;
				break;
            case 'theme':
                $this->appPath = THEME_PATH;
                break;
			default:
				# code...
				break;
		}
    }

    /**
     * 远程下载扩展
     * 
     * @param string $name 应用名称
     * @param array $extend 扩展参数
     * @return string
     * @throws Exception
     * @throws Exception
     */
    public function download($name='',$extend = [])
    {
        $appTmpDir = RUNTIME_PATH . $this->appType . DS;
        if (!is_dir($appTmpDir))
        {
            @mkdir($appTmpDir, 0755, true);
        }

        $tmpFile = $appTmpDir . $name . ".zip";
        $options = [
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'X-REQUESTED-WITH: XMLHttpRequest'
            ]
        ];
        $result = curl_request(self::EACOO_APPSTORE_DOWNLOAD_URL, array_merge(['name' => $name,'type'=>$this->appType], $extend), 'GET', $options);
        if ($result['status']==true) {
            if (substr($result['content'], 0, 1) == '{')
            {
                $result = (array) json_decode($result['content'], true);
                if (!empty($result['data']) && isset($result['data']['download_url'])) {
                    array_pop($options);
                    $res = curl_request($result['data']['download_url'], [], 'GET', $options);
                    if (!$res['status']) {
                        throw new \Exception($res['content']);
                    }
                    if ($write = fopen($tmpFile, 'w'))
                    {
                        fwrite($write, $res['content']);
                        fclose($write);
                        return $tmpFile;
                    }
                    throw new \Exception("没有权限写入临时文件",1); 
                    
                } else{
                    throw new \Exception($result['msg'],$result['code']);  
                }
            }
        } else{
            throw new \Exception($result['errno'],0); 
        }
        
    }

    /**
     * 解压应用压缩包
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-10-25
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function unzip($name='')
    {
    	$file = RUNTIME_PATH . $this->appType . DS . $name . '.zip';
        $dir = $this->appPath . $name . DS;
        if (class_exists('ZipArchive'))
        {
            $zip = new ZipArchive;
            if ($zip->open($file) !== TRUE)
            {
                throw new \Exception('无法打开zip文件');
            }
            if (!$zip->extractTo($dir))
            {
                $zip->close();
                throw new \Exception('无法提取文件');
            }
            $zip->close();
            return $dir;
        }
        throw new \Exception("无法执行解压操作，请确保ZipArchive安装正确");
    }

    
}
