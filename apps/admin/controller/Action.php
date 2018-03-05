<?php
// 行为控制器       
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

use think\Db;

class Action extends Admin {

	/**
	 * 用户行为列表
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function index() {

		//获取列表数据
		$map['status']  = ['gt',-1];  // 禁用和正常状态
		list($data_list,$total) 
			= model('action')//
			->search() //添加搜索查询
			->getListByPage($map,'id,name,title,depend_type,depend_flag,log,remark,status','id desc');

        return builder('list')
    			->setMetaTitle('用户行为')  // 设置页面标题
    			->setPageTips('定义用户的操作行为，定义后的行为系统会根据行为规则进行处理。建议将敏感的操作设置行为，方便记录。')
	    		->addTopButton('addnew')    // 添加新增按钮
	            ->addTopButton('resume')  // 添加启用按钮
	            ->addTopButton('forbid')  // 添加禁用按钮
	            ->addTopButton('delete')  // 添加禁用按钮
	            //->setSearch() //添加搜索框
	    		->keyListItem('id','编码')
	            ->keyListItem('name','标识')
	            ->keyListItem('title','行为名称')
	            //->keyListItem('action_type_text','行为类型')
	            ->keyListItem('depend_type','来源类型','array',[0=>'未知',1=>'模块',2=>'插件',3=>'主题'])
	            ->keyListItem('depend_flag','来源标识')
	            ->keyListItem('log','日志规则')
	            ->keyListItem('remark','描述')
	            ->keyListItem('status', '状态', 'status')
	            ->keyListItem('right_button', '操作', 'btn')
	            ->setListData($data_list)     // 数据列表
	            ->setListPage($total)  // 数据列表分页
	            ->addRightButton('edit')
	            ->addRightButton('delete')  // 添加删除按钮
	            ->fetch();
	}

	/**
	 * 编辑用户行为
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function edit($id = 0) {
		$title = $id ? "编辑":"新增";

		if (IS_POST) {
			$data = $this->request->param();
            //验证数据
            $this->validateData($data,'Action');
            //$data里包含主键，则editData就会更新数据，否则是新增数据
            if (model('action')->editData($data)) {
                $this->success($title.'成功', url('index'));
            } else {
                $this->error(model('action')->getError());
            }

		} else {

			$info = ['action_type'=>1,'depend_type'=>1,'status'=>1];
            if ($id>0) {
                $info = model('action')->find($id);
            }
            $depend_flag = logic('Auth')->getDependFlags($info['depend_type']);
            $extra_html = logic('Auth')->getFormMenuHtml();//获取表单菜单html

            return builder('Form')
        		->setMetaTitle($title.'行为')  // 设置页面标题
                ->addFormItem('id', 'hidden', 'ID', 'ID')
                ->addFormItem('name', 'text', '行为标识', '输入行为标识 英文字母')
                ->addFormItem('title', 'text', '行为名称', '输入行为名称')
                ->addFormItem('depend_type', 'select', '来源类型', '来源类型。分别是模块，插件，主题',[1=>'模块',2=>'插件',3=>'主题'])
                    ->addFormItem('depend_flag', 'select', '来源标识', '请选择标识名，模块、插件、主题的标识名',$depend_flag)
                ->addFormItem('action_type', 'radio', '行为执行类型', '',[1=>'自定义操作',2=>'记录操作'])
                ->addFormItem('remark', 'text', '行为描述', '')
                ->addFormItem('rule', 'text', '行为规则', '输入行为规则，不写则只记录日志')
                ->addFormItem('log', 'text', '日志规则', '记录日志备注时按此规则来生成，支持[变量|函数]。目前变量有：user,time,model,record,data')
                ->addFormItem('status', 'select', '状态', '',[0=>'禁用',1=>'启用'])
                ->setFormData($info)//->setAjaxSubmit(false)
                ->setExtraHtml($extra_html)
                ->addButton('submit')->addButton('back')    // 设置表单按钮
                ->fetch();

		}
	}

	/**
	 * 行为日志列表
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function log() {

		list($data_list,$total) = model('actionLog')->getListByPage([],true,'create_time desc');
		if (!empty($data_list)) {
			foreach ($data_list as $key => &$row) {
				$row['action_name']=$row->action_name;
			}
		}
        return builder('list')
	        		->setMetaTitle('行为日志')  // 设置页面标题
	        		->setPageTips('根据用户行为，自动记录后台日志记录')  // 设置页面标题
	        		->addTopButton('self', ['title'=>'清空日志','href'=>url('clearLog'),'class'=>'btn btn-warning btn-sm ajax-get confirm','icon'=>'fa fa-trash','hide-data'=>'true']) //清空
	                ->addTopButton('delete',['href'=>url('admin/Action/dellog')])  // 添加禁用按钮
	        		->keyListItem('action_name','行为标识')
	                ->keyListItem('url','URL')
	                ->keyListItem('request_method','请求类型')
	                ->keyListItem('nickname','执行者')
	                ->keyListItem('remark','备注')
	                ->keyListItem('ip','IP')
	                ->keyListItem('create_time','执行时间')
	                ->keyListItem('right_button', '操作', 'btn')
	                ->setListData($data_list)     // 数据列表
	                ->setListPage($total)
	                //->setListPage($data_list->render())  // 数据列表分页
	                ->addRightButton('self',['href'=>url('detail',['id'=>'__data_id__']),'title'=>'详情','icon'=>'fa fa-view'])
	                ->addRightButton('delete')  // 添加删除按钮
	                ->fetch();

	}

	/**
	 * 查看行为日志
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function detail($id = 0) {
		$this->assign('meta_title','日志详情');

		if (empty($id)) {
			$this->error('参数错误！');
		}

		$info = model('actionLog')->alias('a')->where('a.id',$id)->join('__USERS__ b','a.uid = b.uid')->join('__ACTION__ c','a.action_id = c.id')->order('a.create_time desc')->field('a.*,b.nickname,c.name,c.title')->find();
		$info['nickname']= db('users')->where('uid',$info['uid'])->value('nickname');
		//$info['action_ip']   = long2ip($info['action_ip']);
		if ($info['ip']!='' && $info['ip']!='127.0.0.1') {
			$ip_info         = curl_get('http://www.ip.cn/?ip='.$info['ip']);
			$sub_content     = get_sub_content($ip_info,'<div class="well">','</div>');
			$sub_content     = get_sub_content($sub_content,'<p>所在地理位置','<p>GeoIP');
			$info['ip_city'] = get_sub_content($sub_content,'<code>','</p>');
		}
		
		$this->assign('info',$info);
		
		return $this->fetch();
	}
	
	/**
	 * 删除日志
	 * @param mixed $id
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function delLog() {
		$ids = input('param.ids/a');
		if (empty($ids)) {
			$this->error("非法操作！", '');
		}
		$map['id'] = array('IN', $ids);
		$res       = model('actionLog')->where($map)->delete();
		if ($res !== false) {
			$this->success('删除成功！');
		} else {
			$this->error('删除失败！');
		}
	}

	/**
	 * 清空日志
	 */
	public function clearLog($id = '') {
		$res = Db::execute('truncate table '.config('database.prefix').'action_log');
		if ($res !== false) {
			$this->success('日志清空成功！');
		} else {
			$this->error('日志清空失败！');
		}
	}
}