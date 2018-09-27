<?php
//配置控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

/**
 * 系统配置控制器
 */
class Config extends Admin {

    protected $configModel;

    function _initialize()
    {
        parent::_initialize();
        $this->configModel = model('common/config');
    }

    /**
     * 配置列表
     * @param $tab 配置分组ID
     */
    public function index($group = 1) {

        $map = [
            'group'=>$group
        ];
        list($data_list,$total) = $this->configModel->search('id|name|title')->getListByPage($map,true,'sort asc,id asc');
        // 设置Tab导航数据列表
        $config_group_list = config('config_group_list');  // 获取配置分组

        foreach ($config_group_list as $key => $val) {
            $tab_list[$key]['title'] = $val;
            $tab_list[$key]['href']  = url('index', ['group' => $key]);
        }
        //移动按钮属性
        $move_attr = [
            'title'   =>'移动分组',
            'icon'    =>'fa fa-exchange',
            'class'   =>'btn btn-info btn-sm',
            'onclick' =>'move()'
        ];
        $extra_html = $this->moveGroupHtml($config_group_list,$group);//添加移动按钮html
        // 使用Builder快速建立列表页面。

        $return = builder('list')
                ->setPageTips('调用方式，如：<code>config("web_site_statistics")</code>，即可调用站点统计的配置信息')
                ->addTopButton('addnew',['href'=>url('edit',['group_id'=>$group])])   // 添加新增按钮
                //->addTopButton('resume',array('title'=>'显示'))   // 添加启用按钮
                //->addTopButton('forbid',array('title'=>'隐藏'))   // 添加禁用按钮
                ->addTopButton('delete')   // 添加删除按钮
                ->addTopButton('self', $move_attr) //添加移动按钮
                ->setSearch('请输入ID/配置名称/配置标题',url('index', array('group' => $group)))
                ->setTabNav($tab_list, $group)  // 设置页面Tab导航
                ->keyListItem('id', 'ID')
                ->keyListItem('name', '名称')
                ->keyListItem('title', '标题')
                ->keyListItem('type', '类型','type')
                //->keyListItem('remark', '说明')
                ->keyListItem('sub_group', '子分组')
                ->keyListItem('sort', '排序')
                ->keyListItem('status', '状态', 'status')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListData($data_list)     // 数据列表
                ->setListPage($total)  // 数据列表分页
                ->setExtraHtml($extra_html)
                ->addRightButton('edit')           // 添加编辑按钮
                ->addRightButton('delete')         // 添加删除按钮
                ->fetch();

        return Iframe()
                ->setMetaTitle('配置列表') // 设置页面标题
                ->content($return);
    }

