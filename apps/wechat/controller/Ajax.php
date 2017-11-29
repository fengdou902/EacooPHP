<?php
namespace Weixin\Controller;
use Think\Controller;
class AjaxController extends HomeController {
    protected $pinyin_obj;
    function _initialize()
    {
        parent::_initialize();

        $this->pinyin_obj=new \Common\Util\Pinyin;
    }
   
    
}