<?php
namespace plugins\Alidayu;
use app\common\controller\Plugins;
/**
 * 阿里大鱼短信插件
 */
class Alidayu extends Plugins{
    /**
     * 插件信息
     */
    public $info = [
        'name'        => 'Alidayu',
        'title'       => '阿里大鱼-短信接口',
        'description' => '通过阿里大鱼短信接口发送短信',
        'status'      => 1,
        'author'      => '心云间、凝听',
        'version'     => '1.0'
    ];

    /**
     * 插件所需钩子
     */
    public $hooks = [
        '0' => 'sms',
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
}
