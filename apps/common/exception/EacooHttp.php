<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------

namespace app\common\exception;

use Exception;
use think\exception\Handle;
use think\exception\HttpException;

class EacooHttp extends Handle
{

    public function render(Exception $e)
    {
        $code    = $e->getCode();
        $message = $e->getMessage();

        $remote  = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        $method  = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'CLI';
        $uri     = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

        $data = [
            'File'      => $e->getFile(),
            'file_line' => $e->getLine(),
            'remote'    => $remote,
            'method'    => $method,
            'uri'       => $uri
        ];
        // 参数验证错误
        if ($e instanceof ValidateException) {
            //return json($e->getError(), 422);
        }

        // 请求异常
        if ($e instanceof HttpException && request()->isAjax()) {
            //return response($e->getMessage(), $e->getCode());
        }

        $data = [
            'code' => $code,
            'msg'  => $message,
            'data' => $data
        ];

        $now = date('Y-m-d H:i:s');
        $content = "[{$now}] ERROR: ".json_encode($data)."\n";
        $log_file = RUNTIME_PATH."log/exception".DS.'error-'.date('Ymd',time()).".log";
        $path = dirname($log_file);
        !is_dir($path) && mkdir($path, 0755, true);
        file_put_contents($log_file,$content,FILE_APPEND|LOCK_EX);
        //TODO::开发者对异常的操作
        //可以在此交由系统处理
        return parent::render($e);
    }

}