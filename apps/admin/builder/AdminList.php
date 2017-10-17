<?php
namespace app\admin\builder;

/**
 * 列表构建器
 * @package app\admin\builder
 * @author 心云间、凝听 <981248356@qq.com>
 */
class AdminList extends Builder
{
    private $_meta_title;                  // 页面标题
    private $_sub_title;                    // 页面子标题
    private $_tip;         // 页面子标题
    private $_head_button_list    = [];   // 顶部工具栏按钮组
    private $_foot_button_list    = [];     // 底部工具栏按钮组
    private $_select              = [];             //添加下拉框
    private $_search              = [];           // 搜索参数配置
    private $_tab_nav             = [];           // 页面Tab导航
    private $_table_column_list   = [];    //表格数据标题
    private $_table_data_list     = []; // 表格数据列表
    private $_table_data_list_key = 'id';  // 表格数据列表主键字段名
    private $_table_data_page;             // 表格数据分页
    private $_right_button_list   = []; // 表格右侧操作按钮组
    private $_alter_data_list     = [];   // 表格数据列表重新修改的项目
    private $_extra_html;                  // 额外功能代码
    private $_right_button_type   = 1;  //右边按钮类型
    //private $_template;                    // 模版

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
     * 加入一个列表顶部工具栏按钮
     * 在使用预置的几种按钮时，比如我想改变新增按钮的名称
     * 那么只需要$builder->addTopButton('add', array('title' => '换个马甲'))
     * 如果想改变地址甚至新增一个属性用上面类似的定义方法
     * @param string $type 按钮类型，主要有add/resume/forbid/recycle/restore/delete/self七几种取值
     * @param array  $attr 按钮属性，一个定了标题/链接/CSS类名等的属性描述数组
     * @return $this
     */
    public function addTopBtn($type, $attribute = null) {
        return $this->addBtn('head',$type, $attribute);
    }

    //添加顶部按钮，用法同上（兼容性）
    public function addTopButton($type, $attribute = null) {
        return $this->addBtn('head',$type, $attribute);
    }

    //添加底部按钮，用法同上
    public function addFootBtn($type, $attribute = null) {
        return $this->addBtn('foot',$type, $attribute);
    }
    
