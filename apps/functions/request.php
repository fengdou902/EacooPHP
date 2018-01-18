<?php 
// 请求
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------

/**获取网站的根Url
 * @return string
 * @auth 陈一枭
 */
function getRootUrl()
{
    if (BASE_PATH != '') {
        return BASE_PATH . '/';
    }
    return BASE_PATH;
}

/**
 * root_full_path   渲染链接
 * @param $path
 * @return mixed
 * @author:心云间、凝听 <981248356@qq.com>
 */
function root_full_path($path)
{
    //不存在http://
    $not_http_remote = (strpos($path, 'http://') === false);
    //不存在https://
    $not_https_remote = (strpos($path, 'https://') === false);

    if (substr($path,0,1)=='.') {
        $path= substr($path, 1);
    }

    if ($not_http_remote && $not_https_remote) {
        
        //本地url
        return str_replace('//', '/', getRootUrl() . $path); //防止双斜杠的出现
    }  
    return $path;
}

/**
 * 路径转换为url
 * @param  string $value [description]
 * @return [type]        [description]
 */
function path_to_url($path ='')
{
    if($path=='' || !$path) return false;

    if (strpos($path, 'http://')!==false || strpos($path, 'https://')!==false) return $path;//包含http和https的返回原值

    $url = get_cdn_domain().$path;
    return $url;
}

/**
 * 去除URL的参数
 * @param  [type] $url [description]
 * @return [type]      [description]
 */
function clear_urlcan($url){
    $rstr   ='';
    $tmparr =parse_url($url);
    $rstr   =empty($tmparr['scheme'])?'http://':$tmparr['scheme'].'://';
    $rstr   .=$tmparr['host'].$tmparr['path'];
    return $rstr;
}

/**
 * get_ip_lookup  获取ip地址所在的区域
 * @param null $ip
 * @return bool|mixed
 * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
 */
function get_ip_lookup($ip = null)
{
    if (empty($ip)) {
        $ip = get_client_ip(0);
    }
    $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);
    if (empty($res)) {
        return false;
    }
    $jsonMatches = array();
    preg_match('#\{.+?\}#', $res, $jsonMatches);
    if (!isset($jsonMatches[0])) {
        return false;
    }
    $json = json_decode($jsonMatches[0], true);
    if (isset($json['ret']) && $json['ret'] == 1) {
        $json['ip'] = $ip;
        unset($json['ret']);
    } else {
        return false;
    }
    return $json;
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0, $adv = false) {
    $type      = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL) {
        return $ip[$type];
    }

    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }

            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

//todo 扩展多种采集方式，以备一种方式采集不到页面

function get_content_by_url($url){
    $md5 = md5($url);
    $content = cache('file_content_'.$md5);
    if(is_bool($content)){
        $content = curl_file_get_contents($url);
        cache('file_content_'.$md5,$content,60*60);
    }
    return $content;
}


function curl_file_get_contents($durl){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $durl);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, '');
    curl_setopt($ch, CURLOPT_REFERER,'b');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $file_contents = curl_exec($ch);
    curl_close($ch);
    return $file_contents;
}

// 防超时的file_get_contents改造函数
function wp_file_get_contents($url) {
    $context = stream_context_create ( array (
            'http' => array (
                    'timeout' => 30 
            ) 
    ) ); // 超时时间，单位为秒
    
    return file_get_contents ( $url, 0, $context );
}

/**
 * 获取远程文件头信息
 * @param  [type] $uri [description]
 * @param  string $user [description]
 * @param  string $pw [description]
 * @return [type] [description]
 * @date   2017-09-07
 * @author 心云间、凝听 <981248356@qq.com>
 */
function curl_remote_filesize($uri,$user='',$pw='')    
{    
    // start output buffering    
    ob_start();    
    // initialize curl with given uri    
    $ch = curl_init($uri);    
    // make sure we get the header    
    curl_setopt($ch, CURLOPT_HEADER, 1);    
    // make it a http HEAD request    
    curl_setopt($ch, CURLOPT_NOBODY, 1);    
    // if auth is needed, do it here    
    if (!empty($user) && !empty($pw))    
    {    
        $headers = array('Authorization: Basic ' . base64_encode($user.':'.$pw));    
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);    
    }    
    $okay = curl_exec($ch);    
    curl_close($ch);    
    // get the output buffer    
    $head = ob_get_contents();    
    // clean the output buffer and return to previous    
    // buffer settings    
    ob_end_clean();    
    
    echo '<br>head-->'.$head.'<----end <br>';    
    
    // gets you the numeric value from the Content-Length    
    // field in the http header    
    $regex = '/Content-Length:\s([0-9].+?)\s/';    
    $count = preg_match($regex, $head, $matches);    
    
    // if there was a Content-Length field, its value    
    // will now be in $matches[1]    
    if (isset($matches[1]))    
    {    
        $size = $matches[1];    
    }    
    else    
    {    
        $size = 'unknown';    
    }    
    //$last=round($size/(1024*1024),3);    
    //return $last.' MB';    
    return $size;    
}  