    /**
     * 编辑配置
     * @param  integer $id [description]
     * @return [type] [description]
     * @date   2018-02-26
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function edit($id=0){
        $title = $id>0 ? "编辑" : "新增";
        $group_id = input('param.group_id');
        if (IS_POST) {
            $data = $this->request->param();
            $result = $this->validateData($data,
                                [
                                    ['group','require|number|>=:0','请选择配置分组|分组必须为数字|分组格式不正确'],
                                    ['sub_group','number|>=:0','子分组必须为数字|子分组格式不正确'],
                                    ['name','require|alphaDash','配置名称不能为空|配置名称只限字母、数字、下划线'],
                                    ['title','require|chsDash','标题不能为空|配置标题只限汉字、字母、数字和下划线_及破折号-'],
                                ]);
            if ($this->configModel->editData($data)) {
                cache('DB_CONFIG_DATA',null);
                $this->success($title.'成功',url('index',['group'=>$data['group']]));
            } else {
                $this->error($this->configModel->getError());
            }

        } else {
            $info = [
                'sort'   => 99,
                'group'  => $group_id,
                'status' => 1
            ];
            if ($id>0) {
                $info = $this->configModel->where('id',$id)->field(true)->find();
            }
            // 获取Builder表单类型转换成一维数组
            $sub_group = logic('Config')->getSubGroup($group_id);

            $builder = builder('form')
                    ->addFormItem('id', 'hidden', 'ID', 'ID')
                    ->addFormItem('group', 'select', '配置分组', '配置所属的分组', config('config_group_list'));
            if (!empty($sub_group['sub_group'])) {
                $builder->addFormItem('sub_group','select','配置子分组','先对大分组创建一个子分组，一般不填写',$sub_group['sub_group']);
            }
            $content = $builder->addFormItem('type', 'select', '配置类型', '配置类型的分组',config('form_item_type'))
                    ->addFormItem('name', 'text', '配置名称', '配置名称')
                    ->addFormItem('title', 'text', '配置标题', '配置标题')
                    ->addFormItem('value', 'textarea', '配置值', '配置值')
                    ->addFormItem('options', 'textarea', '配置项', '如果是单选、多选、下拉等类型 需要配置该项')
                    //->addFormItem('function', 'text', '关联函数', '确保函数已创建，并且函数具有返回值')
                    ->addFormItem('remark', 'textarea', '配置说明', '配置说明')
                    ->addFormItem('sort', 'number', '排序', '按照数值大小的倒叙进行排序，数值越小越靠前')
                    ->addFormItem('status', 'radio', '状态', '状态，开启或关闭',[1=>'是',0=>'否'])
                    ->setExtraHtml($sub_group['html'])
                    ->setFormData($info)
                    //->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();
            return Iframe()
                ->setMetaTitle($title.'配置')  // 设置页面标题
                ->content($content);
        }
    }

    /**
     * 获取某个分组的配置参数
     */
    public function group($group = 1){
        //根据分组获取配置
        $map=[
            'status'=>['egt', 1],
            'group' =>['eq', $group]
        ];
        $data_list =$this->configModel->getList($map,true,'sort asc,id asc');

        $tab_list = logic('admin/Config')->getTabList();
        // 构造表单名、解析options
        foreach ($data_list as &$val) {
            $val['description'] = $val['remark'].'，配置名：<code>'.$val['name'].'</code>';
            $val['name']        = 'config['.$val['name'].']';
            $val['confirm']     = $val['extra_class'] = $val['extra_attr']='';
            $val['options']     = parse_config_attr($val['options']);
            
        }

        $content = builder('form')
                ->setPageTips('调用方式，如：<code>config("配置名")</code>，即可调用站点统计的配置信息')
                ->setTabNav($tab_list, $group)  // 设置Tab按钮列表
                ->setExtraItems($data_list)     // 直接设置表单数据
                ->addButton('submit','确认',url('groupSave'))
                ->addButton('back') // 设置表单按钮
                ->fetch();
        return Iframe()
                ->setMetaTitle('系统设置')  // 设置页面标题
                ->content($content);
    }

    /**
     * 批量保存配置
     */
    public function groupSave($config) {
        if ($config && is_array($config)) {
            foreach ($config as $name => $value) {
                $map = ['name' => $name];
                // 如果值是数组则转换成字符串，适用于复选框等类型
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                if ($name=='develop_mode') {
                    cache('admin_sidebar_menus_'.$this->currentUser['uid'],null);//清空后台菜单缓存
                }
                $this->configModel->where($map)->update(['value'=>$value]);
            }
        }
        cache('DB_CONFIG_DATA',null);
        $this->success('保存成功！');
    }

