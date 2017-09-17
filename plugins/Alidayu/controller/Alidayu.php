<?php

namespace addons\alidayu\controller;
use app\admin\builder\AdminFormBuilder;
use app\admin\builder\AdminListBuilder;

use app\admin\controller\Addon;
/**
 * 阿里大鱼短信控制器
 */
class Alidayu extends Addon{
    /**
     * 短信发送函数
     * @param string $sms_data 短信信息结构
     * @$sms_data['RecNum'] 收件人手机号码
     * @$sms_data['code']验证码内容
     * @$sms_data['SmsFreeSignName']短信签名
     * @$sms_data['SmsTemplateCode']短信模版ID
     * @return boolean
     */
    function sendSms($RecNum , $code , $SmsFreeSignName , $SmsTemplateCode){
        $addon_config = \Common\Controller\Addon::getConfig('Alidayu');
        if($addon_config['status']){
            include "Addons/Alidayu/sdk/TopSdk.php";
            date_default_timezone_set('Asia/Shanghai'); 
            $SmsParam = json_encode(array('code'=>$code,'product'=>C('WEB_SITE_TITLE')));
            $c = new \TopClient;
            $c->method = 'alibaba.aliqin.fc.sms.num.send';
            $c->appkey = $addon_config['appkey'];
            $c->secretKey = $addon_config['secret'];
            $c->format = "json";
            $req = new \AlibabaAliqinFcSmsNumSendRequest;
            $req->setExtend('123456');
            $req->setSmsType("normal");
            $req->setSmsFreeSignName($SmsFreeSignName);
            $req->setSmsParam($SmsParam);
            $req->setRecNum($RecNum);
            $req->setSmsTemplateCode($SmsTemplateCode);
            $resp = $c->execute($req);
            $return=json_encode($resp);
            if($return['result']['err_code']==0){
                return ture;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}
