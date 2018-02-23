<?php
// 附件模型 
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\model;

use think\File;
class Attachment extends Base {

    // protected $auto  = ['update_time'];
    protected $insert = ['status' => 1,'uid']; 

    protected function setUidAttr($value)
    {
        return is_login();
    }

    // protected function setCreateTimeAttr($value)
    // {
    //     return time();
    // }

    //获取缩略图地址
    protected function getThumbSrcAttr($value,$data)
    {
        if ($data['location']=='link') {
            $thumb_src = $data['path'];
        } else {
            $style = 'medium';
            if ($data['path_type']=='brand') {
                $style = '';
            }
            if (in_array($data['ext'],['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'wps', 'txt', 'zip', 'rar', 'gz', 'bz2', '7z','wav','mp3','mp4','wmv'])) {
                $thumb_src = getImgSrcByExt($data['ext'],$data['path'],true);
            } else{
                $thumb_src = get_thumb_image($data['path'],$style);
            }
            
        }

        return $thumb_src;
    }

}
