<?php
namespace plugins\Alidayu;
use app\common\controller\Plugin;
/**
 * 阿里大于-短信插件
 */
class Index extends Plugin{

    /**
     * @var array 插件钩子
     */
    public $hooks = [
        'sms'
    ];

    /**
     * 插件安装方法
     */
    public function install(){
        return true;
    }

    /**
     * 插件卸载方法
     */
    public function uninstall(){
        return true;
    }

    /**
     * 短信发送函数
     * @param string $sms_data 短信信息结构
     * @$sms_data['RecNum'] 收件人手机号码
     * @$sms_data['code']验证码内容
     * @$sms_data['SmsFreeSignName']短信签名
     * @$sms_data['SmsTemplateCode']短信模版ID
     * @return boolean
     */
    function sms($RecNum , $code , $SmsFreeSignName , $SmsTemplateCode){
        $config = $this->getConfig('Alidayu');
        if($addon_config['status']){
            include "plugins/Alidayu/sdk/TopSdk.php";
            date_default_timezone_set('Asia/Shanghai'); 
            $SmsParam = json_encode(array('code'=>$code,'product'=>config('web_site_title')));
            $c = new \TopClient;
            $c->method = 'alibaba.aliqin.fc.sms.num.send';
            $c->appkey = $config['appkey'];
            $c->secretKey = $config['secret'];
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
