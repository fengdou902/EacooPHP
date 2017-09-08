<?php
// 配置模型       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\model;

class Config extends Base
{
    // 设置数据表（不含前缀）
    // protected $name = 'config';

    //protected $auto 	= ['update_time'];
    protected $insert   = ['status' => 1];

    /**
     * 获取配置列表与ThinkPHP配置合并
     * @return [type] [description]
     * @return {[type]}  [description]
     * @date   2017-08-04
     * @author 赵俊峰 <981248356@qq.com>
     */
    public static function lists() {
        $map['status'] = ['eq', 1];
        $list = self::where($map)->field('name,value,type')->select();
        foreach ($list as $key => $val) {
            switch ($val['type']) {
                case 'array': 
                    $config[$val['name']] = parse_config_attr($val['value']);
                    break;
                case 'json': 
                    $config[$val['name']] = json_decode($val['value'],true);
                    break;
                case 'checkbox': 
                    $config[$val['name']] = explode(',', $val['value']);
                    break;
                default:
                    $config[$val['name']] = $val['value'];
                    break;
            }
        }
        return $config;
    }
}