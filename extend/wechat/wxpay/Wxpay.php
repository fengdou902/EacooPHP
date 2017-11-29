<?php
namespace wechat\wxpay;
/**
 * jsapi微信支付类
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 定义时区
ini_set('date.timezone','Asia/Shanghai');

class Wxpay {
	// 定义配置项
    private $config = [];

    // 构造函数
    public function __construct($config=[]) {
        // 如果配置项为空 则直接返回
        if (empty($config)) {
            $this->config = config('wxpay');
        } else {
            $this->config = $config;
        }
    }

    /**
     * 获取jssdk需要用到的数据
     * @return array jssdk需要用到的数据
     */
    public function getParameters($out_trade_no = '', $total_fee = 0, $body = ''){
    	// 统一下单 获取prepay_id
    	$unified_order = $this->unifiedOrder($out_trade_no , $total_fee, $body);
    	if (!$unified_order || $unified_order['return_code'] == 'FAIL'){
    		return "<script>alert('对不起，微信支付接口调用错误!" . $unified_order['return_msg'] . "');history.go(-1);</script>";
    	} 
    	
    	// 获取当前时间戳
        $time = time();
        // 组合jssdk需要用到的数据
        $data = [
            'appId'     => $this->config['appid'], //appid
            'timeStamp' => strval($time), //时间戳
            'nonceStr'  => $unified_order['nonce_str'],// 随机字符串
            'package'   => 'prepay_id='.$unified_order['prepay_id'],// 预支付交易会话标识
            'signType'  => 'MD5'//加密方式
        ];
        // 生成签名
        $data['paySign'] = $this->makeSign($data);
        return $data;
    }

	/**
     * 统一下单
     * @param  array $order 订单 必须包含支付所需要的参数 body(产品描述)、total_fee(订单金额)、out_trade_no(订单号)、product_id(产品id)、trade_type(类型：JSAPI，NATIVE，APP)
     */
    protected function unifiedOrder($out_trade_no = '', $total_fee = 0, $body = '')
    {
		$url                      = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		$data['appid']            = $this->config['appid'];
		$data['mch_id']           = $this->config['mchid'];                       //商户号
		$data['device_info']      = 'WEB';
		$data['body']             = $body;
		$data['out_trade_no']     = $out_trade_no;                           //订单号
		$data['total_fee']        = $total_fee;                             //金额
		$data['spbill_create_ip'] = $_SERVER["REMOTE_ADDR"];   //ip地址
		$data['notify_url']       = $this->config['notify'];
		$data['trade_type']       = 'JSAPI';
		$data['openid']           = session('openid');                 //获取保存用户的openid
		$data['nonce_str']        = $this->createNoncestr();
		$data['sign']             = $this->makeSign($data);
		
        $xml = $this->toXml($data);
        $curl = curl_init(); // 启动一个CURL会话
		
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		
        //设置header
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
		
        //要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_POST, TRUE); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xml); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        $tmpInfo = curl_exec($curl); // 执行操作
        curl_close($curl); // 关闭CURL会话
        $arr = $this->xmlToArray($tmpInfo);
        return $arr;
    }
    /**
     *    作用：产生随机字符串，不长于32位
     */
    public function createNoncestr($length = 32){
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    /**
     *    作用：产生随机字符串，不长于32位
     */
    public function randomkeys($length)
    {
        $pattern = '1234567890123456789012345678905678901234';
        $key = null;
        for ($i = 0; $i < $length; $i++) {
            $key .= $pattern{mt_rand(0, 30)};    //生成php随机数
        }
        return $key;
    }
    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function xmlToArray($xml)
    {
        //将XML转为array
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }
    /**
     * 输出xml字符
     * @throws WxPayException
     **/
    public function toXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    protected function makeSign($arr)
    {
        //签名步骤一：按字典序排序参数
        ksort($arr);
        $string = $this->toUrlParams($arr);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $this->makesign;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }
    /**
     * 格式化参数格式化成url参数
     */
    protected function toUrlParams($arr)
    {
        $buff = "";
        foreach ($arr as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }

}