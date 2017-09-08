<?php 

/**
 * 发送创蓝短信
 *
 * @param string $mobile 手机号码
 * @param string $msg 短信内容
 * @param string $needstatus 是否需要状态报告
 * @param string $product 产品id，可选
 * @param string $extno   扩展码，可选
 */
function sendCLSMS($mobile, $msg, $needstatus = 'false', $product = '', $extno = '')
{
	//创蓝接口参数
		$postArr = array (
							'account'    => 'N5676872',
							'pswd'       => 'H7f6BvDZxGac49',
							'msg'        => $msg,
							'mobile'     => $mobile,
							'needstatus' => $needstatus,
							'product'    => $product,
							'extno'      => $extno
                     );
		
		$result = $this->curlPost('http://222.73.117.158/msg/HttpBatchSendSM' , $postArr);
		$result = preg_split("/[,\r\n]/",$result);
		return $result;
}