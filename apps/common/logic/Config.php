<?php
// 配置逻辑      
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\logic;

class Config extends Base
{

    /**
     * 获取配置列表与ThinkPHP配置合并
     * @return [type] [description]
     * @return {[type]}  [description]
     * @date   2017-08-04
     * @author 心云间、凝听 <981248356@qq.com>
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

    /**
     * 通过字段配置构建一个Builder构建器配置
     * @param  array $option 配置选项
     * @param  string $value 值
     * @return [type] [description]
     * @date   2018-02-22
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function buildFormByFiled($options =[],$data=[],$is_config_field=false)
    {
        if (!empty($options) && is_array($options)) {
            if (!empty($data)) {
                
                foreach ($options as $key => $value) {
                    switch ($value['type']) {
                        case 'group':
                            foreach ($value['options'] as $okey => $option) {
                                if (isset($data[$key][$okey])) {
                                    $options[$key]['options'][$okey]['value'] = $data[$key][$okey]; 
                                }
                                
                            }
                            break;
                        case 'tab':
                            foreach ($value['options'] as $okey => $option) {
                                foreach ($option['options'] as $gkey => $value) {
                                    $options[$key]['options'][$okey][$gkey]['options']['value'] = $data[$gkey];
                                }
                                
                            }
                            break;
                        default:
                            if (isset($data[$key])) {
                                $options[$key]['value'] = $data[$key];
                            }
                            break;
                    }

                }
            }
            // 构造表单名
            foreach ($options as $key => $val) {
                $option_name = '';
                switch ($val['type']) {
                    case 'group':
                        foreach ($val['options'] as $key2 => $val2) {
                            if ($is_config_field==true) {
                                $option_name = 'config['.$key.']['.$key2.']';
                            } else{
                                $option_name = $key.'['.$key2.']';
                            }
                            $options[$key]['options'][$key2]['name'] = $option_name;
                        }
                        break;
                    case 'tab':
                        foreach ($val['options'] as $key2 => $val2) {
                            foreach ($val2['options'] as $key3 => $val3) {
                                if ($is_config_field==true) {
                                    $option_name = 'config['.$key3.']';
                                } else{
                                    $option_name = $key3;
                                }
                                $options[$key]['options'][$key2]['options'][$key3]['name'] = $option_name;

                                $options[$key]['options'][$key2]['options'][$key3]['confirm'] = $options[$key]['options'][$key2]['options'][$key3]['extra_class'] = $options[$key]['options'][$key2]['options'][$key3]['extra_attr']='';
                            }
                            
                        }
                        break;
                    default:
                        if ($is_config_field==true) {
                            $option_name = 'config['.$key.']';
                        } else{
                            $option_name = $key;
                        }
                        $options[$key]['name'] = $option_name;

                        $options[$key]['confirm']     = isset($val['confirm']) ? $val['confirm']:'';
                        $options[$key]['options']     = isset($val['options']) ? $val['options']:[];
                        $options[$key]['extra_class'] = isset($val['extra_class']) ? $val['extra_class']:'';
                        $options[$key]['extra_attr']  = isset($val['extra_attr']) ? $val['extra_attr']:'';
                        break;
                }  
            }
        }
        return $options;
    }

}