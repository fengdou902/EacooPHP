<?php 
namespace app\common\model;
use think\Model;

/**
 * 积分模型
 * @author 心云间、凝听 <981248356@qq.com>
 */
class Score extends Model {
    
    protected $updateTime = false;
    protected $type = [
        // 设置birthday为时间戳类型（整型）
        'create_time' => 'timestamp:Y-m-d H:i:s',
        ];
    
}
