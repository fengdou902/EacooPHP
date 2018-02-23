<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\Builder;
use think\Db;

/**
 * 排序构建器
 * @package app\admin\builder
 * @author 心云间、凝听 <981248356@qq.com>
 */
class BuilderSort extends Builder {
    private $_meta_title;                  // 页面标题
    private $_list;
    private $_buttonList;
    private $_post_url;              // 表单提交地址
    private $_extra_html;            // 额外功能代码
    private $_ajax_submit = true;    // 是否ajax提交

    /**
     * 设置页面标题
     * @param $title 标题文本
     * @return $this
     */
    public function setMetaTitle($meta_title) {
        $this->_meta_title = $meta_title;
        return $this;
    }
    /**
     * 设置额外功能代码
     * @param $extra_html 额外功能代码
     * @return $this
     */
    public function setExtraHtml($extra_html) {
        $this->_extra_html = $extra_html;
        return $this;
    }

    public function setListData($list) {
        $this->_list = $list;
        return $this;
    }

    public function button($title, $attr = array())
    {
        $this->_buttonList[] = ['title' => $title, 'attr' => $attr];
        return $this;
    }
   
    /**
     * [addButton description]
     * @param  string     $type  按钮类型
     * @param  string     $title [description]
     * @param  string     $url   [description]
     * @date   2017-08-03
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function addButton($type='submit',$title='',$url=''){
        switch ($type) {
            case 'submit'://确认按钮
                if ($url!= '') {
                    $this->setPostUrl($url);
                }
                if ($title == '') {
                    $title ='确定';
                }
                
                $attr = [];
                $attr['class'] = "btn btn-primary submit sort_confirm";
                //$attr['class']="radius ud-button bg-color-blue submit {$ajax_submit} ud-shadow";
                $attr['type'] = 'button';
                $attr['target-form'] = 'sort-builder';
                break;
            case 'back'://返回
                if ($title == '') {
                    $title ='返回';
                }
                $attr = [];
                $attr['onclick'] = 'javascript:history.back(-1);return false;';
                $attr['class'] = 'btn btn-default return';
                //$attr['class'] = 'radius ud-button color-5 submit ud-shadow';
                break;
            case 'link'://链接
                if ($title == '') {
                    $title ='按钮';
                }
                $attr['onclick'] = 'javascript:location.href=\''.$url.'\';return false;';
                break;
            
            default:
                # code...
                break;
        }
        return $this->button($title, $attr);
    }

    /**
     * 设置表单提交地址
     * @param $url 提交地址
     * @return $this
     */
    public function setPostUrl($post_url) {
        $this->_post_url = $post_url;
        return $this;
    }

    public function fetch($template_name='sortbuilder',$vars =[], $replace ='', $config = '') {
        //设置post_url默认值
        $this->_post_url=$this->_post_url? $this->_post_url : url(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
        //编译按钮的属性
        foreach($this->_buttonList as &$e) {
            $e['attr'] = $this->compileHtmlAttr($e['attr']);
        }
        unset($e);

        //显示页面
        $this->assign('meta_title', $this->_meta_title);
        $this->assign('list', $this->_list);
        $this->assign('buttonList', $this->_buttonList);
        $this->assign('post_url', $this->_post_url);
        $templateFile = APP_PATH.'/common/view/builder/'.$template_name.'.html';
        parent::fetch($templateFile);
    }

    /**
     * 处理排序
     * @param  [type] $table [description]
     * @param  [type] $ids [description]
     * @return [type] [description]
     * @date   2018-02-01
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function doSort($table, $ids) {
        $ids = explode(',', $ids);
        $res = 0;
        foreach ($ids as $key=>$value){
            $res += Db::name($table)->where(['id'=>$value])->setField('sort', $key+1);
        }
        if($res) {
            $this->success('排序成功');
        } else {
            $this->error('排序失败');
        }
    }
}