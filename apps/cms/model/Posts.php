<?php
// 内容模型       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2012-2014 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\cms\model;

use app\common\model\Base;

class Posts extends Base {

    protected $insert   = ['status' => 1];
    protected $auto     = ['publish_time'];

    protected $type       = [
        'publish_time' => 'timestamp:Y-m-d H:i',
    ];

    /**
     * 获取摘要内容
     * @param  [type] $post_id [description]
     * @return [type] [description]
     * @date   2017-10-16
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getDigestAttr($value,$data)
    {
    	$excerpt = $data['excerpt'];
    	if (empty($excerpt)) {
    		if(function_exists("mb_strimwidth")){
    			$excerpt = mb_strimwidth(strip_tags($data['content']), 0, 280,"...");
    		} else{
    			$excerpt = eacoo_strimwidth(strip_tags($data['content']),0,280,'...');
    		}
    		
    	}

    	return $excerpt;
    }

    /**
     * 获取缩略图
     * @param  [type] $value [description]
     * @param  [type] $data [description]
     * @return [type] [description]
     * @date   2017-10-16
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getCoverAttr($value,$data)
    {
    	$img = get_image($data['img']);
    	if (!$img) {
    		$img = get_first_pic($data['content']);
    	}

    	return $img;
    }
}