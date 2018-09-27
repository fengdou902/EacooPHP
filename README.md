EacooPHP v1.2.8
===============
### 介绍
EacooPHP是基于ThinkPHP5.0.21开发的一套轻量级WEB产品开发框架，追求高效，简单，灵活。
具有完善并灵活的模块化和插件机制，模块式开发，大大降低开发成本。命令行管理应用

>支持EacooPHP的用户请给我们一个star

使用EacooPHP框架开发定制您的系统前，建议熟悉官方的tp5.0完全开发手册。

![eacoophp封面图](https://github.com/fengdou902/EacooPHP/blob/master/screenshot.jpeg)

### 功能特性
- **严谨规范：** 提供一套有利于团队协作的结构设计、编码、数据等规范。
- **高效灵活：** 清晰的分层设计、钩子行为扩展机制，解耦设计更能灵活应对需求变更。
- **严谨安全：** 清晰的系统执行流程，严谨的异常检测和安全机制，详细的日志统计，为系统保驾护航。
- **构建器Builder：** 完善的构建器设计，丰富的组件，让开发列表和表单更得心应手。无需模版开发，省时省力。
- **简单上手快：** 结构清晰、代码规范、在开发快速的同时还兼顾性能的极致追求。
- **自身特色：** 权限管理、组件丰富、第三方应用多、分层解耦化设计和先进的设计思想。
- **高级进阶：** 分布式、负载均衡、集群、Redis、分库分表。 
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
// 获取所有用户
$map =[
	'status'=> ['egt', '0'], // 禁用和正常状态
];
list($data_list,$total) = model('common/User')->search('username|nickname')->getListByPage($map,true,'create_time desc',12);

$reset_password = [
    'icon'=> 'fa fa-recycle',
    'title'=>'重置原始密码',
    'class'=>'btn btn-default ajax-table-btn confirm btn-sm',
    'confirm-info'=>'该操作会重置用户密码为123456，请谨慎操作',
    'href'=>url('resetPassword')
];
        
return builder('List')
        ->setMetaTitle('用户管理') // 设置页面标题
        ->addTopButton('addnew')  // 添加新增按钮
        ->addTopButton('delete')  // 添加删除按钮
        ->addTopButton('self',$reset_password)  // 添加重置按钮
        ->setSearch('custom','请输入关键字')//自定义搜索框
        ->keyListItem('uid', 'UID')
        ->keyListItem('avatar', '头像', 'avatar')
        ->keyListItem('nickname', '昵称')
        ->keyListItem('username', '用户名')
        ->keyListItem('email', '邮箱')
        ->keyListItem('mobile', '手机号')
        ->keyListItem('reg_time', '注册时间')
        ->keyListItem('allow_admin', '允许进入后台','status')
        ->keyListItem('status', '状态', 'array',[0=>'禁用',1=>'正常',2=>'待验证'])
        ->keyListItem('right_button', '操作', 'btn')
        ->setListPrimaryKey('uid')//设置主键uid（默认id）
        ->setExtraHtml($extra_html)//自定义html
        ->setListData($data_list)    // 数据列表
        ->setListPage($total,12) // 数据列表分页
        ->addRightButton('edit') //添加编辑按钮
        ->addRightButton('delete')  // 添加编辑按钮
        ->fetch();
```
### 效果图
![效果图](https://github.com/fengdou902/EacooPHP/blob/dev/builder-list-user-demo1.jpg)

### 前端组件
artTemplate(JS模版引擎),artDialog(弹窗),datetimepicker(日期),echarts(图表),colorpicker(颜色选择器),fastclick,iCheck(复选框美化),ieonly,imgcutter,jquery-repeater,lazyload(延迟加载),select2,superslide,ueditor,wangeditor,webuploader,x-editable

官网：[https://www.eacoophp.com](https://www.eacoophp.com)
QQ群: 436491685
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
版权所有Copyright © 2017-2018 by EacooPHP (http://www.eacoophp.com)  
All rights reserved。
