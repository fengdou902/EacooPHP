EacooPHP v1.3.2
===============
### 介绍
EacooPHP是基于ThinkPHP5.0.21开发的一套轻量级WEB产品开发框架，追求高效，简单，灵活。
具有完善并灵活的模块化和插件机制，模块式开发，大大降低开发成本。命令行管理应用

>支持EacooPHP的用户请给我们一个star

使用EacooPHP框架开发定制您的系统前，建议熟悉官方的tp5.0完全开发手册。

### 功能特性
- **严谨规范：** 提供一套有利于团队协作的结构设计、编码、数据等规范。
- **高效灵活：** 清晰的分层设计、钩子行为扩展机制，解耦设计更能灵活应对需求变更。
- **严谨安全：** 清晰的系统执行流程，严谨的异常检测和安全机制，详细的日志统计，为系统保驾护航。
- **构建器Builder：** 完善的构建器设计，丰富的表单组件，让开发列表和表单更得心应手。无需前端开发，省时省力。
- **简单上手快：** 结构清晰、代码规范、在开发快速的同时还兼顾性能的极致追求。
- **自身特色：** 权限管理、组件丰富、第三方应用多、分层解耦化设计和先进的设计思想。
- **高级进阶：** 分布式、负载均衡、集群、Redis、分库分表。 
- **应用中心：** 在线应用中心，后台即可在线安装模块、插件和主题。 
- **命令行：** 命令行功能，一键管理应用扩展。 

### 为什么选择EacooPHP框架？
**1.问：我的前端水平一般，使用EacooPHP会不会比较麻烦？**

答：EacooPHP的设计架构注重开发的高效灵活并保持性能高效，基于Builder构建器开发表单和列表，代码量非常少，后台的列表和表单简单构建，而且这个过程不需要创建view层模版文件，功能非常强大。

**2.问：我对ThinkPHP3.2/5.0有基础，学习EacooPHP容易上手开发项目吗？**

答：EacooPHP框架是基于ThinkPHP5开发的一款框架，结合tp5文档和本文档一起学习会比较容易上手。而且该框架独有开发设计，是您不错的选择。

**3.问：我们的系统功能多、体系复杂、需求变化也多，担心出现性能问题和代码维护不变！**

答：EacooPHP框架提供一套开发规范利于团队协作，系统执行流程清晰，代码结构分层设计维护方便，逻辑解耦。并且分布式、负载均衡、Redis、缓存等都有文档说明。