    //添加按钮
    public function addBtn($position='head',$type, $attribute = null){

        switch ($type) {
            case 'addnew':  // 添加新增按钮
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '新增';
                $my_attribute['class'] = 'btn btn-primary btn-sm';
                $my_attribute['href']  = url(MODULE_NAME.'/'.CONTROLLER_NAME.'/edit');

                /**
                * 如果定义了属性数组则与默认的进行合并
                * 用户定义的同名数组元素会覆盖默认的值
                * 比如$builder->addTopButton('add', array('title' => '换个马甲'))
                * '换个马甲'这个碧池就会使用山东龙潭寺的十二路谭腿第十一式“风摆荷叶腿”
                * 把'新增'踢走自己霸占title这个位置，其它的属性同样道理
                */
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                break;
            case 'resume':  // 添加启用按钮(禁用的反操作)
                //预定义按钮属性以简化使用
                $my_attribute['title'] = '启用';
                $my_attribute['target-form'] = 'ids';
                $my_attribute['class'] = 'btn btn-success ajax-post confirm btn-sm';
                $my_attribute['model'] = $attribute['model'] ? : CONTROLLER_NAME;;  // 要操作的数据模型
                $my_attribute['href']  = url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'resume',
                        'model' => $my_attribute['model']
                    )
                );

                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的新增按钮
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                break;
            case 'forbid':  // 添加禁用按钮(启用的反操作)
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '禁用';
                $my_attribute['target-form'] = 'ids';
                $my_attribute['class'] = 'btn btn-warning ajax-post confirm btn-sm';
                $my_attribute['model'] = !empty($attribute['model']) ? $attribute['model']: CONTROLLER_NAME;
                $my_attribute['href']  = url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'forbid',
                        'model' => $my_attribute['model']
                    )
                );

                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的新增按钮
                if (!empty($attribute) && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                break;
            case 'recycle':  // 添加回收按钮(还原的反操作)
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '回收';
                $my_attribute['target-form'] = 'ids';
                $my_attribute['class'] = 'btn btn-danger ajax-post confirm btn-sm';
                $my_attribute['model'] = $attribute['model'] ? : CONTROLLER_NAME;
                $my_attribute['href']  = url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'recycle',
                        'model' => $my_attribute['model']
                    )
                );

                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的新增按钮
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                break;
            case 'restore':  // 添加还原按钮(回收的反操作)
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '还原';
                $my_attribute['target-form'] = 'ids';
                $my_attribute['class'] = 'btn btn-success ajax-post confirm btn-sm';
                $my_attribute['model'] = $attribute['model'] ? : CONTROLLER_NAME;
                $my_attribute['href']  = url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'restore',
                        'model' => $my_attribute['model']
                    )
                );

                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的新增按钮
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                break;
            case 'delete': // 添加删除按钮(我没有反操作，删除了就没有了，就真的找不回来了)
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '删除';
                $my_attribute['target-form'] = 'ids';
                $my_attribute['class'] = 'btn btn-danger ajax-post confirm btn-sm';
                $my_attribute['model'] = isset($attribute['model']) && $attribute['model'] ? $attribute['model']: CONTROLLER_NAME;
                $my_attribute['href']  = url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'delete',
                        'model' => $my_attribute['model']
                    )
                );

                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的新增按钮
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                break;
            case 'sort':  // 添加排序按钮
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '<i class="fa fa-sort"></i> 排序';
                $my_attribute['name'] = '排序';
                $my_attribute['class'] = 'btn btn-info btn-sm';
                $my_attribute['href']  = url(MODULE_NAME.'/'.CONTROLLER_NAME.'/sort');

                /**
                * 如果定义了属性数组则与默认的进行合并
                * 用户定义的同名数组元素会覆盖默认的值
                * 比如$builder->addTopButton('add', array('title' => '换个马甲'))
                * '换个马甲'这个碧池就会使用山东龙潭寺的十二路谭腿第十一式“风摆荷叶腿”
                * 把'新增'踢走自己霸占title这个位置，其它的属性同样道理
                */
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                break;
            case 'self': //添加自定义按钮(第一原则使用上面预设的按钮，如果有特殊需求不能满足则使用此自定义按钮方法)
                // 预定义按钮属性以简化使用
                $my_attribute['target-form'] = 'ids';
                $my_attribute['class'] = 'btn btn-danger btn-sm';

                // 如果定义了属性数组则与默认的进行合并
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                } else {
                    $my_attribute['title'] = '该自定义按钮未配置属性';
                }

                break;
        }
        // 这个按钮定义好了把它丢进按钮池里
        if ($position=='head') {
            $this->_head_button_list[] = $my_attribute;
        }elseif ($position=='foot') {
            $this->_foot_button_list[] = $my_attribute;
        }
        
        return $this;
    }
    /**
     * 添加筛选功能
     * @param string $title 标题
     * @param string $name 键名
     * @param string $type 类型，默认文本
     * @param string $des 描述
     * @param        $attr  标签文本
     * @param string $arrdb 择筛选项数据来源
     * @param string $arrvalue 筛选数据（包含ID 和value的数组:array(array('id'=>1,'value'=>'系统'),array('id'=>2,'value'=>'项目'),array('id'=>3,'value'=>'机构'));）
     * @return $this
     */
    public function addSelect($title = '筛选', $name = 'key', $arrvalue = null)
    {

        $this->_select[] = array('title' => $title, 'name' => $name,'arrvalue' => $arrvalue);

        return $this;
    }
    /**
     * 设置搜索参数
     * @param $title
     * @param $url
     * @return $this
     */
    public function setSearch($title, $url='') {
        if(!$url){
            $url=url(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
        }
        $this->_search = array('title' => $title, 'url' => $url);
        return $this;
    }

    /**
     * 设置Tab按钮列表
     * @param $tab_list Tab列表  array(
     *                               'title' => '标题',
     *                               'href' => 'http://www.xxx.cn'
     *                           )
     * @param $current_tab 当前tab
     * @return $this
     */
    public function setTabNav($tab_list, $current_tab) {
        $this->_tab_nav = [
            'tab_list'    => $tab_list,
            'current_tab' => $current_tab
        ];
        return $this;
    }

    /**
     * 加一个表格字段
     */
    public function keyListItem($name, $title, $type = null, $param = null,$extra_attr=null) {
        $column = array(
            'name'  => $name,
            'title' => $title,
            'type'  => $type,
            'param' => $param,
            'extra_attr'=>$extra_attr
        );
        $this->_table_column_list[] = $column;
        return $this;
    }

    /**
     * 表格数据列表
     */
    public function setListData($table_data_list) {
        $this->_table_data_list = $table_data_list;
        return $this;
    }

    /**
     * 表格数据列表的主键名称
     */
    public function setListDataKey($table_data_list_key) {
        $this->_table_data_list_key = $table_data_list_key;
        return $this;
    }

    /**
     * 加入一个数据列表右侧按钮
     * 在使用预置的几种按钮时，比如我想改变编辑按钮的名称
     * 那么只需要$builder->addRightpButton('edit', array('title' => '换个马甲'))
     * 如果想改变地址甚至新增一个属性用上面类似的定义方法
     * 因为添加右侧按钮的时候你并没有办法知道数据ID，于是我们采用__data_id__作为约定的标记
     * __data_id__会在display方法里自动替换成数据的真实ID
     * @param string $type 按钮类型，edit/forbid/recycle/restore/delete/self六种取值
     * @param array  $attr 按钮属性，一个定了标题/链接/CSS类名等的属性描述数组
     * @return $this
     */
    public function addRightButton($type, $attribute = null) {
        switch ($type) {
            case 'edit':  // 编辑按钮
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '编辑';
                $my_attribute['class'] = $this->_right_button_type==1 ? 'btn btn-primary btn-xs':'';
                $my_attribute['href']  = url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/edit',
                    array($this->_table_data_list_key => '__data_id__')
                );

                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的顶部按钮
                /**
                * 如果定义了属性数组则与默认的进行合并
                * 用户定义的同名数组元素会覆盖默认的值
                * 比如$builder->addRightButton('edit', array('title' => '换个马甲'))
                * '换个马甲'这个碧池就会使用山东龙潭寺的十二路谭腿第十一式“风摆荷叶腿”
                * 把'新增'踢走自己霸占title这个位置，其它的属性同样道理
                */
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                // 这个按钮定义好了把它丢进按钮池里
                $this->_right_button_list[] = $my_attribute;
                break;
            case 'forbid':  // 改变记录状态按钮，会更具数据当前的状态自动选择应该显示启用/禁用
                //预定义按钮属
                $my_attribute['type'] = 'forbid';
                $my_attribute['model'] = !empty($attribute['model']) ? $attribute['model'] : CONTROLLER_NAME;
                $my_attribute['0']['title'] = '启用';
                $my_attribute['0']['class'] = $this->_right_button_type==1 ? 'btn btn-success btn-xs ajax-get confirm':'ajax-get confirm';
                $my_attribute['0']['href']  = url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'resume',
                        'ids' => '__data_id__',
                        'model' => $my_attribute['model']
                    )
                );
                $my_attribute['1']['title'] = '禁用';
                $my_attribute['1']['class'] = $this->_right_button_type==1 ? 'btn btn-warning btn-xs ajax-get confirm':'ajax-get confirm';
                $my_attribute['1']['href']  = url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'forbid',
                        'ids' => '__data_id__',
                        'model' => $my_attribute['model']
                    )
                );

                // 这个按钮定义好了把它丢进按钮池里
                $this->_right_button_list[] = $my_attribute;
                break;
            case 'hide':  // 改变记录状态按钮，会更具数据当前的状态自动选择应该显示隐藏/显示
                // 预定义按钮属
                $my_attribute['type'] = 'hide';
                $my_attribute['model'] = !empty($attribute['model']) ? $attribute['model'] : CONTROLLER_NAME;
                $my_attribute['2']['title'] = '显示';
                $my_attribute['2']['class'] = $this->_right_button_type==1 ? 'btn btn-success btn-xs ajax-get confirm':'ajax-get confirm';
                $my_attribute['2']['href']  = url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'show',
                        'ids' => '__data_id__',
                        'model' => $my_attribute['model']
                    )
                );
                $my_attribute['1']['title'] = '隐藏';
                $my_attribute['1']['class'] = $this->_right_button_type==1 ? 'btn btn-info btn-xs ajax-get confirm':'ajax-get confirm';
                $my_attribute['1']['href']  = url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'hide',
                        'ids' => '__data_id__',
                        'model' => $my_attribute['model']
                    )
                );

                // 这个按钮定义好了把它丢进按钮池里
                $this->_right_button_list[] = $my_attribute;
                break;
            case 'recycle':
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '回收';
                $my_attribute['class'] = $this->_right_button_type==1 ? 'btn btn-danger btn-xs ajax-get confirm':'ajax-get confirm';
                $my_attribute['model'] = $attribute['model'] ? : CONTROLLER_NAME;
                $my_attribute['href'] = url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'recycle',
                        'ids' => '__data_id__',
                        'model' => $my_attribute['model']
                    )
                );

                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的顶部按钮
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                // 这个按钮定义好了把它丢进按钮池里
                $this->_right_button_list[] = $my_attribute;
                break;
            case 'restore':
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '还原';
                $my_attribute['class'] = $this->_right_button_type==1 ? 'btn btn-success btn-xs ajax-get confirm':'ajax-get confirm';
                $my_attribute['model'] = $attribute['model'] ? : CONTROLLER_NAME;
                $my_attribute['href'] = url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'restore',
                        'ids' => '__data_id__',
                        'model' => $my_attribute['model']
                    )
                );

                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的顶部按钮
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                // 这个按钮定义好了把它丢进按钮池里
                $this->_right_button_list[] = $my_attribute;
                break;
            case 'delete':
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '删除';
                $my_attribute['class'] = $this->_right_button_type==1 ? 'btn btn-danger btn-xs ajax-get confirm':'ajax-get confirm';
                $my_attribute['model'] = $attribute['model'] ? : CONTROLLER_NAME;
                $my_attribute['href'] = url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'delete',
                        'ids' => '__data_id__',
                        'model' => $my_attribute['model']
                    )
                );

                // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的顶部按钮
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                }

                // 这个按钮定义好了把它丢进按钮池里
                $this->_right_button_list[] = $my_attribute;
                break;
            case 'self':
                // 预定义按钮属性以简化使用
                $my_attribute['class'] = '';

                // 如果定义了属性数组则与默认的进行合并
                if ($attribute && is_array($attribute)) {
                    $my_attribute = array_merge($my_attribute, $attribute);
                } else {
                    $my_attribute['title'] = '该自定义按钮未配置属性';
                }

                // 这个按钮定义好了把它丢进按钮池里
                $this->_right_button_list[] = $my_attribute;
                break;
        }
        return $this;
    }

    /**
     * 设置分页
     * @param $page
     * @return $this
     */
    public function setListPage($page,$r=20) {
        $this->_table_data_page = $page;
        return $this;
    }

    /**
     * 修改列表数据
     * 有时候列表数据需要在最终输出前做一次小的修改
     * 比如管理员列表ID为1的超级管理员右侧编辑按钮不显示删除
     * @param $page
     * @return $this
     */
    public function alterListData($condition, $alter_data) {
        $this->_alter_data_list[] = array(
            'condition' => $condition,
            'alter_data' => $alter_data
        );
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
     * 设置列表按钮类型
     * @param $type 类型(1 label,2 下拉,3,链接)
     * @return $this
     */
    public function setRightButton($type) {
        $this->_right_button_type = $type;
        return $this;
    }

    /**
     * 显示页面
     */
    public function fetch($templateFile='listbuilder',$vars ='', $replace ='', $config = '') {
        // 编译data_list中的值
        foreach ($this->_table_data_list as &$data) {
            if(!isset($data['right_button'])) $data['right_button']='';
            // 编译表格右侧按钮
            if ($this->_right_button_list) {
                if ($this->_right_button_type==2) {
                    $data['right_button'] .='<div class="btn-group">
                      <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                        操作 <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu" role="menu">';
                }
                
                foreach ($this->_right_button_list as $right_button) {
                    // 禁用按钮与隐藏比较特殊，它需要根据数据当前状态判断是显示禁用还是启用
                    if (isset($right_button['type'])) {
                        if ($right_button['type'] === 'forbid' || $right_button['type'] === 'hide'){
                            $right_button = $right_button[$data['status']];
                        }
                    }

                    // 将约定的标记__data_id__替换成真实的数据ID
                    $right_button['href'] = preg_replace(
                        '/__data_id__/i',
                        $data[$this->_table_data_list_key],
                        $right_button['href']
                    );

                    if (isset($right_button['name']) && $right_button['name']) {//单独修改name(ZhaoJunfeng)
                       $right_button_href_name=$right_button['name'];
                    }else{
                        $right_button_href_name=$right_button['title'];
                    }
                    unset($right_button['name']);
                    // 编译按钮属性
                    $right_button['attribute'] = $this->compileHtmlAttr($right_button);
                    switch ($this->_right_button_type) {
                        case 2:
                            $data['right_button'] .= '<li><a '.$right_button['attribute'].'>'.$right_button_href_name.'</a></li>';
                            break;
                        
                        default:
                            $data['right_button'] .= '<a '.$right_button['attribute'].' style="margin-right:6px;">'.$right_button_href_name.'</a>';
                            break;
                    }
                    
                }
                if ($this->_right_button_type==2) {
                    $data['right_button'] .='</ul></div>';
                }
            }

            // 根据表格标题字段指定类型编译列表数据
            foreach ($this->_table_column_list as &$column) {
                switch ($column['type']) {
                    case 'status':
                        switch($data[$column['name']]){
                            case '-1':
                                $data[$column['name']] = '<span class="fa fa-trash text-danger"></span>';
                                break;
                            case '0':
                                $data[$column['name']] = '<span class="fa fa-ban text-danger"></span>';
                                break;
                            case '1':
                                $data[$column['name']] = '<span class="fa fa-check text-success"></span>';
                                break;
                            case '2':
                                $data[$column['name']] = '<span class="fa fa-eye-slash text-warning"></span>';
                                break;
                        }
                        break;
                    case 'bool':
                        switch($data[$column['name']]){
                            case '0':
                                $data[$column['name']] = '<span class="fa fa-ban text-danger"></span>';
                                break;
                            case '1':
                                $data[$column['name']] = '<span class="fa fa-check text-success"></span>';
                                break;
                        }
                        break;
                    case 'byte':
                        $data[$column['name']] = format_bytes($data[$column['name']]);
                        break;
                    case 'icon':
                        $data[$column['name']] = '<i class="fa '.$data[$column['name']].'"></i>';
                        break;
                    case 'date':
                        $data[$column['name']] = time_format($data[$column['name']], 'Y-m-d');
                        break;
                    case 'datetime':
                        $data[$column['name']] = time_format($data[$column['name']]);
                        break;
                    case 'time':
                        $data[$column['name']] = time_format($data[$column['name']]);
                        break;
                    case 'avatar':
                        if (!$data[$column['name']] || empty($data[$column['name']])) {
                            $data[$column['name']] = config('view_replace_str.__PUBLIC__').'/img/default-avatar.svg';
                        }
                        $data[$column['name']] = '<img style="width:40px;height:40px;" src="'.path_to_url($data[$column['name']]).'">';
                        break;
                    case 'cover':
                        $data[$column['name']] = '<img class="cover" width="120" src="'.$data[$column['name']].'">';
                        break;
                    case 'picture':
                        $data[$column['name']] = '<img class="picture" width="120" src="'.get_image($data[$column['name']]).'">';
                        break;
                    case 'pictures':
                        $temp = explode(',', $data[$column['name']]);
                        $data[$column['name']] = '<img class="pictures" width="120" src="'.get_image($temp[0]).'">';
                        break;
                    case 'link':
                        $url_attribute='';
                        if (is_array($column['param'])) {
                            $url_attribute=$this->compileHtmlAttr($column['param']);
                            $column_link= str_replace('__data_id__',$data[$this->_table_data_list_key],$column['param']['link']);
                            $data[$column['name']] = '<a href="'.$column_link.'" '.$url_attribute.'>'.$data[$column['name']].'</a>';
                        }
                        break;
                    case 'url'://以URL形式添加
                        $url_attribute='';
                        if (is_array($column['param'])) {
                            $url_attribute=$this->compileHtmlAttr($column['param']);
                        }
                        $data[$column['name']] = '<a href="'.$data[$column['name']].'" '.$url_attribute.'>'.$data[$column['name']].'</a>';
                        break;
                    case 'type':
                        $form_item_type = config('form_item_type');
                        $data[$column['name']] = $form_item_type[$data[$column['name']]];
                        break;
                    case 'array'://新增
                        if (is_array($column['param'])) {
                            $column_array=$column['param'];
                            $data[$column['name']] = $column_array[$data[$column['name']]];
                        }
                        
                        break;
                    case 'switch'://开关
                        $data[$column['name']] = '<label class="css-input switch switch-sm switch-primary" title="开启/关闭"><input type="checkbox" data-table="admin_attachment" data-id="1" data-field="status" checked=""><span></span></label>';
                        break;
                    case 'callback': // 调用函数
                        if (is_array($column['param'])) {
                            if ($column['param']['callback_fun']) {//存在多个个参数，且为自定义函数
                                $callback_param=$column['param']['sub_param'];
                                array_unshift($callback_param,$data[$column['name']]);
                                $data[$column['name']] = call_user_func_array($column['param']['callback_fun'],$callback_param);
                            }else{//否则为回调函数模式
                                $data[$column['name']] = call_user_func_array($column['param'], array($data[$column['name']]));
                            }

                        } else {
                            $data[$column['name']] = call_user_func($column['param'], $data[$column['name']]);
                        }
                        break;
                }
            }

            /**
             * 修改列表数据
             * 有时候列表数据需要在最终输出前做一次小的修改
             * 比如管理员列表ID为1的超级管理员右侧编辑按钮不显示删除
             */
            if ($this->_alter_data_list) {
                foreach ($this->_alter_data_list as $alter) {
                    if ($data[$alter['condition']['key']] === $alter['condition']['value']) {
                        foreach ($alter['alter_data'] as &$val) {
                            $val = preg_replace(
                                '/__data_id__/i',
                                $data[$this->_table_data_list_key],
                                $val
                            );
                        }
                        $data = array_merge($data, $alter['alter_data']);
                    }
                }
            }
        }

        //编译head_button_list中的HTML属性
        if ($this->_head_button_list) {
            foreach ($this->_head_button_list as &$button) {
                $button['attribute'] = $this->compileHtmlAttr($button);
            }
        }
        //编译foot_button_list中的HTML属性
        if ($this->_foot_button_list) {
            foreach ($this->_foot_button_list as &$button) {
                $button['attribute'] = $this->compileHtmlAttr($button);
            }
        }

        $this->assign('meta_title',          $this->_meta_title);         // 页面标题
        $this->assign('sub_title',           $this->_sub_title);          // 页面子标题
        $this->assign('tip',                 $this->_tip);                // 页面提示说明
        $this->assign('head_button_list',    $this->_head_button_list);   // 顶部工具栏按钮
        $this->assign('foot_button_list',    $this->_foot_button_list);   // 底部工具栏按钮
        $this->assign('selects',             $this->_select);             //加入筛选select
        $this->assign('selectPostUrl',       url(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME));
        $this->assign('search',              $this->_search);              // 搜索配置
        $this->assign('tab_nav',             $this->_tab_nav);             // 页面Tab导航
        $this->assign('table_column_list',   $this->_table_column_list);   // 表格的列
        $this->assign('table_data_list',     $this->_table_data_list);     // 表格数据
        $this->assign('table_data_list_key', $this->_table_data_list_key); // 表格数据主键字段名称
        $this->assign('table_data_page',     $this->_table_data_page);     // 表示个数据分页
        $this->assign('right_button_list',   $this->_right_button_list);   // 表格右侧操作按钮
        $this->assign('alter_data_list',     $this->_alter_data_list);     // 表格数据列表重新修改的项目
        $this->assign('extra_html',          $this->_extra_html);          // 额外HTML代码
        parent::fetch($templateFile);
    }

}