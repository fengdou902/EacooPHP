<?php 

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
    			if (is_weixin()) {
    				$map['pay_code']=['neq','alipay'];
    			} else{
    				$map['pay_code']=['neq','wxpay'];
    			}
                $query->where($map)->order('sort asc');
            });
    	return $payment_list;
    }
}