/**
 * CURL发送Request请求,含POST和REQUEST
 * @param string $url 请求的链接
 * @param mixed $params 传递的参数
 * @param string $method 请求的方法
 * @param mixed $options CURL的参数
 * @return array
 */
function curl_request($url, $params = [], $method = 'POST', $options = [])
{
    $method = strtoupper($method);
    $protocol = substr($url, 0, 5);
    $query_string = is_array($params) ? http_build_query($params) : $params;

    $ch = curl_init();
    $defaults = [];
    if ('GET' == $method)
    {
        $geturl = $query_string ? $url . (stripos($url, "?") !== FALSE ? "&" : "?") . $query_string : $url;
        $defaults[CURLOPT_URL] = $geturl;
    }
    else
    {
        $defaults[CURLOPT_URL] = $url;
        if ($method == 'POST')
        {
            $defaults[CURLOPT_POST] = 1;
        }
        else
        {
            $defaults[CURLOPT_CUSTOMREQUEST] = $method;
        }
        $defaults[CURLOPT_POSTFIELDS] = $query_string;
    }

    $defaults[CURLOPT_HEADER] = FALSE;
    $defaults[CURLOPT_USERAGENT] = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36";
    $defaults[CURLOPT_FOLLOWLOCATION] = TRUE;
    $defaults[CURLOPT_RETURNTRANSFER] = TRUE;
    $defaults[CURLOPT_CONNECTTIMEOUT] = 3;
    $defaults[CURLOPT_TIMEOUT] = 3;

    // disable 100-continue
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

    if ('https' == $protocol)
    {
        $defaults[CURLOPT_SSL_VERIFYPEER] = FALSE;
        $defaults[CURLOPT_SSL_VERIFYHOST] = FALSE;
    }

    curl_setopt_array($ch, (array) $options + $defaults);

    $result = curl_exec($ch);
    $err = curl_error($ch);

    if (FALSE === $result || !empty($err))
    {
        $errno = curl_errno($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return [
            'status'   => false,
            'errno' => $errno,
            'content'   => $err,
            'info'  => $info,
        ];
    }
    curl_close($ch);
    return [
        'status' => true,
        'content' => $result,
    ];
}

/**
 * curl_post 请求函数
 * @param  [type]  $url          url链接
 * @param  string  $params        请求参数
 * @return [type]                [description]
 */
function curl_post($url, $params ){
    $query_string = is_array($params) ? http_build_query($params) : $params;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $errorno  = curl_errno($ch);
    return $response;
}

/**
 * 获取
 * @param  [type] $url [description]
 * @return [type] [description]
 * @date   2017-09-07
 * @author 心云间、凝听 <981248356@qq.com>
 */
function curl_get($url){
    $ch = curl_init();
    $header[] = "Accept-Charset: utf-8";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);//2017-09-21
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $temp = curl_exec($ch);
    return $temp;
}

// 以POST方式提交数据
function post_data($url, $param, $is_file = false, $return_array = true) {
    if (! $is_file && is_array ( $param )) {
        $param = JSON ( $param );
        //$param=json_encode($param);
    }
    if ($is_file) {
        $header [] = "content-type: multipart/form-data; charset=UTF-8";
    } else {
        $header [] = "content-type: application/json; charset=UTF-8";
    }
    
    $ch = curl_init ();
    curl_setopt ( $ch, CURLOPT_URL, $url );
    curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
    curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
    curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
    curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header );
    curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)' );
    curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
    curl_setopt ( $ch, CURLOPT_AUTOREFERER, 1 );
    curl_setopt ( $ch, CURLOPT_POSTFIELDS, $param );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
    $res = curl_exec ( $ch );
    
    $flat = curl_errno ( $ch );
    if ($flat) {
        $data = curl_error ( $ch );
        addWeixinLog ( $flat, 'post_data flat' );
        addWeixinLog ( $data, 'post_data msg' );
    }
    
    curl_close ( $ch );
    
    $return_array && $res = json_decode ( $res, true );
    
    return $res;
}

/**
 * 是否微信访问
 * @return bool
 * @author 心云间、凝听<981248356@qq.com>
 */
function is_weixin() {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
        return true;
    } else {
        return false;
    }
}