<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.eacoo123.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\cms\controller;
use app\home\controller\Home;
use app\cms\model\Posts;

class Index extends Home {
    /**
     * 默认方法
     */
    public function index() {

        $this->assign('meta_title', '官网');
        $this->display();
    }

    /**
     * 文章列表方法
     */
    public function lists($cid) {
        // 获取分类信息
        $map['cid'] = $cid;
        $category_info = D('Category')->find($cid);
        switch ($category_info['doc_type']) {
            case 1:  // 链接
                if (stristr($category_info['url'], 'http://') || stristr($category_info['url'], 'https://')) {
                    redirect($category_info['url']);
                } else {
                    redirect(U($category_info['url'], null, false, true));
                }
                break;
            case 2:  // 单页
                redirect(U(D('Index')->moduleName.'/Category/detail', array("id" => $category_info["id"])));
                break;
            default :
                // 获取文档公共属性信息
                if ($category_info['index_template']) {
                    $template = $category_info['index_template'];
                }

                // 获取该分类绑定文档模型的主要字段
                $type_object      = D('Type');
                $attribute_object = D('Attribute');
                $doc_type_info    = $type_object->find($category_info['doc_type']);
                $type_main_field  = $attribute_object->getFieldById($doc_type_info['main_field'], 'name');

                // 获取筛选字段
                $con = array();
                $con['id'] = array('in', $doc_type_info['filter_field']);
                $filter_field_list = $attribute_object->where($con)->select();
                $new_filter_field_list = array();
                foreach ($filter_field_list as $key => $val) {
                    $val['options'] = parse_attr($val['options']);
                    $new_filter_field_list[$val['name']] = $val;
                }

                // 关键字搜索
                if (I('keyword')) {
                    $map[$type_main_field] = array('like', '%'.I('keyword').'%');
                }

                // 筛选条件
                if ($new_filter_field_list) {
                    foreach ($new_filter_field_list as &$value) {
                        // 构造搜索条件
                        if ($_GET[$value['name']] !== 'all' && $_GET[$value['name']]) {
                            switch ($value['type']) {
                                // 筛选价格类型
                                case 'price':
                                    $tmp = explode('-', $_GET[$value['name']]);
                                    if ($tmp[0] && $tmp[1] === '') {dump('1');
                                        $map['price'] = array('egt', $tmp[0]);
                                    } else if ($tmp[0] === '' && $tmp[1]) {
                                        $map['price'] = array('elt', $tmp[1]);
                                    } else {
                                        $map['price'] = array('between', $tmp);
                                    }
                                    break;
                                case 'radio':
                                    $tmp = $_GET[$value['name']];
                                    $map[$value['name']] = $tmp;
                                    break;
                                case 'select':
                                    $tmp = $_GET[$value['name']];
                                    $map[$value['name']] = $tmp;
                                    break;
                                case 'checkbox':
                                    $tmp = $_GET[$value['name']];
                                    $map[$value['name']] = array(
                                        'like',
                                        array(
                                            $tmp,
                                            $tmp.',%',
                                            '%,'.$tmp.',%',
                                            '%,'.$tmp
                                        ),
                                        'OR'
                                    );
                                    break;
                            }
                        }
                    }
                }

                // 获取文档列表
                $map['status'] = array('eq', 1);
                $base_table   = C('DB_PREFIX').D('Index')->tableName;
                $extend_table = strtolower(C('DB_PREFIX').D('Index')->moduleName.'_'.$doc_type_info['name']);
                $document_list = D('Index')
                               ->page(!empty($_GET["p"])?$_GET["p"]:1, C('ADMIN_PAGE_ROWS'))
                               ->order('sort desc,'.$base_table.'.id desc')
                               ->where($map)
                               ->join($extend_table.' ON '.$base_table.'.id = '.$extend_table.'.id')
                               ->select();
                $page = new Page(
                    D('Index')->where($map)->join($extend_table.' ON '.$base_table.'.id = '.$extend_table.'.id')->count(),
                    C('ADMIN_PAGE_ROWS')
                );

                // 如果当前分类下无文档则获取子分类文档
                if (!$document_list) {
                    // 获取当前分类的子分类ID列表
                    $child_cagegory_id_list = D('Category')->where(array('pid' => $cid))->getField('id',true);
                    if ($child_cagegory_id_list) {
                        $map['cid'] = array('in', $child_cagegory_id_list);
                        $document_list = D('Index')
                                       ->page(!empty($_GET["p"])?$_GET["p"]:1, C('ADMIN_PAGE_ROWS'))
                                       ->order('sort desc,'.$base_table.'.id desc')
                                       ->where($map)
                                       ->join($extend_table.' ON '.$base_table.'.id = '.$extend_table.'.id')
                                       ->select();
                        $page = new Page(
                            D('Index')->where($map)->join($extend_table.' ON '.$base_table.'.id = '.$extend_table.'.id')->count(),
                            C('ADMIN_PAGE_ROWS')
                        );
                    }
                }

                // 给文档主要字段赋值，如：文章标题、商品名称
                foreach ($document_list as &$doc) {
                    // 给文档主要字段赋值，如：文章标题、商品名称
                    $doc['main_field'] = $doc[$type_main_field];
                }

                // 模版赋值
                $this->assign('_current_category', $category_info);
                $this->assign('_filter_field_list', $new_filter_field_list);
                $this->assign('_category_info', $category_info);
                $this->assign('volist', $document_list);
                $this->assign('page', $page->show());
                $this->meta_title = $category_info['title'].'列表';
                Cookie('__forward__', $_SERVER['REQUEST_URI']);
                $this->display($template);
                break;
        }
    }