### 用法
例：创建一个列表页面
```
//配置高级查询
Iframe()->search([
    ['name'=>'reg_time_range','type'=>'daterange','extra_attr'=>'placeholder="注册时间"'],
    ['name'=>'status','type'=>'select','title'=>'状态','options'=>[1=>'正常',0=>'禁用']],
    ['name'=>'sex','type'=>'select','title'=>'性别','options'=>[0=>'未知',1=>'男',2=>'女']],
    ['name'=>'is_lock','type'=>'select','title'=>'是否锁定','options'=>[0=>'否',1=>'是']],
    ['name'=>'actived','type'=>'select','title'=>'激活','options'=>[0=>'否',1=>'是']],
    ['name'=>'keyword','type'=>'text','extra_attr'=>'placeholder="请输入查询关键字"'],
])

// 构建器构建用户列表
$condition =[
	'status'=> ['egt', '0'], // 禁用和正常状态
];
list($data_list,$total) = model('common/User')->search()->getListByPage($condition,true,'create_time desc',15);
      
return builder('list')
        ->setMetaTitle('用户列表') // 设置页面标题
        ->addTopButton('addnew')  // 添加新增按钮
        ->addTopButton('resume')  // 添加启用按钮
        ->addTopButton('forbid')  // 添加禁用按钮
        ->addTopButton('delete')  // 添加删除按钮
        ->setActionUrl(url('grid')) //设置请求地址
        ->keyListItem('uid', 'UID')
        ->keyListItem('avatar', '头像', 'avatar')
        ->keyListItem('nickname', '昵称')
        ->keyListItem('sex_text', '性别')
        ->keyListItem('username', '用户名')
        ->keyListItem('email', '邮箱')
        ->keyListItem('mobile', '手机号')
        ->keyListItem('reg_time', '注册时间')
        ->keyListItem('lock_text', '锁定','label_bool')
        ->keyListItem('actived', '激活','bool')
        ->keyListItem('status_text', '状态','status')
        ->keyListItem('right_button', '操作', 'btn')
        ->setListPrimaryKey('uid')
        ->setListData($data_list)    // 数据列表
        ->setListPage($total) // 数据列表分页
        ->addRightButton('edit')
        ->addRightButton('forbid')
        ->fetch();
```
### 效果图
![效果图](https://github.com/fengdou902/EacooPHP/blob/dev/eacoophp-demo-builderlist-1.png)

### 表单构建器
```
// 大量丰富的表单构建
return Builder('Form')
        ->setTabNav($tab_list, 'builderform')  // 设置页面Tab导航
        ->addFormItem('id', 'hidden', 'ID', '')//这个字段一般是默认添加
        ->addFormItem('title', 'text', '标题', '使用文本字段text','','required')
        ->addFormItem('password', 'password', '密码', '密码字段password','','placeholder="留空则不修改密码"')
        ->addFormItem('email', 'email', '邮箱', '邮箱字段email','','required')
        ->addFormItem('sex', 'radio', '性别', '单选框形式radio',[0=>'保密',1=>'男',2=>'女'])
        ->addFormItem('sex', 'select', '性别', '下拉框形式select',['none'=>'请设置性别',0=>'保密',1=>'男',2=>'女'])
        ->addFormItem('picture', 'picture', '单图片1', '添加单个图片picture，基于图片选择器')
        ->addFormItem('image', 'image', '单图片2', '添加单个图片image，直接上传并保持图片地址')
        ->addFormItem('pictures', 'pictures', '多图片', '添加多个图片pictures，基于图片选择器')
        ->addFormItem('file', 'file', '单个文件', '添加单个文件file')
        ->addFormItem('files', 'files', '多个文件', '添加多个文件files')
        ->addFormItem('region', 'region', '地区三级', '地区字段region，实现地区三级联动选择。基于地区管理插件',json_decode($info['region'],true))
        //基于repeater控件
        ->addFormItem('repeater_content', 'repeater', '自定义数据', '根据repeater控件生成，该示例一个处理多图',[
            'options'=>
                    [
                        'img'  =>['title'=>'图片','type'=>'image','default'=>'','placeholder'=>''],
                        'url'  =>['title'=>'链接','type'=>'url','default'=>'','placeholder'=>'http://'],
                        'text' =>['title'=>'文字','type'=>'text','default'=>'','placeholder'=>'输入文字'],
                    ]
            ]
        )
        ->addFormItem('description', 'textarea', '个人说明', '大文本框texarea')
        ->addFormItem('content', 'wangeditor', '详情内容', '使用编辑器wangeditor')
        ->addFormItem('content1', 'ueditor', '详情内容', '使用编辑器ueditor')
        ->addFormItem('datetime', 'datetime', '时间选取器', '时间选择器组件datetime')
        ->addFormItem('daterange', 'daterange', '时间范围', '时间范围选择器组件daterange')
        ->addFormItem('sort', 'number', '排序', '按照数值大小的倒叙进行排序，数值越小越靠前')
        ->addFormItem('status', 'radio', '状态', '',[1=>'正常',0=>'禁用'])
        ->setFormData($info)
        //->setAjaxSubmit(false)//是否禁用ajax提交，普通提交方式
        ->addButton('submit')->addButton('back')    // 设置表单按钮
        ->fetch();
```
### 效果图：
![效果图](https://github.com/fengdou902/EacooPHP/blob/dev/eacoophp-demo-builderform-1.png)

### 命令行：
命令行操作：
```
一键创建模块：php think module -a 模块名(英文) -c create
一键创建插件：php think plugin -a 插件名(英文) -c create
一键创建主题：php think theme -a 主题名(英文) -c create
```

#### 更多神级操作，高并发，读写分离，分库分表，大数据量解决方案。

### 前端组件
artTemplate(JS模版引擎),artDialog(弹窗),datetimepicker(日期),echarts(图表),colorpicker(颜色选择器),fastclick,iCheck(复选框美化),ieonly,imgcutter,jquery-repeater,lazyload(延迟加载),select2,superslide,ueditor,wangeditor,webuploader,x-editable

官网：[https://www.eacoophp.com](https://www.eacoophp.com)
QQ群: 1082768796
### 演示地址
[http://demo1.eacoophp.com/admin](http://demo1.eacoophp.com/admin)  
账号：admin  
密码：123456 

### 项目地址
(记得给项目加个star哦)  
码云gitee：[https://gitee.com/ZhaoJunfeng/EacooPHP.git](https://gitee.com/ZhaoJunfeng/EacooPHP.git)  
GitHub：[https://github.com/fengdou902/EacooPHP.git](https://github.com/fengdou902/EacooPHP.git)  

### 鸣谢
感谢以下的项目,排名不分先后
[ThinkPHP](http://www.thinkphp.cn)、[JQuery](http://jquery.com/)、[Bootstrap](http://getbootstrap.com/)、[AdminLTE](https://almsaeedstudio.com)、[Select2](https://github.com/select2/select2)等优秀开源项目。
### 版权申明
EacooPHP遵循Apache2开源协议发布，并提供免费使用。  
本项目包含的第三方源码和二进制文件之版权信息另行标注。  
版权所有Copyright © 2017-2019 by EacooPHP (http://www.eacoophp.com)  
All rights reserved。
