<?php
namespace plugins\ImageGallery;
use app\common\controller\Plugin;

/**
 * 图片轮播插件
 * @author birdy
 */
class Index extends Plugin {

    /**
     * @var array 插件钩子
     */
    public $hooks = [
        'ImageGallery',
        'PageHeader',
        'PageFooter'
    ];

    public function install(){
        return true;
    }

    public function uninstall(){
        //删除钩子
        return true;
    }
    
    //实现的ImageSlider钩子方法
    public function ImageGallery($param){
        $config = $this->getConfig();
        if($config['status']){
            $sliders = $config['sliders'];
            $this->assign('sliders', $sliders);
            $this->assign('config', $config);
            
            echo $this->fetch($config['type']);
        }
    }
}