<?php
// 验证基类
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\validate;

/**
* 设置模型
*/
class Base extends \think\Validate{


	protected function requireIn($value, $rule, $data){
		if (is_string($rule)) {
			$rule = explode(',', $rule);
		}else{
			return true;
		}
		$field = array_shift($rule);
		$val = $this->getDataValue($data, $field);
		if (!in_array($val, $rule) && $value == '') {
			return false;
		} else {
			return true;
		}
	}
}