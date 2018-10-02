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
 * 列表构建器
 * @package app\common\builder
 * @author 心云间、凝听 <981248356@qq.com>
 */
class BuilderList extends Builder {
    
    private $topButtonList   = [];   // 顶部工具栏按钮组
    //private $_select         = [];             //添加下拉框
    private $search          = ['type'=>'basic']; // 搜索参数配置
    private $action_url = '';
    private $tabNav          = [];           // 页面Tab导航
    private $tableColumns    = [];    //表格数据标题
    private $tableDataList   = []; // 表格数据列表
    private $tablePrimaryKey = 'id';  // 表格数据列表主键字段名
    private $tableDataPage = ['total'=>0];        // 表格数据分页
    private $staticFiles;     // 静态资源文件
    private $rightButtonList = []; // 表格右侧操作按钮组
    private $alterDataList   = [];   // 表格数据列表重新修改的项目
    private $extraHtml;                  // 额外功能代码
    private $rightButtonType = 1;  //右边按钮类型
    //private $_template;                    // 模版

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
    // public function addSelect($title = '筛选', $name = 'key', $arrvalue = null)
    // {
    //     $this->_select[] = array('title' => $title, 'name' => $name,'arrvalue' => $arrvalue);
    //     return $this;
    // }

    /**
     * 设置搜索参数
     * @param  string $type 显示类型(base|custom)
     * @param  [type] $title [description]
     * @param  string $url [description]
     * @date   2018-02-06
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function setSearch($type='basic',$title='请输入关键字', $url='') {
        if(!$url){
            $url = url(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
        }
        $this->search = ['type'=>$type,'title' => $title, 'url' => $url];
        return $this;
    }

    /**
     * 设置Tab按钮列表
     * @param $tab_list Tab列表  array(
     *                               'title' => '标题',
     *                               'href' => 'http://www.xxx.cn'
     *                           )
     * @param $current 当前tab
     * @return $this
     */
    public function setTabNav($tab_list, $current) {
        $this->tabNav = [
            'tab_list' => $tab_list,
            'current' => $current
        ];
        return $this;
    }

    /**
     * 加一个表格字段
     */
    public function keyListItem($name, $title, $type = null, $param = null,$extra_attr=null) {

        $column = [
            'name'  => $name,
            'title' => $title,
            'type'  => $type,
            'param' => $param,
            'extra_attr'=>$extra_attr
        ];
        $this->tableColumns[] = $column;
        return $this;
    }

    /**
     * 表格数据列表
     */
    public function setListData($table_data_list) {
        //如果请求方式不是ajax，则直接返回对象
        if (!IS_AJAX) return $this;
        $this->tableDataList = $table_data_list;
        return $this;
    }

    /**
     * 表格数据列表的主键名称
     */
    public function setListPrimaryKey($table_primary_key = 'id') {
        $this->tablePrimaryKey = $table_primary_key;
        return $this;
    }

