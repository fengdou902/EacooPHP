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

class EacooException extends Handle
{

    public function render(Exception $e)
    {
        // 参数验证错误
        if ($e instanceof ValidateException) {
            //return json($e->getError(), 422);
        }

        // 请求异常
        if ($e instanceof HttpException && request()->isAjax()) {
            //return response($e->getMessage(), $e->getStatusCode());
        }
        
        //TODO::开发者对异常的操作
        //可以在此交由系统处理
        return parent::render($e);
    }

}