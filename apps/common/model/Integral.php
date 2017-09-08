<?php 
namespace app\common\model;
use think\Model;

/**
 * 商品模型
 * @author 心云间、凝听 <981248356@qq.com>
 */
class Integral extends Model {
    
    protected $updateTime = '';
    protected $type = [
        // 设置birthday为时间戳类型（整型）
        'create_time' => 'timestamp:Y-m-d H:i:s',
        ];

    
}
