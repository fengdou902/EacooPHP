<?php 
namespace app\wechat\model;

use app\common\model\Base;
/**
 * 微信公众号管理模型
 * @author 心云间、凝听 <981248356@qq.com>
 */
class Material extends Base {
	protected $name = 'wechat_material';

	// 定义时间戳字段名 
    protected $updateTime = '';
	protected $insert =['status'=>1];
}
