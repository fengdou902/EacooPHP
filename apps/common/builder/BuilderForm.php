<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.eacoophp.com, All rights reserved.
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\builder;

/**
 * 表单构建器
 * @package app\common\builder
 * @author 心云间、凝听 <981248356@qq.com>
 */
class BuilderForm extends Builder
{
    private $tabNav     = [];     // 页面Tab导航
    private $groupTabNav=[]; //页面Tab分组
    private $postUrl;              // 表单提交地址
    private $buttonList  = [];    //按钮组
    private $formItems  = [];  // 表单项目
    private $extraItems = []; // 额外已经构造好的表单项目
    private $formData   = [];   // 表单数据
    private $extraHtml;            // 额外功能代码
    private $ajaxSubmit = true;    // 是否ajax提交
    protected $fieldsItemsList = ['text','number','url','info','section','date','datetime','daterange','hidden','readonly','password','left_icon_text','right_icon_text','left_icon_number','right_icon_number','textarea','ueditor','wangeditor','radio','checkbox','select','select2','select_multiple','tags','multilayer_select','email','group','icon','avatar','picture','pictures','image','file','files','repeater','self','self_html','tab'];

    /**
     * 设置Tab按钮列表
     * @param $tab_list    Tab列表  array('title' => '标题', 'href' => 'http://www.xxx.com')
     * @param $current 当前tab
     * @return $this
     */
    public function setTabNav($tab_list, $current) {
        $this->tabNav = ['tab_list' => $tab_list, 'current' => $current];
        return $this;
    }

    /**
     * 组tab
     * @param $tab_list    Tab列表  array('title' => '标题', 'href' => 'http://www.xxx.com')
     * @param $current 当前tab
     * @return $this
     */
    public function setGTabNav($tab_list, $current) {
        $this->groupTabNav = ['tab_list' => $tab_list, 'current' => $current];
        return $this;
    }

    public function group($name, $list = array())
    {
        !is_array($list) && $list = explode(',', $list);
        $this->groupTabNav[$name] = $list;
        return $this;
    }

    public function groups($list = array())
    {
        foreach ($list as $key => $v) {
            $this->groupTabNav[$key] = is_array($v) ? $v : explode(',', $v);
        }
        return $this;
    }
    /**
     * 直接设置表单项数组
     * @param $form_items 表单项数组
     * @return $this
     */
    public function setExtraItems($extra_items) {
        $this->extraItems = $extra_items;
        return $this;
    }

    /**
     * 设置表单提交地址
     * @param $url 提交地址
     * @return $this
     */
    public function setPostUrl($post_url) {
        $this->postUrl = $post_url;
        return $this;
    }

    /**
     * 加入一个表单项
     * @param $name 字段名
     * @param $type 表单类型(取值参考系统配置form_item_type)
     * @param $title 表单标题
     * @param $description 表单项描述说明
     * @param $options 表单options
     * @param $confirm 验证规则
     * @param $extra_attr 表单项额外属性
     * @param $extra_class 表单项是否隐藏
     * @return $this
     */
    public function addFormItem($name, $type, $title, $description = '', $options = [], $extra_attr = '', $extra_class = '') {
        $item = [
            'name'        => $name,
            'type'        => $type,
            'title'       => $title,
            'description' => $description,
            'options'     => $options,
            'extra_attr'  => $extra_attr,
            'extra_class' => $extra_class
        ];
        $this->formItems[] = $item;
        
        return $this;
    }

    /**
     * 设置表单表单数据
     * @param $form_data 表单数据
     * @return $this
     */
    public function setFormData($form_data) {
        $this->formData = $form_data;
        return $this;
    }

    /**
     * 设置额外功能代码
     * @param $extra_html 额外功能代码
     * @return $this
     */
    public function setExtraHtml($extra_html) {
        $this->extraHtml = $extra_html;
        return $this;
    }

    /**
    *添加按钮
    *@param $type 按钮类型
    *@param $title 按钮标题
    *@param $title 提交地址
    *@return $this
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
                
                $ajax_submit = '';
                if ($this->ajaxSubmit==true) {
                    $ajax_submit='ajax-post';
                }
                $attr = [];
                $attr['class'] = "btn btn-block btn-primary submit {$ajax_submit} ";
                //$attr['class']="radius ud-button bg-color-blue submit {$ajax_submit} ud-shadow";
                $attr['type'] = 'submit';
                $attr['target-form'] = 'form-builder';
                break;
            case 'back'://返回
                if ($title == '') {
                    $title ='返回';
                }
                $attr = array();
                $attr['onclick'] = 'javascript:history.back(-1);return false;';
                $attr['class'] = 'btn btn-block btn-default return';
                //$attr['class'] = 'radius ud-button color-5 submit ud-shadow';
                break;
            case 'reset'://重置
                if ($title == '') {
                    $title ='重置';
                }
                $attr = [];
                $attr['onclick'] = 'javascript:document.getElementById("form1").reset();return false;';
                $attr['class'] = 'btn btn-block btn-warning';
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
     * 添加按钮
     * @param  [type] $title [description]
     * @param  array  $attr  [description]
     * @return [type]        [description]
     */
    public function button($title, $attr = [])
    {
        $this->buttonList[] = ['title' => $title, 'attr' => $attr];
        return $this;
    }

    /**
     * 设置提交方式
     * @param $title 标题文本
     * @return $this
     */
    public function setAjaxSubmit($ajax_submit = true) {
        $this->ajaxSubmit = $ajax_submit;
        return $this;
    }