    /**
     * 我的文档列表
     */
    public function my() {
        $uid = $this->is_login();

        // 获取文档基础信息
        $map['uid'] = $uid;
        $map['status'] = array('egt', 0);
        $document_list = D('Index')->page(!empty($_GET["p"])?$_GET["p"]:1, C('ADMIN_PAGE_ROWS'))
                                   ->order('sort desc,id desc')
                                   ->where($map)
                                   ->select();
        $page = new Page(D('Index')->where($map)->count(), C('ADMIN_PAGE_ROWS'));

        // 获取扩展表的信息
        // 前台与后台查询文档列表不一样
        // 因为前台没有指定分类ID所以只能通过先找到文档的分类ID再根据分类绑定的模型获取主要字段
        foreach($document_list as &$document){
            // 合并基础信息与扩展信息
            $doc_type_info = D('Type')->find($document['doc_type']);
            $document = array_merge($document, D(D('Index')->moduleName.ucfirst($doc_type_info['name']))->find($document['id']));

            // 给主要字段赋值
            $main_field_name = D('Attribute')->getFieldById($doc_type_info['main_field'], 'name');
            $document['main_field'] = $document[$main_field_name];

            // 标题带链接
            $val['title_url'] = '<a target="_blank" href="'.U(D('Index')->moduleName.'/Index/detail', array('id' => $document['id'])).'">'.$document['main_field'].'</a>';
        }

        // 使用Builder快速建立列表页面。
        $builder = new \Common\Builder\ListBuilder();
        $builder->setMetaTitle('我的文档')  // 设置页面标题
                ->addTopButton('addnew')    // 添加新增按钮
                ->addTopButton('resume', array('model' => D('Index')->tableName))    // 添加启用按钮
                ->addTopButton('forbid', array('model' => D('Index')->tableName))    // 添加禁用按钮
                ->addTopButton('recycle', array('model' => D('Index')->tableName))   // 添加回收按钮
                ->addTableColumn('id', 'ID')
                ->addTableColumn('title_url', '标题')
                ->addTableColumn('create_time', '发布时间', 'time')
                ->addTableColumn('sort', '排序')
                ->addTableColumn('status', '状态', 'status')
                ->addTableColumn('right_button', '操作', 'btn')
                ->setTableDataList($document_list)  // 数据列表
                ->setTableDataPage($page->show())   // 数据列表分页
                ->addRightButton('edit')            // 添加编辑按钮
                ->addRightButton('forbid')          // 添加禁用/启用按钮
                ->addRightButton('recycle')         // 添加回收按钮
                ->setTemplate(C('USER_CENTER_LIST'))
                ->display();
    }

