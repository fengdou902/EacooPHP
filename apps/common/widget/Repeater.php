<?php
// 自定义repeater
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\widget;

use app\common\controller\Base;

class Repeater extends Base
{
    //灵活字段
    public function render($attributes = [],$param='')
    {
        $id      = isset($attributes['id'])      ? $attributes['id']     :'repeater';
        $name    = isset($attributes['name'])    ? $attributes['name']   :'content';
        $default = isset($attributes['default']) ? $attributes['default']:'';
        $options = isset($attributes['options']) ? $attributes['options']:[];

        $this->assign('id',$id);
        $this->assign('name',$name);
        $optionss = [];
        //$this->assign('default',$default);
        if (!empty($default) && is_array($default)) {
            $new_options=[];
            foreach ($default as $key => $data) {
                $options = array_intersect_key($options,$data);  
                foreach ($options as $o_key => $option) {
                    $options[$o_key]['default']=$data[$o_key]; 
                } 
                $new_options[]=$options;
            }
            $optionss=$new_options;//赋值新的options
    
        } else{
            $optionss[0]=$options;
        }

        // $options=[
        //     'img'  =>['title'=>'图片','type'=>'image','default'=>'','placeholder'=>''],
        //     'url'  =>['title'=>'链接','type'=>'text','default'=>'','placeholder'=>'http://'],
        //     'text' =>['title'=>'文字','type'=>'text','default'=>'','placeholder'=>'输入文字'],
        // ];

        $this->assign('options',$optionss);
        //是否加载图片选择器
        if (is_array($optionss) && !empty($optionss)) {
            $num=0;
            foreach ($optionss[0] as $key => $val) {
                if ($val['type']=='image') {
                    $num++;
                }
            }
        }
        $param = [
            'is_load_WebUploader_script' =>$num,//加载webuploader资源
            'is_load_attachment_modal'   =>$num,//加载图片选择器
            'is_load_script'             =>false
        ];
        
        $this->assign('param',$param);
        $this->assign('field',$attributes);
        return $this->fetch('common@widget/repeater');
    }


}
