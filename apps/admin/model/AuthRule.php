<?php
// 权限模型       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2016 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author: 心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use app\common\model\Base;

class AuthRule extends Base
{
	// 设置完整的数据表（包含前缀）
    // protected $table = 'think_access';

    // 设置数据表（不含前缀）
    // protected $name = 'auth_rule';

	// 设置birthday为时间戳类型（整型）
    // protected $type       = [
    //     'birthday' => 'timestamp',
    // ];

    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';

}