    /**
     * 新增文档
     */
    public function add() {
        $this->is_login();

        if(I('get.doc_type')){
            $map['doc_type'] = I('get.doc_type');
            $category_info = D('Category')->where($map)->order('id asc')->find();
        }elseif(I('get.cid')){
            $category_info = D('Category')->find(I('get.cid'));
        }
        // 获取当前分类
        if(!$category_info['post_auth']){
            $this->error('该分类禁止投稿');
        }
        $doc_type = D('Type')->find($category_info['doc_type']);
        $field_sort = json_decode($doc_type['field_sort'], true);
        $field_group = parse_attr($doc_type['field_group']);

        // 获取文档字段
        $map = array();
        $map['status'] = array('eq', '1');
        $map['show'] = array('eq', '1');
        $map['doc_type'] = array('in', '0,'.$category_info['doc_type']);
        $attribute_list = D('Attribute')->where($map)->select();

        // 解析字段options
        $new_attribute_list = array();
        foreach($attribute_list as $attr){
            if($attr['name'] == 'cid'){
                $con = array();
                $con['group'] = $category_info['group'];
                $con['doc_type'] = $category_info['doc_type'];
                $attr['value'] = $category_info['id'];
                $attr['options'] = select_list_as_tree('Category', $con);
            }else{
                $attr['options'] = parse_attr($attr['options']);
            }
            $new_attribute_list[$attr['id']] = $attr;
        }

        // 表单字段排序及分组
        if($field_sort){
            $new_attribute_list_sort = array();
            foreach($field_sort as $k1 => &$v1){
                $new_attribute_list_sort[0]['type'] = 'group';
                $new_attribute_list_sort[0]['options']['group'.$k1]['title'] = $field_group[$k1];
                foreach($v1 as $k2 => $v2){
                    $new_attribute_list_sort[0]['options']['group'.$k1]['options'][] = $new_attribute_list[$v2];
                }
            }
            $new_attribute_list = $new_attribute_list_sort[0]['options']['group1']['options'];
        }

        // 使用FormBuilder快速建立表单页面。
        $builder = new \Common\Builder\FormBuilder();
        $builder->setMetaTitle('新增文章')   // 设置页面标题
                ->setPostUrl(U('update'))    // 设置表单提交地址
                ->addFormItem('doc_type', 'hidden')
                ->setFormData(array('doc_type' => $category_info['doc_type']))
                ->setExtraItems($new_attribute_list)
                ->setTemplate(C('USER_CENTER_FORM'))
                ->display();
    }

