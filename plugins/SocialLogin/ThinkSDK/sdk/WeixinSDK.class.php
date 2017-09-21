<?php
class WeixinSDK extends ThinkOauth{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $GetRequestCodeURL1 = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    protected $GetRequestCodeURL2 = 'https://open.weixin.qq.com/connect/qrconnect';

    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $GetAccessTokenURL = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    /**
     * API根路径
     * @var string
     */
    protected $ApiBase = 'https://api.weixin.qq.com/';

    public function getRequestCodeURL(){
        $this->config();
        $params = array(
                'appid' => $this->AppKey,
                'redirect_uri'=>$this->Callback,
                'response_type'=>'code',
                'scope'=>'snsapi_login'
        );
        if($this->is_weixin()){
            return $this->GetRequestCodeURL1 . '?' . http_build_query($params);
        }else{
            return $this->GetRequestCodeURL2 . '?' . http_build_query($params);
        }
    }

    /**
     * 获取access_token
     * @param string $code 上一步请求到的code
     */
    public function getAccessToken($code, $extend = null){
        $this->config();
        $params = array(
                'appid'     => $this->AppKey,
                'secret'    => $this->AppSecret,
                'grant_type'    => $this->GrantType,
                'code'          => $code,
        );

        $data = $this->http($this->GetAccessTokenURL, $params, 'POST');
        $this->Token = $this->parseToken($data, $extend);
        return $this->Token;
    }

    /**
     * 组装接口调用参数 并调用接口
     * @param  string $api    微博API
     * @param  string $param  调用API的额外参数
     * @param  string $method HTTP请求方法 默认为GET
     * @return json
     */
    public function call($api, $param = '', $method = 'GET', $multi = false){
        /* 腾讯微博调用公共参数 */
        $params = array(
            'access_token'       => $this->Token['access_token'],
            'openid'             => $this->openid(),
            'lang'               =>'zh-CN',
        );

        $vars = $this->param($params, $param);
        $data = $this->http($this->url($api), $vars, $method, array(), $multi);
        return json_decode($data, true);
    }

    /**
     * 解析access_token方法请求后的返回值
     */
    protected function parseToken($result, $extend){
        $data = json_decode($result,true);
        //parse_str($result, $data);
        if($data['access_token'] && $data['expires_in']){
            $this->Token    = $data;
            $data['openid'] = $this->openid();
            return $data;
        } else {
            cookie('sns_error', "获取ACCESS_TOKEN出错：{$result}");
            return false;
        }
    }

    /**
     * 获取当前授权应用的openid
     */
    public function openid(){
        $data = $this->Token;
        if(!empty($data['openid']))
            return $data['openid'];
        else
            exit('没有获取到微信用户ID！');
    }

    /**
     * 判断浏览器是否是微信
     * @author jry <598821125@qq.com>
     */
    function is_weixin(){
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $is_weixin = strpos($agent, 'micromessenger') ? true : false ;
        if($is_weixin){
            return true;
        }else{
            return false;
        }
    }
}