    /**
     * 高级配置
     * @return [type] [description]
     * @date   2018-02-16
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function advanced()
    {
        $config_fields = ['cache','session','cookie','redis','memcache'];
        if (IS_POST) {
            $params = $this->request->param();

            foreach ($config_fields as $key => $field) {
                $this->configModel->where('name',$field)->setField('value',json_encode($params[$field]));
            }
            cache('DB_CONFIG_DATA',null);
            $this->success('提交成功');
        } else{
            $data = [
                'cache'=>[
                    'type'   => 'File',
                    'path'   => CACHE_PATH,
                    'expire' => 0
                ],
                'session'=>[
                    'type'       =>'File',
                    'prefix'     =>'eacoophp_',
                    'auto_start' =>true
                ],
                'cookie'=>[
                    'prefix'    =>'eacoophp_',
                    'expire'    =>0,
                    'path'      =>'/',
                    'secure'    =>0,
                    'setcookie' => 1,
                ],
                'redis'=>[
                    'host'    =>'127.0.0.1',
                    'port'    =>6979,
                ],
                'memcache'=>[
                    'host'    =>'127.0.0.1',
                    'port'    =>11211,
                ]
            ];
            //从数据库拿
            foreach ($config_fields as $key => $field) {
                $field_value = $this->configModel->where('name',$field)->value('value');
                if (!empty($field_value)) {
                    $data[$field] = array_merge($data[$field],json_decode($field_value,true));
                }
            }

            $options = [
                'cache'=>[
                    'type'=>'group',
                    'title'=>'缓存（Cache）<span class="f12 color-6">-全局。配置名：<code>cache</code></span>',
                    'options'=>[
                        'type'=>[
                            'title'       =>'驱动方式:',
                            'description' =>'支持的缓存类型包括file、memcache、wincache、sqlite、redis和xcache。',
                            'type'        =>'select',
                            'options'     => ['File'=>'文件','memcache'=>'Memcache','wincache'=>'wincache','sqlite'=>'Sqlite','redis'=>'redis','xcache'=>'xcache'],
                            'value'       =>'', 
                        ],
                        'path'=>[
                            'title'       =>'保存目录:',
                            'description' =>'绝对路径',
                            'type'        =>'text',
                            'value'       =>'', 
                        ],
                        'prefix'=>[
                            'title'       =>'前缀:',
                            'description' =>'',
                            'type'        =>'text',
                            'value'       =>'', 
                        ],
                        'expire'=>[
                            'title'       =>'有效期:',
                            'description' =>'缓存有效期 0表示永久缓存',
                            'type'        =>'text',
                            'value'       =>'', 
                        ]
                    ],
                ],
                'session'=>[
                    'type'=>'group',
                    'title'=>'会话（Session）<span class="f12 color-6">-全局。配置名：<code>session</code></span>',
                    'options'=>[
                        'type'=>[
                            'title'       =>'驱动方式:',
                            'description' =>'支持的类型包括file、memcache、wincache、sqlite、redis和xcache。',
                            'type'        =>'select',
                            'options'     => ['none'=>'默认','memcache'=>'Memcache','redis'=>'redis'],
                            'value'       =>'', 
                        ],
                        'prefix'=>[
                            'title'       =>'前缀:',
                            'description' =>'',
                            'type'        =>'text',
                            'value'       =>'', 
                        ],
                        'auto_start'=>[
                            'title'       =>'自动开启 SESSION:',
                            'description' =>'是否自动开启SESSION',
                            'type'        =>'radio',
                            'options'    =>[0=>'关闭',1=>'开启'],
                            'value'       =>'', 
                        ]
                    ],
                ],
                'cookie'=>[
                    'type'=>'group',
                    'title'=>'Cookie设置<span class="f12 color-6">-全局。配置名：<code>cookie</code></span>',
                    'options'=>[
                        'path'=>[
                            'title'       =>'保存路径:',
                            'description' =>'',
                            'type'        =>'text',
                            'value'       =>'', 
                        ],
                        'prefix'=>[
                            'title'       =>'前缀:',
                            'description' =>'',
                            'type'        =>'text',
                            'value'       =>'', 
                        ],
                        'expire'=>[
                            'title'       =>'有效期:',
                            'description' =>'',
                            'type'        =>'text',
                            'value'       =>'', 
                        ],
                        'domain'=>[
                            'title'       =>'有效域名:',
                            'description' =>'',
                            'type'        =>'text',
                            'value'       =>'', 
                        ],
                        'secure'=>[
                            'title'       =>'启用安全传输:',
                            'description' =>'',
                            'type'        =>'radio',
                            'options'     =>[0=>'关闭',1=>'开启'],
                            'value'       =>'', 
                        ],
                        'httponly'=>[
                            'title'       =>'httponly:',
                            'description' =>'',
                            'type'        =>'text',
                            'value'       =>'', 
                        ],
                        'setcookie'=>[
                            'title'       =>'使用setcookie:',
                            'description' =>'',
                            'type'        =>'radio',
                            'options'     =>[0=>'关闭',1=>'开启'],
                            'value'       =>'', 
                        ],
                    ],
                ],
                'redis'=>[
                    'type'=>'group',
                    'title'=>'Redis<span class="f12 color-6">-全局。配置名：<code>redis</code></span>',
                    'options'=>[
                        'host'=>[
                            'title'       =>'服务器Host:',
                            'description' =>'请填写服务器地址。配置名：<code>redis.host</code>',
                            'type'        =>'text',
                            'value'       =>'',
                        ],
                        'port'=>[
                            'title'       =>'端口port:',
                            'description' =>'redis服务器端口。配置名：<code>redis.port</code>',
                            'type'        =>'number',
                            'value'       =>'',
                        ]
                    ],
                ],
                'memcache'=>[
                    'type'=>'group',
                    'title'=>'Memcache<span class="f12 color-6">-全局。配置名：<code>memcache</code></span>',
                    'options'=>[
                        'host'=>[
                            'title'       =>'服务器Host:',
                            'description' =>'请填写服务器地址。配置名：<code>memcache.host</code>',
                            'type'        =>'text',
                            'value'       =>'',
                        ],
                        'port'=>[
                            'title'       =>'端口port:',
                            'description' =>'memcache服务器端口。配置名：<code>memcache.port</code>',
                            'type'        =>'number',
                            'value'       =>''
                        ]
                    ],
                ],
        ];

        $options = logic('common/Config')->buildFormByFiled($options,$data);
        $tab_list = logic('admin/Config')->getTabList();
        $content = builder('Form')
                ->setMetaTitle('高级设置')  //设置页面标题
                ->setPageTips('调用方式，如：<code>config("配置名")</code>，即可调用站点统计的配置信息')
                ->setTabNav($tab_list,'advanced')  // 设置页面Tab导航
                ->setExtraItems($options) //直接设置表单数据
                //->setFormData($data)
                //->setAjaxSubmit(false)
                ->addButton('submit')
                ->addButton('back')    // 设置表单按钮
                ->fetch();

            return Iframe()
                ->setMetaTitle('高级设置')  // 设置页面标题
                ->content($content);
        }
    }

    /**
     * 附件选项
     * @return [type] [description]
     * @date   2017-11-15
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function attachmentOption($tab_list = [])
    {   
        if (empty($tab_list)) {
            // 设置Tab导航数据列表
            $tab_list = logic('admin/Config')->getTabList();
        }
        if (IS_POST) {
            // 提交数据
            $attachment_data = input('post.');
            $data['value'] = json_encode($attachment_data);
            if ($data) {
                $result =$this->configModel->allowField(true)->save($data,['name'=>'attachment_options']);
                if ($result) {
                    cache('cdn_domain',null);
                    cache('DB_CONFIG_DATA',null);//清理缓存
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            } else {
                $this->error('数据为空');
            }
        } else {
            
            $info = config('attachment_options');//获取配置值
            
            if (!isset($info['water_opacity']) || empty($info['water_opacity'])) {
                $info['water_opacity']=100;
            }
            if (!isset($info['watermark_type']) || empty($info['watermark_type'])) {
                $info['watermark_type'] = 1;
            }
            if (!isset($info['water_img']) || empty($info['water_img'])) {
                $info['water_img'] = './logo.png';
            }
            //自定义表单项
            $content = builder('Form')
                    ->setPageTips('调用方式，如：<code>config("配置名")</code>，即可调用站点统计的配置信息')
                    ->setTabNav($tab_list,'attachment_option')  // 设置页面Tab导航
                    ->addFormItem('driver', 'select', '上传驱动', '选择上传驱动插件用于七牛云、又拍云等第三方文件上传的扩展',upload_drivers())
                    ->addFormItem('file_max_size', 'number', '上传的文件大小限制', '文件上传大小单位：kb (0-不做限制)')
                    ->addFormItem('file_exts', 'text', '允许上传的文件后缀', '多个后缀用逗号隔开，不填写则不限制类型')
                    ->addFormItem('file_save_name', 'text', '上传文件命名规则', 'date,md5,sha1,自定义规则')
                    ->addFormItem('image_max_size', 'number', '图片上传大小限制', '0为不限制大小，单位：kb')
                    ->addFormItem('image_exts', 'text', '允许上传的图片后缀', '多个后缀用逗号隔开，不填写则不限制类型')
                    ->addFormItem('image_save_name', 'text', '上传图片命名规则', 'date,md5,sha1,自定义规则')
                    ->addFormItem('page_number', 'number', '每页显示数量', '附件管理每页显示的数量')
                    ->addFormItem('widget_show_type', 'radio', '附件选择器显示方式', '在附件选择器中显示的附件内容',[0=>'所有',1=>'当前用户'])
                    ->addFormItem('section', 'section', '缩略图', '下列设置图像尺寸为上传生成缩略图尺寸,以像素px为单位。')
                    ->addFormItem('cut', 'radio', '生成缩略图', '上传图像同时生成缩略图，并保留原图（建议开启）',[1=>'是',0=>'否'])
                    ->addFormItem('small_size', 'self', '小尺寸', '',$this->settingInputHtml($info['small_size'],'small_size'))
                    ->addFormItem('medium_size', 'self', '中等尺寸', '',$this->settingInputHtml($info['medium_size'],'medium_size'))
                    ->addFormItem('large_size', 'self', '大尺寸', '',$this->settingInputHtml($info['large_size'],'large_size'))
                    ->addFormItem('section', 'section', '添加水印', '给上传的图片添加水印。')
                    ->addFormItem('watermark_scene', 'select', '场景', '',['none'=>'',1=>'不添加水印',2=>'上传同时添加水印',3=>'只限普通图片添加水印',4=>'只限商品图片添加水印'])
                    ->addFormItem('watermark_type', 'radio', '水印类型', '暂不支持文字水印',[1=>'图片水印',2=>'文字水印'])
                    ->addFormItem('water_position', 'select', '水印位置', '',['none'=>'',1=>'左上角',2=>'上居中',3=>'右上角',4=>'左居中',5=>'居中',6=>'右居中',7=>'左下角',8=>'下居中',9=>'右下角'])
                    ->addFormItem('water_img', 'image', '水印图片', '请选择水印图片')
                    ->addFormItem('water_opacity', 'number', '水印透明度', '默认100')
                    ->setFormData($info)
                    //->setAjaxSubmit(false)
                    ->addButton('submit')    // 设置表单按钮
                    ->fetch();

            return Iframe()
                ->setMetaTitle('多媒体设置')  // 设置页面标题
                ->content($content);
        }
    }

    /**
     * 设置缩略图尺寸的输入框
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    private function settingInputHtml($data = [], $type='', $extra_attr='')
    {
        if (!$data||!$type) return false;
        return '
        <div class="col-xs-3"><div class="input-group input-group-sm"><span class="input-group-addon">宽度</span><input type="number" class="form-control" name="'.$type.'[width]" value="'.$data['width'].'" '.$extra_attr.'></div> </div><div class="col-xs-3"><div class="input-group input-group-sm"><span class="input-group-addon">高度</span><input type="number" class="form-control" name="'.$type.'[height]" value="'.$data['height'].'" '.$extra_attr.'></div></div>
        ';
    }

    /**
     * 网站信息设置
     * @param  integer $sub_group [description]
     * @return [type] [description]
     * @date   2017-10-17
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function website($sub_group=0)
    {
        //根据分组获取配置
        $map['status'] = ['egt', '0'];  // 禁用和正常状态
        $map['group']  = 6;//6是大分组网站信息
        $map['sub_group'] = $sub_group;
        $data_list = $this->configModel->getList($map,true,'sort asc,id asc');

        // 设置Tab导航数据列表
        $config_subgroup_list = config('website_group');  // 获取配置分组
        foreach ($config_subgroup_list as $key => $val) {
            $tab_list[$key]['title'] = $val;
            $tab_list[$key]['href']  = url('website', array('sub_group' => $key));
        }

        // 构造表单名、解析options
        foreach ($data_list as &$data) {
            $data['name']        = 'config['.$data['name'].']';
            $data['description'] = $data['remark'];
            $data['confirm']     = $data['extra_class'] = $data['extra_attr']='';
            $data['options']     = parse_config_attr($data['options']);
        }

        // 使用FormBuilder快速建立表单页面。

        $content = builder('form')
                ->SetTabNav($tab_list, $sub_group)  // 设置Tab按钮列表
                ->setPostUrl(url('groupSave'))    // 设置表单提交地址
                ->setExtraItems($data_list)     // 直接设置表单数据
                ->addButton('submit','确认',url('groupSave'))->addButton('back')    // 设置表单按钮
                ->fetch();

        return Iframe()
                ->setMetaTitle('网站设置')  // 设置页面标题
                ->content($content);
    }

    /**
     * 移动配置分组
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function moveGroup() {
        if (IS_POST) {
            $ids      = input('post.ids');
            $from_gid = input('post.from_gid');
            $to_gid   = input('post.to_gid');
            if ($from_gid === $to_gid) {
                $this->error('目标分类与当前分类相同');
            }
            if ($to_gid) {
                $map['id'] = array('in',$ids);
                $data      = array('group' => $to_gid);
                $this->editRow('config', $data, $map, array('success'=>'移动成功','error'=>'移动失败',url('index')));

            } else {
                $this->error('请选择目标配置组');
            }
        }
    }
    /**
     * 构建列表移动配置分组按钮
     * @author 心云间、凝听 <981248356@qq.com>
     */
    protected function moveGroupHtml($config_group_list,$group_id){
            //构造移动文档的目标分类列表
            $options = '';
            foreach ($config_group_list as $key => $val) {
                $options .= '<option value="'.$key.'">'.$val.'</option>';
            }
            //文档移动POST地址
            $move_url = url('moveGroup');

            return <<<EOF
            <div class="modal fade mt100" id="moveModal">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span></button>
                            <p class="modal-title">移动至</p>
                        </div>
                        <div class="modal-body">
                            <form action="{$move_url}" method="post" class="form-move">
                                <div class="form-group">
                                    <select name="to_gid" class="form-control">{$options}</select>
                                </div>
                                <div class="form-group">
                                    <input type="hidden" name="ids">
                                    <input type="hidden" name="from_gid" value="{$group_id}">
                                    <button class="btn btn-primary btn-block submit ajax-post" type="submit" target-form="form-move">确 定</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <script type="text/javascript">
                function move(){
                    var ids = '';
                    $('input[name="ids[]"]:checked').each(function(){
                       ids += ',' + $(this).val();
                    });
                    if(ids != ''){
                        ids = ids.substr(1);
                        $('input[name="ids"]').val(ids);
                        $('.modal-title').html('移动选中的配置至：');
                        $('#moveModal').modal('show', 'fit')
                    }else{
                        updateAlert('请选择需要移动的配置', 'warning');
                    }
                }
            </script>
EOF;
    }
}
