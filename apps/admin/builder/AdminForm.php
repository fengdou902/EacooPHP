<?php
namespace app\admin\builder;

/**
 * 表单构建器
 * @package app\admin\builder
 * @author 心云间、凝听 <981248356@qq.com>
 */
class AdminForm extends Builder
{
    private $_meta_title;            // 页面标题
    private $_sub_title;            // 页面子标题
    private $_tip;         // 页面子标题
    private $_tab_nav     = [];     // 页面Tab导航
    private $_group_tab_nav=[]; //页面Tab分组
    private $_post_url;              // 表单提交地址
    private $_buttonList  = [];    //按钮组
    private $_form_items  = [];  // 表单项目
    private $_extra_items = []; // 额外已经构造好的表单项目
    private $_form_data   = [];   // 表单数据
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
     * 设置页面子标题
     * @param $title 标题文本
     * @return $this
     */
    public function setSubTitle($sub_title) {
        $this->_sub_title = $sub_title;
        return $this;
    }

    /**
     * 设置页面说明
     * @param $title 标题文本
     * @return $this
     */
    public function setTip($content) {
        $this->_tip = $content;
        return $this;
    }

    /**
     * 设置页面说明
     * @param $title 标题文本
     * @return $this
     */
    public function setPageTips($content,$type='info') {
        $this->_tip = $content;
        return $this;
    }

    /**
     * 设置Tab按钮列表
     * @param $tab_list    Tab列表  array('title' => '标题', 'href' => 'http://www.xxx.com')
     * @param $current_tab 当前tab
     * @return $this
     */
    public function setTabNav($tab_list, $current_tab) {
        $this->_tab_nav = ['tab_list' => $tab_list, 'current_tab' => $current_tab];
        return $this;
    }

    /**
     * 组tab
     * @param $tab_list    Tab列表  array('title' => '标题', 'href' => 'http://www.xxx.com')
     * @param $current_tab 当前tab
     * @return $this
     */
    public function setGTabNav($tab_list, $current_tab) {
        $this->_group_tab_nav = ['tab_list' => $tab_list, 'current_tab' => $current_tab];
        return $this;
    }

    public function group($name, $list = array())
    {
        !is_array($list) && $list = explode(',', $list);
        $this->_group_tab_nav[$name] = $list;
        return $this;
    }

    public function groups($list = array())
    {
        foreach ($list as $key => $v) {
            $this->_group_tab_nav[$key] = is_array($v) ? $v : explode(',', $v);
        }
        return $this;
    }
    /**
     * 直接设置表单项数组
     * @param $form_items 表单项数组
     * @return $this
     */
    public function setExtraItems($extra_items) {
        $this->_extra_items = $extra_items;
        return $this;
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

    /**
     * 加入一个表单项
     * @param $type 表单类型(取值参考系统配置form_item_type)
     * @param $title 表单标题
     * @param $description 表单项描述说明
     * @param $name 表单名
     * @param $options 表单options
     * @param $extra_class 表单项是否隐藏
     * @param $extra_attr 表单项额外属性
     * @return $this
     */
    public function addFormItem($name, $type, $title, $description = '',$options = [],$confirm='',$extra_attr = '',$extra_class = '') {
        $item['name']        = $name;
        $item['type']        = $type;
        $item['title']       = $title;
        $item['description'] = $description;
        $item['options']     = $options;
        $item['confirm']     = $confirm;//验证。required必填，
        $item['extra_class'] = $extra_class;
        $item['extra_attr']  = $extra_attr;
        $this->_form_items[] = $item;
        return $this;
    }

    /**
     * 设置表单表单数据
     * @param $form_data 表单数据
     * @return $this
     */
    public function setFormData($form_data) {
        $this->_form_data = $form_data;
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
                
                $ajax_submit='';
                if ($this->_ajax_submit==true) {
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
        $this->_buttonList[] = ['title' => $title, 'attr' => $attr];
        return $this;
    }

    /**
     * 设置提交方式
     * @param $title 标题文本
     * @return $this
     */
    public function setAjaxSubmit($ajax_submit = true) {
        $this->_ajax_submit = $ajax_submit;
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
       if (!empty($this->_extra_items)) {
           $this->_form_items = array_merge($this->_form_items, $this->_extra_items);
       }
        //设置post_url默认值
        $this->_post_url=$this->_post_url? $this->_post_url : $this->url;
        //编译表单值
        if ($this->_form_data) {
            foreach ($this->_form_items as &$item) {
                if (isset($this->_form_data[$item['name']])) {
                    $item['value'] = $this->_form_data[$item['name']];
                }
            }
        }
        
        /**
         * 设置按钮
         */
        if (empty($this->_buttonList)) {
            $this->addButton('submit')->addButton('back');
        }
        //编译按钮的html属性
        foreach ($this->_buttonList as &$button) {
            $button['attr'] = $this->compileHtmlAttr($button['attr']);
        }

        $this->assign('meta_title',  $this->_meta_title);  //页面标题
        $this->assign('sub_title',   $this->_sub_title);          // 页面子标题
        $this->assign('tip',         $this->_tip);          // 页面提示说明
        $this->assign('tab_nav',    $this->_tab_nav);     //页面Tab导航
        $this->assign('group_tab_nav',$this->_group_tab_nav);//页面Tab分组
        $this->assign('post_url',    $this->_post_url);    //标题提交地址
        $this->assign('fieldList',  $this->_form_items);  //表单项目
        $this->assign('ajax_submit', $this->_ajax_submit);//是否ajax提交
        $this->assign('buttonList', $this->_buttonList);//按钮组
        $this->assign('extra_html',  $this->_extra_html);  //额外HTML代码 

        $templateFile = APP_PATH.'/admin/view/builder/'.$template_name.'.html';
        parent::fetch($templateFile);
    }

    /**
     * 字段模版
     * @param  array $field [description]
     * @return [type] [description]
     * @date   2017-10-20
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function fieldType($field=[])
    {
        if (!is_array($field)) {
            $field = $field->toArray();
        }
        $this->assign('field',$field);
        if (PUBLIC_RELATIVE_PATH=='') {
            $template_path_str = '../';
        } else{
            $template_path_str = './';
        }

        $fields_name = ['text','number','info','section','date','datetime','hidden','password','left_icon_text','right_icon_text','left_icon_number','right_icon_number','textarea','ueditor','wangeditor','radio','checkbox','select','select2','select_multiple','tags','multilayer_select','email','region','city','icon','avatar','picture','pictures','image','file','files','repeater','self','self_html'];
        $builder_fields = [];
        foreach ($fields_name as $key => $type) {
            $builder_fields[$type]= $template_path_str.'apps/admin/view/builder/Fields/'.$type.'.html';
        }
        $field_template = isset($builder_fields[$field['type']]) ? $builder_fields[$field['type']] : '';
        parent::fetch($field_template);
    }
}