    /**
     * @param  string $template_name 模板名
     * @param  array $vars 模板变量
     * @param  string $replace
     * @param  string $config
     * @return parent::fetch('formbuilder');
     */
    public function fetch($template_name='formbuilder',$vars =[], $replace ='', $config = '') {
         //额外已经构造好的表单项目与单个组装的的表单项目进行合并
       if (!empty($this->extraItems)) {
           $this->formItems = array_merge($this->formItems, $this->extraItems);
       }

       //过来表单项
       $this->formItems = $this->buildFormItems($this->formItems);
       
        //设置post_url默认值
        $this->postUrl=$this->postUrl? $this->postUrl : $this->url;
        //编译表单值
        if ($this->formData) {
            foreach ($this->formItems as &$item) {
                if ($item['type']!='group') {
                    if ($item['name']!='') {
                        if (isset($this->formData[$item['name']])) {
                            $item['value'] = $this->formData[$item['name']];
                        }
                    }
                } else{
                    foreach ($item['options'] as $gkey => $gvalue) {
                        // if (isset($this->formData[$item['name']])) {
                        //     $item['value'] = $this->formData[$item['name']];
                        // }
                    }
                }
                
            }
        }
        
        /**
         * 设置按钮
         */
        if (empty($this->buttonList)) {
            $this->addButton('submit')->addButton('back');
        }

        //编译按钮的html属性
        foreach ($this->buttonList as &$button) {
            $button['attr'] = $this->compileHtmlAttr($button['attr']);
        }

        $template_val = [
            'tab_nav'         => $this->tabNav,// 页面Tab导航
            'grouptabNav'     => $this->groupTabNav,//页面Tab分组
            'post_url'        => $this->postUrl,//表单提交地址
            'fieldList'       => $this->formItems,//表单项目
            'button_list'     => $this->buttonList,//按钮组
            'extra_html'      => $this->extraHtml//额外HTML代码 
        ];
        $this->assign($template_val);

        $templateFile = APP_PATH.'/common/view/builder/'.$template_name.'.html';
        return parent::fetch($templateFile);
    }

    /**
     * 构建表单项数据builderFormItems
     * @param  [type] $formItems [description]
     * @return [type] [description]
     * @date   2018-10-19
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function buildFormItems($formItems = [])
    {
        if (!$formItems) {
            return false;
        }

        foreach ($formItems as $key => &$item) {
           if (!in_array($item['type'], $this->fieldsItemsList)) {
                //unset($formItems[$key]);
                $item['FormBuilderExtend']='FormBuilderExtend';//扩展字段
                continue;
            }
            switch ($item['type']) {
                case 'hidden':
                    $item['extra_class']='hide';
                    break;
                case 'picture':
                    $item['extra']=[
                        'field_body_class'=>'col-md-6',
                        'field_help_block_class'=>'col-md-6 col-md-offset-2 hide',
                        'field_body_extra'=>'style="padding-bottom: 5px;padding-left: 5px;"'
                    ];
                    break;
                case 'pictures':
                    $item['extra']=[
                        'field_body_class'=>'col-md-8',
                        'field_help_block_class'=>'col-md-6 col-md-offset-2 hide',
                        'field_body_extra'=>'style="padding-bottom: 5px;padding-left: 5px;"'
                    ];
                    break;
                case 'files':
                    $item['extra']=[
                        'field_body_class'=>'col-md-6',
                        'field_help_block_class'=>'col-md-6 col-md-offset-2 hide',
                        'field_body_extra'=>''
                    ];
                    break;
                case 'repeater':
                    $item['extra']=[
                        'field_body_class'=>'col-md-10',
                        'field_help_block_class'=>'col-md-6 col-md-offset-2',
                    ];
                    break;
                case 'wangeditor':
                    $item['extra']=[
                        'field_body_class'=>'col-md-10',
                        'field_help_block_class'=>'col-md-6 col-md-offset-2',
                    ];
                    break;
                case 'ueditor':
                    $item['extra']=[
                        'field_body_class'=>'col-md-10',
                        'field_help_block_class'=>'col-md-6 col-md-offset-2',
                    ];
                    break;
                case 'radio':
                    $item['extra']=[
                        'field_body_class'=>'col-md-8',
                        'field_help_block_class'=>'col-md-8 col-md-offset-2',
                    ];
                    break;
                case 'textarea':
                    $item['extra']=[
                        'field_body_class'=>'col-md-6',
                        'field_help_block_class'=>'col-md-6 col-md-offset-2',
                    ];
                    break;
                case 'self':
                    $item['extra']=[
                        'field_body_class'=>'col-md-10',
                        'field_help_block_class'=>'hide',
                    ];
                    break;
                
                default:
                    # code...
                    break;
            }
       }

       return $formItems;
    }

    /**
     * 字段模版
     * @param  array $field [description]
     * @return [type] [description]
     * @date   2017-10-20
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function fieldType($field = [])
    {
        if (!is_array($field)) {
            $field = $field->toArray();
        }
        
        $template_path_str = '../';
        $field_type = $field['type'];

        $this->assign('field',$field);
        if (in_array($field_type, $this->fieldsItemsList)) {//为了兼容库中，要做校验
            $field_template = $template_path_str.'apps/common/view/builder/Fields/'.$field_type.'.html';
            return parent::fetch($field_template);
        } else{
            hook('FormBuilderExtend', ['field' => $field]);
        }  
    }
}