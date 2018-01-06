<?php 
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\model;

/**
 * 配送方式模型
 * @author 心云间、凝听 <981248356@qq.com>
 */
class Payment extends Base {

	// 定义时间戳字段名 
	protected $createTime = '';
    protected $updateTime = '';
    
    //支付方式
    public function getPayTypeAttr($value,$data)
    {
        $status = [1=>'货到付款',2=>'在线支付'];
        return $status[$data['type']];
    }

    /**
     * 获取支付方式列表
     * @return [type] [description]
     */
    public static function getPaymentList()
    {
    	$payment_list = self::all(function($query){
    			$map = [];
                $query->where($map)->order('sort asc');
            });
    	return $payment_list;
    }
}
