<?php
// 内容字段模型       
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

class Postmeta extends Base {

	protected $pk = 'meta_id';

	/**
	 * 获取文章字段值
	 * @param  [type] $post_id [description]
	 * @param  [type] $meta_key [description]
	 * @return [type] [description]
	 * @date   2017-10-15
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public static function getMetaValue($post_id,$meta_key)
	{
		$map = [
			'post_id'=>$post_id,
			'meta_key'=>$meta_key
		];
		return $value = self::where($map)->value('meta_value');
	}

	/**
	 * 获取文章的所有meta
	 * @param  [type] $post_id [description]
	 * @return [type] [description]
	 * @date   2017-10-15
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public static function getMetas($post_id)
	{
		$map = [
			'post_id'=>$post_id
		];
		return $data = self::where($map)->field('meta_id,meta_key,meta_value')->select();
	}

	/**
	 * 设置meta
	 * @param  [type] $post_id [description]
	 * @param  [type] $meta_key [description]
	 * @param  string $meta_value [description]
	 * @date   2017-10-15
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function setMeta($post_id,$meta_key,$meta_value='')
	{
		$res = $this->where(['post_id'=>$post_id,'meta_key'=>$meta_key])->count();
		if ($res>0) {
			$res = $this->updateMeta($post_id,$meta_key,$meta_value);
		} else{
			$res = $this->addMeta($post_id,$meta_key,$meta_value);
		}
		return $res;
	}

	/**
	 * 添加文章meta
	 * @param  [type] $post_id [description]
	 * @param  [type] $meta_key [description]
	 * @date   2017-10-15
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function addMeta($post_id,$meta_key,$meta_value='')
	{
		$data = [
			'post_id'=>$post_id,
			'meta_key'=>$meta_key,
			'meta_value'=>$meta_value
		];
		$res = $this->allowField(true)->isUpdate(false)->data($data)->save();
		return $res ? $this->meta_id : false;
	}

	/**
	 * 更新meta
	 * @param  [type] $post_id [description]
	 * @param  [type] $meta_key [description]
	 * @param  string $meta_value [description]
	 * @return [type] [description]
	 * @date   2017-10-15
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function updateMeta($post_id,$meta_key,$meta_value='')
	{
		$map = [
			'post_id'=>$post_id,
			'meta_key'=>$meta_key
		];
		$res = $this->where($map)->update(['meta_value'=>$meta_value]);
		return $res ? true : false;
	}

	/**
	 * 删除meta
	 * @param  [type] $post_id [description]
	 * @param  [type] $meta_key [description]
	 * @return [type] [description]
	 * @date   2017-10-15
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function deleteMeta($post_id,$meta_key)
	{
		$map = [
			'post_id'=>$post_id,
			'meta_key'=>$meta_key
		];
		$res = $this->where($map)->delete();
		return $res ? true : false;
	}
}