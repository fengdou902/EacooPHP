<?php
// 内容模型       
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\cms\model;

use app\common\model\Base;

class Posts extends Base {

    protected $insert   = [];
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

    /**
     * 获取作者名
     * @param  [type] $value [description]
     * @param  [type] $data [description]
     * @return [type] [description]
     * @date   2018-01-06
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getAuthorAttr($value,$data)
    {
        $nickname = '';
        if ($data['author_id']>0) {
            $nickname = db('users')->where('uid',$data['author_id'])->value('nickname');
        }
        return !empty($nickname) ? $nickname : '未知';
    }
}