<?php
namespace plugins\ImageSlider;
use app\common\controller\Plugins;

/**
 * 图片轮播插件
 * @author birdy
 */

class ImageSlider extends Plugins {

    public $info = [
        'name'            => 'ImageSlider',
        'title'           => '图片轮播',
        'description'     => '图片轮播',
        'status'          => 1,
        'has_adminManage' => 0,
        'author'          => '心云间、凝听',
        'version'         => '1.0'
    ];

    /**
     * 插件所需钩子
     */
    public $hooks = ['ImageSlider'];

    public function install(){
        return true;
    }

    public function uninstall(){
        //删除钩子
        //$this->deleteHook($this->info['name']);
        return true;
    }
    
    //实现的ImageSlider钩子方法
    public function ImageSlider($param){
        $config = $this->getConfig();
        if($config['status']){
            $images = [];
            if($config['images']){
                $images = db("attachment")->field('id,path')->where("id in ({$config['images']})")->select();
            }
            $this->assign('urls', explode("\r\n",$config['url']));
            $this->assign('images', $images);
            $this->assign('config', $config);
            return $this->fetch($config['type']);
        }
    }
}