    /**
     * 设置提交请求地址
     * @param  string $url [description]
     * @date   2018-09-08
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function setActionUrl($url='')
    {
        $this->action_url = !empty($url) ? $url : $this->request->url();
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
        return $this->addBtn('top',$type, $attribute);
    }

    //添加顶部按钮，用法同上（兼容性）
    public function addTopButton($type, $attribute = null) {
        return $this->addBtn('top',$type, $attribute);
    }
    
    /**
     * 添加按钮
     * @param  string $position 位置
     * @param  [type] $type 类型
     * @param  [type] $attribute 属性
     * @date   2018-02-06
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function addBtn($position='top',$type, $attribute = null){
        //如果请求方式是ajax，则直接返回对象
        if (IS_AJAX) return $this;

        $model_name = !empty($attribute['model']) ? $attribute['model'] : ($this->pluginName ? input('param._controller') : CONTROLLER_NAME);
        $query_model_params = $this->preQueryConnector.'model='.$model_name;
        switch ($type) {
            case 'addnew':  // 添加新增按钮
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '添加';
                $my_attribute['icon'] = 'fa fa-plus';
                $my_attribute['class'] = 'btn btn-primary btn-sm';
                $my_attribute['href']  = $this->pluginName ? plugin_url('edit') :  url(MODULE_NAME.'/'.CONTROLLER_NAME.'/edit');
                
                break;
            case 'resume':  // 添加启用按钮(禁用的反操作)
                //预定义按钮属性以简化使用
                $my_attribute['title'] = '启用';
                $my_attribute['target-form'] = 'ids';
                $my_attribute['icon'] = 'fa fa-play';
                $my_attribute['class'] = 'btn btn-success ajax-table-btn confirm btn-sm';
                $my_attribute['href']  = $this->pluginName ? plugin_url('setStatus',['status' => 'resume']) : url(MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',['status'=>'resume']);
                $my_attribute['href'] .= $query_model_params;
                break;
            case 'forbid':  // 添加禁用按钮(启用的反操作)
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '禁用';
                $my_attribute['target-form'] = 'ids';
                $my_attribute['icon'] = 'fa fa-pause';
                $my_attribute['class'] = 'btn btn-warning ajax-table-btn confirm btn-sm';
                $my_attribute['confirm-info'] = '您确定要执行禁用操作吗？';
                $my_attribute['href']  = $this->pluginName ? plugin_url('setStatus',['status' => 'forbid']) : url(MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',['status' => 'forbid']);
                $my_attribute['href'] .=  $query_model_params;
                break;
            case 'recycle':  // 添加回收按钮(还原的反操作)
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '回收';
                $my_attribute['target-form'] = 'ids';
                $my_attribute['icon'] = 'fa fa-recycle';
                $my_attribute['class'] = 'btn btn-danger ajax-table-btn confirm btn-sm';
                $my_attribute['confirm-info'] = '您确定要执行回收操作吗？';
                $my_attribute['href']  = $this->pluginName ? plugin_url('setStatus',['status' => 'recycle']) : url(MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',['status' => 'recycle']);
                $my_attribute['href'] .=  $query_model_params;
                break;
            case 'restore':  // 添加还原按钮(回收的反操作)
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '还原';
                $my_attribute['target-form'] = 'ids';
                $my_attribute['icon'] = 'fa fa-window-restore';
                $my_attribute['class'] = 'btn btn-success ajax-table-btn confirm btn-sm';
                $my_attribute['href']  = $this->pluginName ? plugin_url('setStatus',['status'=>'restore']) :  url(MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',['status' => 'restore']);
                $my_attribute['href'] .=  $query_model_params;
                break;
            case 'delete': // 添加删除按钮(我没有反操作，删除了就没有了，就真的找不回来了)
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '删除';
                $my_attribute['target-form'] = 'ids';
                $my_attribute['icon'] = 'fa fa-remove';
                $my_attribute['class'] = 'btn btn-danger ajax-table-btn confirm btn-sm';
                $my_attribute['confirm-info'] = '您确定要执行删除操作吗？';
                $my_attribute['href']  = $this->pluginName ? plugin_url('setStatus',['status'=>'delete']) :  url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'delete',
                    )
                );
                $my_attribute['href'] .=  $query_model_params;
                break;
            case 'sort':  // 添加排序按钮
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '排序';
                $my_attribute['icon'] = 'fa fa-sort';
                $my_attribute['name'] = '排序';
                $my_attribute['class'] = 'btn btn-info btn-sm';
                $my_attribute['href']  = $this->pluginName ? plugin_url('sort') :  url(MODULE_NAME.'/'.CONTROLLER_NAME.'/sort');

                break;
            case 'self': //添加自定义按钮(第一原则使用上面预设的按钮，如果有特殊需求不能满足则使用此自定义按钮方法)
                // 预定义按钮属性以简化使用
                $my_attribute['target-form'] = 'ids';
                $my_attribute['class'] = 'btn btn-danger btn-sm';
                if (empty($my_attribute['title'])) {
                    $my_attribute['title'] = '该自定义按钮未配置属性';
                }
                break;
        }
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
        // 这个按钮定义好了把它丢进按钮池里
        if ($position=='top') {
            $this->topButtonList[] = $my_attribute;
        } 
        
        return $this;
    }

    /**
     * 加入一个数据列表右侧按钮
     * 在使用预置的几种按钮时，比如我想改变编辑按钮的名称
     * 那么只需要$builder->addRightpButton('edit', array('title' => '换个马甲'))
     * 如果想改变地址甚至新增一个属性用上面类似的定义方法
     * 因为添加右侧按钮的时候你并没有办法知道数据ID，于是我们采用__data_id__作为约定的标记
     * __data_id__会在fetch方法里自动替换成数据的真实ID
     * @param string $type 按钮类型，edit/forbid/recycle/restore/delete/self六种取值
     * @param array  $attribute 按钮属性，一个定了标题/链接/CSS类名等的属性描述数组
     * @param array  $condition 条件表达式
     * @return $this
     */
    public function addRightButton($type, $attribute = null,$condition=[]) {
        //如果请求方式不是ajax，则直接返回对象
        if (!IS_AJAX) return $this;

        $model_name = !empty($attribute['model']) ? $attribute['model'] : ($this->pluginName ? input('param._controller') : CONTROLLER_NAME);
        $query_model_params = $this->preQueryConnector.'model='.$model_name;
        switch ($type) {
            case 'edit':  // 编辑按钮
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '编辑';
                $my_attribute['icon'] = 'fa fa-edit';
                $my_attribute['class'] = $this->rightButtonType==1 ? 'btn btn-primary btn-xs':'';
                $my_attribute['href']  =  $this->pluginName ? plugin_url('edit',[$this->tablePrimaryKey => '__data_id__']) : url(MODULE_NAME.'/'.CONTROLLER_NAME.'/edit',[$this->tablePrimaryKey => '__data_id__']);

                break;
            case 'forbid':  // 改变记录状态按钮，会更具数据当前的状态自动选择应该显示启用/禁用
                //预定义按钮属
                $my_attribute['type'] = 'forbid';
                $my_attribute['0']['title'] = !empty($attribute['0']['title']) ? $attribute['0']['title'] : '启用';
                $my_attribute['0']['class'] = $this->rightButtonType==1 ? 'btn btn-success btn-xs ajax-get confirm':'ajax-get confirm';
                $my_attribute['0']['href']  = $this->pluginName ? plugin_url('setStatus',['status' => 'resume',$this->tablePrimaryKey => '__data_id__']) : url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'resume',
                        'ids' => '__data_id__',
                    )
                ).$query_model_params;
                $my_attribute['1']['title'] = !empty($attribute['1']['title']) ? $attribute['1']['title'] : '禁用';
                $my_attribute['1']['class'] = $this->rightButtonType==1 ? 'btn btn-warning btn-xs ajax-get confirm':'ajax-get confirm';
                $my_attribute['1']['href']  = $this->pluginName ? plugin_url('setStatus',['status' => 'forbid',$this->tablePrimaryKey => '__data_id__']) : url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'forbid',
                        'ids' => '__data_id__',
                    )
                ).$query_model_params;

                break;
            case 'hide':  // 改变记录状态按钮，会更具数据当前的状态自动选择应该显示隐藏/显示
                // 预定义按钮属
                $my_attribute['type'] = 'hide';
                $my_attribute['2']['title'] = '显示';
                $my_attribute['2']['class'] = $this->rightButtonType==1 ? 'btn btn-success btn-xs ajax-get confirm':'ajax-get confirm';
                $my_attribute['2']['href']  = $this->pluginName ? plugin_url('setStatus',['status' => 'show',$this->tablePrimaryKey => '__data_id__']) : url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'show',
                        'ids' => '__data_id__',
                    )
                ).$query_model_params;
                $my_attribute['1']['title'] = '隐藏';
                $my_attribute['1']['class'] = $this->rightButtonType==1 ? 'btn btn-info btn-xs ajax-get confirm':'ajax-get confirm';
                $my_attribute['1']['href']  = url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'hide',
                        'ids' => '__data_id__',
                    )
                ).$query_model_params;

                break;
            case 'recycle':
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '回收';
                $my_attribute['icon'] = 'fa fa-recycle';
                $my_attribute['class'] = $this->rightButtonType==1 ? 'btn btn-danger btn-xs ajax-get confirm':'ajax-get confirm';
                $my_attribute['confirm-info'] = '您确定要执行回收操作吗？';
                $my_attribute['href'] = $this->pluginName ? plugin_url('setStatus',['status'=>'recycle','ids' => '__data_id__']) :  url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'recycle',
                        'ids' => '__data_id__',
                    )
                );
                $my_attribute['href'] .= $query_model_params;

                break;
            case 'restore':
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '还原';
                $my_attribute['class'] = $this->rightButtonType==1 ? 'btn btn-success btn-xs ajax-get confirm':'ajax-get confirm';
                $my_attribute['href'] = $this->pluginName ? plugin_url('setStatus',['status'=>'restore','ids' => '__data_id__']) :  url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'restore',
                        'ids' => '__data_id__',
                    )
                );
                $my_attribute['href'] .= $query_model_params;

                break;
            case 'delete':
                // 预定义按钮属性以简化使用
                $my_attribute['title'] = '删除';
                $my_attribute['icon'] = 'fa fa-remove';
                $my_attribute['class'] = $this->rightButtonType==1 ? 'btn btn-danger btn-xs ajax-get confirm':'ajax-get confirm';
                $my_attribute['confirm-info'] = '您确定要执行删除操作吗？';
                $my_attribute['href'] = $this->pluginName ? plugin_url('setStatus',['status'=>'delete','ids' => '__data_id__']) :  url(
                    MODULE_NAME.'/'.CONTROLLER_NAME.'/setStatus',
                    array(
                        'status' => 'delete',
                        'ids' => '__data_id__',
                    )
                );
                $my_attribute['href'] .= $query_model_params;

                break;
            case 'self':
                // 预定义按钮属性以简化使用
                $my_attribute['class'] = '';
                if (empty($my_attribute['title'])) {
                    $my_attribute['title'] = '该自定义按钮未配置属性';
                }
                
                break;
        }

        // 如果定义了属性数组则与默认的进行合并，详细使用方法参考上面的顶部按钮
        /**
        * 如果定义了属性数组则与默认的进行合并
        * 用户定义的同名数组元素会覆盖默认的值
        * 比如$builder->addColumnButton('edit', array('title' => '换个马甲'))
        * '换个马甲'这个碧池就会使用山东龙潭寺的十二路谭腿第十一式“风摆荷叶腿”
        * 把'新增'踢走自己霸占title这个位置，其它的属性同样道理
        */
        if ($attribute && is_array($attribute)) {
            $my_attribute = array_merge($my_attribute, $attribute);
        }

        //支持layer
        if (isset($attribute['layer'])) {
            if (is_array($attribute['layer'])) {
                $layer_width = !empty($attribute['layer']['width']) ? $attribute['layer']['width']:'60%';
                $layer_height = !empty($attribute['layer']['height']) ? $attribute['layer']['height']:'60%';
            }
            $my_attribute['href'] = 'javascript:layer.open({type: 2,title: \''.$my_attribute['title'].'\',shadeClose: true,shade: 0.8,area: [\''.$layer_width.'\',\''.$layer_height.'\'],content:\''.$my_attribute['href'].'?page_type=iframe\'});';
            unset($my_attribute['layer']);
        }

        if (!empty($condition)) {
            $my_attribute['condition'] = $condition;
        }

        // 这个按钮定义好了把它丢进按钮池里
        $this->rightButtonList[] = $my_attribute;
        return $this;
    }

    /**
     * 设置分页
     * @param  [type] $total 总数量
     * @param  integer $page_size 每页数量
     * @date   2018-02-06
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function setListPage($total = 0, $page_size = null) {
        if (is_null($page_size)) {
            $page_size = config('admin_page_size');
        }
        if ($page_size === false) {
            $page_size = $total;
        }
        $this->tableDataPage = ['total' => $total, 'page_size' => $page_size];
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
        $this->alterDataList[] = [
            'condition' => $condition,
            'alter_data' => $alter_data
        ];
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
     * 设置列表按钮类型
     * @param $type 类型(1 label,2 下拉,3,链接)
     * @return $this
     */
    public function setRightButton($type) {
        $this->rightButtonType = $type;
        return $this;
    }

    /**
     * 显示页面
     */
    public function fetch($template_name='listbuilder',$vars ='', $replace ='', $config = '') {

        if (IS_AJAX) {
            // 编译data_list中的值
            foreach ($this->tableDataList as &$data) {
                //编译表格列按钮
                $data = $this->compileRightButtons($data);
                //编译列表列值
                $data = $this->compileTableColumns($data);

                /**
                 * 修改列表数据
                 * 有时候列表数据需要在最终输出前做一次小的修改
                 * 比如管理员列表ID为1的超级管理员右侧编辑按钮不显示删除
                 */
                if ($this->alterDataList) {
                    foreach ($this->alterDataList as $alter) {
                        if ($data[$alter['condition']['key']] === $alter['condition']['value']) {
                            foreach ($alter['alter_data'] as &$val) {
                                $val = preg_replace(
                                    '/__data_id__/i',
                                    $data[$this->tablePrimaryKey],
                                    $val
                                );
                            }
                            $data = array_merge($data, $alter['alter_data']);
                        }
                    }
                }

            }

            //setAppLog('$params=>'.var_export(input('param.'),true).';','debug');
            $total  = $this->tableDataPage['total'];
            $list   = $this->tableDataList;
            $result = ["total" => $total, "rows" => $list];
            return json($result);
        } else{
            //编译top_button_list中的HTML属性
            if ($this->topButtonList) {
                foreach ($this->topButtonList as &$button) {
                    $button['primary-key'] = $this->tablePrimaryKey;
                    $button['attribute'] = $this->compileHtmlAttr($button);
                }
            }

            //表格列
            $table_column_fields = [];
            foreach ($this->tableColumns as $key => $val) {
                $table_column_fields[$key]['field']=$val['name'];
                $table_column_fields[$key]['title']=$val['title'];
            }

            $template_val = [
                'top_button_list'     => $this->topButtonList,// 顶部工具栏按钮
                'action_url'          => !empty($this->action_url) ? $this->action_url:$this->request->url(),
                'search'              => $this->search,// 搜索配置
                'tab_nav'             => $this->tabNav,// 页面Tab导航
                'table_columns'       => $this->tableColumns,// 表格的列
                'table_column_fields' => json_encode($table_column_fields),//表格列bootstrap-table
                'table_primary_key'   => $this->tablePrimaryKey,// 表格数据主键字段名称
                //'alter_data_list'   => $this->alterDataList,// 表格数据列表重新修改的项目
                'table_data_page'     => $this->tableDataPage, //数据分页
                'extra_html'          => $this->extraHtml, // 额外HTML代码
                'staticFiles'         => $this->staticFiles,// 加载静态资源文件
            ];
            $this->assign($template_val);
            
            $templateFile = APP_PATH.'/common/view/builder/'.$template_name.'.html';
            return parent::fetch($templateFile);
        }
        
    }

    /**
     * 编译右侧按钮
     * @param  array $data [description]
     * @return [type] [description]
     * @date   2018-02-06
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function compileRightButtons($data=[])
    {
        if(!isset($data['right_button'])) $data['right_button']='';
        // 编译表格右侧按钮
        if ($this->rightButtonList) {
            if ($this->rightButtonType==2) {
                $data['right_button'] .='<div class="btn-group">
                  <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                    操作 <span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu" role="menu">';
            }
            
            foreach ($this->rightButtonList as $right_button) {
                //如果条件存在
                if (isset($right_button['condition']) && !empty($right_button['condition'])) {
                    $condition_res = $this->resolveConditionRules($data,$right_button['condition']);
                    if (!$condition_res) {
                        continue;
                    }
                }
                // 禁用按钮与隐藏比较特殊，它需要根据数据当前状态判断是显示禁用还是启用
                if (isset($right_button['type'])) {
                    if ($right_button['type'] === 'forbid' || $right_button['type'] === 'hide'){
                        $right_button = $right_button[$data['status']];
                    }
                }

                // 将约定的标记__data_id__替换成真实的数据ID
                $right_button['href'] = preg_replace(
                    '/__data_id__/i',
                    $data[$this->tablePrimaryKey],
                    $right_button['href']
                );

                $right_button_show_title = $right_button['title'];
                if(isset($right_button['icon'])) $right_button_show_title = '<i class="'.$right_button['icon'].'"></i> ';
                // 编译按钮属性
                $right_button['attribute'] = $this->compileHtmlAttr($right_button);
                switch ($this->rightButtonType) {
                    case 2:
                        $data['right_button'] .= '<li><a '.$right_button['attribute'].'>'.$right_button_show_title.'</a></li>';
                        break;
                    
                    default:
                        $data['right_button'] .= '<a '.$right_button['attribute'].' style="margin-right:6px;">'.$right_button_show_title.'</a>';
                        break;
                }
                
            }
            if ($this->rightButtonType==2) {
                $data['right_button'] .='</ul></div>';
            }
        }
        return $data;
    }

    /**
     * 编译表格列
     * @param  array $data [description]
     * @return [type] [description]
     * @date   2018-02-06
     * @author 心云间、凝听 <981248356@qq.com>
     */
    private function compileTableColumns($data = [])
    {
        $result = [];
        // 根据表格标题字段指定类型编译列表数据
        foreach ($this->tableColumns as &$column) {
            //重新赋值一遍解决拿不到获取器的问题
            //dump($column['name']);
            if (isset($data['sex_text'])) {
                //dump('debug');
            }
            $result[$column['name']] = $data[$column['name']];

            $column_type_str = explode('_', $column['type']);
            $column_type = $column_type_str[0];
            switch ($column_type) {
                case 'status':
                    switch($data[$column['name']]){
                        case -1:
                            $data[$column['name']] = '<span class="fa fa-trash text-danger"></span>';
                            break;
                        case 0:
                            $data[$column['name']] = '<span class="fa fa-ban text-danger"></span>';
                            break;
                        case 1:
                            $data[$column['name']] = '<span class="fa fa-check text-success"></span>';
                            break;
                        case 2:
                            $data[$column['name']] = '<span class="fa fa-eye-slash text-warning"></span>';
                            break;
                    }
                    break;
                case 'bool':
                    switch($data[$column['name']]){
                        case 0:
                            $data[$column['name']] = '<span class="fa fa-ban text-danger"></span>';
                            break;
                        case 1:
                            $data[$column['name']] = '<span class="fa fa-check text-success"></span>';
                            break;
                    }
                    break;
                case 'label':
                    if (isset($column_type_str[1])) {
                        switch($column_type_str[1]){
                            case 'bool':
                                if ($data[$column['name']]=='否') {
                                    $data[$column['name']] = '<label class="label label-default">'.$data[$column['name']].'</label>';
                                } elseif ($data[$column['name']]=='是') {
                                    $data[$column['name']] = '<label class="label label-success">'.$data[$column['name']].'</label>';
                                }
                                break;
                            default:
                                $data[$column['name']] = '<label class="label label-'.$column_type_str[1].'">'.$data[$column['name']].'</label>';
                        }
                    }
                    
                    break;
                case 'byte':
                    $data[$column['name']] = format_bytes($data[$column['name']]);
                    break;
                case 'icon':
                    $data[$column['name']] = '<i class="'.$data[$column['name']].'"></i>';
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
                        $data[$column['name']] = config('view_replace_str.__PUBLIC__').'/img/default-avatar.png';
                    }
                    $data[$column['name']] = '<img style="width:40px;height:40px;" src="'.cdn_img_url($data[$column['name']]).'">';
                    break;
                case 'image':
                    $pic_w = '120';
                    if (!empty($column['param']['width'])) {
                        $pic_w = $column['param']['width'];
                    }
                    $data[$column['name']] = '<img class="cover" width="'.$pic_w.'" src="'.$data[$column['name']].'">';
                    break;
                case 'picture':
                    $pic_w = '120';
                    if (!empty($column['param']['width'])) {
                        $pic_w = $column['param']['width'];
                    }
                    $data[$column['name']] = '<img class="picture" width="'.$pic_w.'" src="'.get_image($data[$column['name']]).'">';
                    break;
                case 'pictures':
                    $pic_w = '120';
                    if (!empty($column['param']['width'])) {
                        $pic_w = $column['param']['width'];
                    }
                    $temp = explode(',', $data[$column['name']]);
                    $data[$column['name']] = '<img class="pictures" width="'.$pic_w.'" src="'.get_image($temp[0]).'">';
                    break;
                case 'url'://以URL形式添加
                    $column_extra_attr = '';
                    $column_url = $data[$column['name']];
                    if (is_array($column['param'])) {
                        if (isset($column['param']['extra_attr'])) {
                            $column_extra_attr = $this->compileHtmlAttr($column['param']);
                        }
                        if (isset($column['param']['url'])) {
                            $column_url = str_replace('__data_id__',$data[$this->tablePrimaryKey],$column['param']['url']);
                        }
                        if (isset($column['param']['url_callback'])) {
                            $column_url = call_user_func($column['param']['url_callback'], $data[$column['name']]);
                        }
                        
                    }
                    $data[$column['name']] = '<a href="'.$column_url.'" '.$column_extra_attr.'>'.$data[$column['name']].'</a>';
                    break;
                // case 'type':
                //     $form_item_type = config('form_item_type');
                //     $data[$column['name']] = $form_item_type[$data[$column['name']]];
                //     break;
                case 'array'://新增
                    if (is_array($column['param'])) {
                        $column_array = $column['param'];
                        $data[$column['name']] = isset($column_array[$data[$column['name']]]) ? $column_array[$data[$column['name']]]:$data[$column['name']];
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
                        } else{//否则为回调函数模式
                            $data[$column['name']] = call_user_func_array($column['param'], array($data[$column['name']]));
                        }

                    } else {
                        $data[$column['name']] = call_user_func($column['param'], $data[$column['name']]);
                    }
                    break;
            }
        }

        if (!empty($data)) {
            $data = !is_array($data) ? $data->toArray() : $data;
            $result = array_merge($result,$data);
            unset($data);
        }
        
        return $result;
    }

    /**
     * 编译引入静态资源文件css|js，支持跨模块
     * @param string $type 类型：css/js
     * @param string $files_name 文件名，多个用逗号隔开,以public/static为根路径索引
     */
    private function compileStaticFiles($type = '', $files_name = '')
    {
        if ($files_name != '') {
            if (!is_array($files_name)) {
                $files_name = explode(',', $files_name);
            }
            foreach ($files_name as $item) {
                $this->staticFiles[$type.'_files'][] = $item;
            }
        }
    }

    /**
     * 解析条件规则
     * @param  [type] $rules [description]
     * @return [type] [description]
     * @date   2018-05-16
     * @author 心云间、凝听 <981248356@qq.com>
     */
    private function resolveConditionRules($data=[], $rules=null)
    {
        if (empty($rules) || empty($data)) {
            return false;
        }
        $res = false;
        if (is_array($rules)) {
            foreach ($rules as $split => $rule) {
                foreach ($rule as $field => $condition_c) {
                    $operator = $condition_c[0];//运算符
                    $condition_val = $condition_c[1];//比较值
                    switch ($operator) {
                        case '=':
                            $res = $data[$field] == $condition_val ? true : false;
                            break;
                        case '>':
                            $res = $data[$field] > $condition_val ? true : false;
                            break;
                        case '<':
                            $res = $data[$field] < $condition_val ? true : false;
                            break;
                        case '>=':
                            $res = $data[$field] >= $condition_val ? true : false;
                            break;
                        case '=<':
                            $res = $data[$field] <= $condition_val ? true : false;
                            break;
                        default:
                            # code...
                            break;
                    }
                }
            }
        }

        return $res;
    }

}