    /**
     * 编辑文章
     */
    public function edit($id) {
        $this->is_login();
        // 获取文档信息
        $document_info = D('Index')->detail($id);

        // 获取当前分类
        $category_info = D('Category')->find($document_info['cid']);
        if(!$category_info['post_auth']){
            $this->error('该分类禁止投稿');
        }
        $doc_type = D('Type')->find($category_info['doc_type']);
        $field_sort = json_decode($doc_type['field_sort'], true);
        $field_group = parse_attr($doc_type['field_group']);

        // 获取文档字段
        $map = array();
        $map['status'] = array('eq', '1');
        $map['show'] = array('eq', '1');
        $map['doc_type'] = array('in', '0,'.$category_info['doc_type']);
        $attribute_list = D('Attribute')->where($map)->select();

        // 解析字段options
        $new_attribute_list = array();
        foreach($attribute_list as $attr){
            if ($attr['name'] == 'cid') {
                $con = array();
                $con['group'] = $category_info['group'];
                $con['doc_type'] = $category_info['doc_type'];
                $attr['options'] = select_list_as_tree('Category', $con);
            } else {
                $attr['options'] = parse_attr($attr['options']);
            }
            $new_attribute_list[$attr['id']] = $attr;
            $new_attribute_list[$attr['id']]['value'] = $document_info[$attr['name']];
        }

        // 表单字段排序及分组
        if ($field_sort) {
            $new_attribute_list_sort = array();
            foreach ($field_sort as $k1 => &$v1) {
                $new_attribute_list_sort[0]['type'] = 'group';
                $new_attribute_list_sort[0]['options']['group'.$k1]['title'] = $field_group[$k1];
                foreach ($v1 as $k2 => $v2) {
                    $new_attribute_list_sort[0]['options']['group'.$k1]['options'][] = $new_attribute_list[$v2];
                }
            }
            $new_attribute_list = $new_attribute_list_sort[0]['options']['group1']['options'];
        }

        // 使用FormBuilder快速建立表单页面。
        $builder = new \Common\Builder\FormBuilder();
        $builder->setMetaTitle('编辑文章')   // 设置页面标题
                ->setPostUrl(U('update'))    // 设置表单提交地址
                ->addFormItem('id', 'hidden', 'ID', 'ID')
                ->setExtraItems($new_attribute_list)
                ->setFormData($document_info)
                ->setTemplate(C('USER_CENTER_FORM'))
                ->display();
    }

    /**
     * 新增或更新一个文档
     */
    public function update() {
        $this->is_login();

        // 新增或更新文档
        $article_object = D('Index');
        $result = $article_object->update();
        if (!$result) {
            $this->error($article_object->getError());
        } else {
            if(is_array($result)){
                $message = '更新成功';
            } else {
                $message = '新增成功';
            }
            $this->success($message, Cookie('__forward__') ? : C('HOME_PAGE'));
        }
    }

    /**
     * 文章信息
     */
    public function detail($id) {
        $article_object = D('Index');
        $info = $article_object->where('status=1')->detail($id);
        if (!$info) {
            $this->error('错误：'. $article_object->getError());
        }

        // 设置文档显示模版
        if ($info['category_info']['detail_template']) {
            $template = $info['category_info']['detail_template'];
        }

        $this->assign('info', $info);
        $this->assign('_current_category', $info['category']);
        $this->assign('_filter_field_list', $info['filter_field_list']);
        $this->assign('meta_title', $info['main_field']);
        $this->assign('meta_keywords', $info['tags'] ? : C('WEB_SITE_KEYWORD'));
        $this->assign('meta_description', $info['abstract'] ? : C('WEB_SITE_DESCRIPTION'));
        $this->assign('meta_cover', $info['cover']);
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display($template);
    }

    /**
     * 设置一条或者多条数据的状态
     */
    public function setStatus($model = CONTROLLER_NAME) {
        $ids    = I('request.ids');
        $status = I('request.status');
        if (empty($ids)) {
            $this->error('请选择要操作的数据');
        }
        $map['id'] = array('in',$ids);
        switch ($status) {
            case 'delete' :  // 删除条目
                $map['status'] = -1;
                $info = D('Index')->detail($ids, $map);
                $extend_table_object = D(strtolower(D('Index')->moduleName.'_'.$info['doc_type_info']['name']));
                $exist = $extend_table_object->find($ids);
                if ($exist) {
                    $result = $extend_table_object->delete($ids);
                } else {
                    $result = true;
                }
                if ($result) {
                    $result2 = D('Index')->delete($ids);
                    if ($result2) {
                        $this->success('彻底删除成功');
                    } else {
                        $this->error('删除失败');
                    }
                } else {
                    $this->error('删除失败');
                }
                break;
            default :
                parent::setStatus($model);
                break;
        }
    }
}