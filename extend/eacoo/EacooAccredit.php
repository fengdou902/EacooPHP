<?php
// 官方授权处理执行类
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
/**
 * 官方授权处理执行类
 */
class EacooAccredit {

    const EACOO_ACCREDIT_URL = 'http://www.eacoo123.com/client_product_accredit';
    /**
     * 授权执行
     * @param  array $data [description]
     * @return [type] [description]
     * @date   2017-09-05
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function runAccredit($data = [])
    {
        $data = array_merge(
            [
                'product_name'   => 'eacoophp',
                'product_verion' => EACOOPHP_V,
                'build_verion'   => BUILD_VERSION,//编译版本
                'domain'         => request()->domain(),
            ],
            $data);
        $data['agent'] = $_SERVER['HTTP_USER_AGENT'];
        $result        = curl_post(self::EACOO_ACCREDIT_URL,$data);
        $result        = json_decode($result,true);
        $install_lock  = $result['data'];

        file_put_contents(APP_PATH . 'install.lock', json_encode($install_lock));
        return $install_lock;
    }

    /**
     * 获取产品授权token
     * @return [type] [description]
     * @date   2018-03-04
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getAccreditToken()
    {
        $token = Cache::get('accredit_token');
        if (!$token) {
            $install_lock = json_decode(file_get_contents(APP_PATH . 'install.lock'),true);
            if ($install_lock) {
                $token = $install_lock['access_token'];
            }
            Cache::set('accredit_token',$token,3600*3);
        }
        return $token;
    }

    /**
     * 检测版本，获取云端版本
     * @return [type] [description]
     * @date   2017-09-09
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getVersion()
    {
        $version_info = Cache::get('eacoophp_remote_version');
        if (!$version_info) {
            $url = config('eacoo_api_url').'/eacoophp_version';
            $result = curl_get($url);
            $version_info = json_decode($result,true);
            Cache::set('eacoophp_remote_version',$version_info,3600);
        }
        return $version_info;
    }

    /**
     * 获取官方动态
     * @return [type] [description]
     * @date   2017-09-06
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getEacooNews($data=[])
    {
        $eacoo_news = Cache::get('eacoo_news');
        if (!$eacoo_news) {
            $url        = config('eacoo_api_url').'/client_eacoo_news';
            $result     = curl_post($url,$data);
            $eacoo_news = json_decode($result,true);
            Cache::set('eacoo_news',$eacoo_news,3600*3);
        }

        return $eacoo_news;
    }

    /**
     * Eacoo身份验证
     * @return [type] [description]
     * @date   2017-11-05
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function eacooIdentification()
    {
        try {
            $eacoo_identification = cache('eacoo_identification');
            if (!$eacoo_identification || !is_array($eacoo_identification)) {
                //需要重新登录
                throw new \Exception("请登录验证身份", 2);
            } else{
                $uid = $eacoo_identification['uid'];
                $access_token = $eacoo_identification['access_token'];
                $result = curl_request(config('eacoo_api_url').'/api/user',['uid'=>$uid,'token'=>$access_token],'GET');
                
                $result = json_decode($result['content'],true);
                if ($result['code']==1) {
                    $return =[
                        'code'=>1,
                        'msg'=>'身份验证成功',
                        'data'=>$result['data']['userinfo'],
                    ];
                    return $return;
                } else{
                    if ($result['code']==2) {
                        cache('eacoo_identification',null);
                    }
                    //需要重新登录
                    throw new \Exception($result['msg'], $result['code']);
                }
                
            }
            
        } catch (\Exception $e) {
            return [
                    'code'=>$e->getCode(),
                    'msg'=>$e->getMessage(),
                    'data'=>[],
                ];
        }
        
    }
}
