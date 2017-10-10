<?php
// 行为控制器       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2017 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author: 心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\admin\model\Action as ActionModel;
use app\common\model\ActionLog;

use app\admin\builder\Builder;
use think\Db;

class Action extends Admin {

	protected $actionModel;
    protected $actionLogModel;

    function _initialize()
    {
        parent::_initialize();

		$this->actionModel     = new ActionModel();
		$this->actionLogModel = new ActionLog();

		
    }
	/**
	 * 用户行为列表
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function index() {

		//获取列表数据
		$map['status']  = ['gt',-1];  // 禁用和正常状态
		list($data_list,$page) = $this->actionModel->getListByPage($map,'id desc','*',15);

        Builder::run('List')
        		->setMetaTitle('用户行为')  // 设置页面标题
        		->addTopButton('addnew')    // 添加新增按钮
                ->addTopButton('resume')  // 添加启用按钮
                ->addTopButton('forbid')  // 添加禁用按钮
                ->addTopButton('delete')  // 添加禁用按钮
        		->keyListItem('id','编码')
                ->keyListItem('name','标识')
                ->keyListItem('title','行为名称')
                ->keyListItem('action_type_text','行为类型')
                ->keyListItem('log','日志规则')
                ->keyListItem('status', '状态', 'status')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListData($data_list)     // 数据列表
                ->setListPage($page)  // 数据列表分页
                ->addRightButton('edit')->addRightButton('forbid')->addRightButton('delete')  // 添加删除按钮
                ->fetch();
	}

	/**
	 * 新建用户行为
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function add() {
		if (IS_POST) {
			$data   = input('post.');
			$result = $this->actionModel->save($data);
			if (false != $result) {
				return $this->success('添加成功！', url('index'));
			} else {
				return $this->error($this->actionModel->getError());
			}
		} else {
			$data = [
				'keyList' => $this->actionModel->fieldlist,
			];
			$this->assign($data);
			$this->setMeta("添加行为");
			return $this->fetch('public/edit');
		}
	}

	/**
	 * 编辑用户行为
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function edit($id = 0) {
		$title = $id ? "编辑":"新增";

		if (IS_POST) {
			$data = input('post.');
            //验证数据
            $result = $this->validate($data,'Action');
            if(true !== $result){
                // 验证失败 输出错误信息
                $this->error($result);exit;
            } else{
	            $id   =isset($data['id']) && $data['id']>0 ? $data['id'] : false;
	            if ($this->actionModel->editData($data,$id)) {
	                $this->success($title.'成功', url('index'));
	            } else {
	                $this->error($this->actionModel->getError());
	            }
	        }

		} else {

			$info = ['action_type'=>1];
            if ($id>0) {
                $info = $this->actionModel->find($id);
            }

            Builder::run('Form')
            		->setMetaTitle($title.'行为')  // 设置页面标题
                    ->addFormItem('id', 'hidden', 'ID', 'ID')
                    ->addFormItem('name', 'text', '行为标识', '输入行为标识 英文字母')
                    ->addFormItem('title', 'text', '行为名称', '输入行为名称')
                    ->addFormItem('action_type', 'radio', '行为执行类型', '',[1=>'自定义操作',2=>'记录操作'])
                    ->addFormItem('remark', 'text', '行为描述', '')
                    ->addFormItem('rule', 'text', '行为规则', '输入行为规则，不写则只记录日志')
                    ->addFormItem('log', 'text', '日志规则', '记录日志备注时按此规则来生成，支持[变量|函数]。目前变量有：user,time,model,record,data')
                    ->setFormData($info)//->setAjaxSubmit(false)
                    ->addButton('submit')->addButton('back')    // 设置表单按钮
                    ->fetch();

		}
	}

	/**
	 * 删除用户行为状态
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function del() {
		$id = input('param.id');
		if (empty($id)) {
			return $this->error("非法操作！", '');
		}
		$map['id'] = array('IN', $id);
		$result    = Action::where($map)->delete();
		if ($result) {
			return $this->success('删除成功！');
		} else {
			return $this->error('删除失败！');
		}
	}

	// /**
	//  * 修改用户行为状态
	//  * @author colin <colin@tensent.cn>
	//  */
	// public function setstatus() {
	// 	$id = $this->getArrayParam('id');
	// 	if (empty($id)) {
	// 		return $this->error("非法操作！", '');
	// 	}
	// 	$status    = input('get.status', '', 'trim,intval');
	// 	$message   = !$status ? '禁用' : '启用';
	// 	$map['id'] = array('IN', $id);
	// 	$result    = db('Action')->where($map)->setField('status', $status);
	// 	if ($result !== false) {
	// 		return $this->success('设置' . $message . '状态成功！');
	// 	} else {
	// 		return $this->error('设置' . $message . '状态失败！');
	// 	}
	// }

	/**
	 * 行为日志列表
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function log() {

		//获取列表数据
		$map['status']  = ['gt',-1];  // 禁用和正常状态
		//list($data_list,$page) = $this->actionLogModel->getListByPage($map,'id desc','*',15);
		$data_list = $this->actionLogModel->alias('a')->join('__USERS__ b','a.uid = b.uid')->join('__ACTION__ c','a.action_id = c.id')->order('a.create_time desc')->field('a.*,b.nickname,c.name')->paginate(15);

        Builder::run('List')
        		->setMetaTitle('行为日志')  // 设置页面标题
        		->addTopButton('self', ['title'=>'清空日志','href'=>url('clearLog'),'class'=>'btn btn-warning btn-sm ajax-post confirm','hide-data'=>'true']) //清空
                ->addTopButton('delete',['href'=>url('admin/Action/dellog')])  // 添加禁用按钮
        		->keyListItem('name','行为标识')
                ->keyListItem('nickname','执行者')
                ->keyListItem('request_method','请求类型')
                ->keyListItem('url','URL')
                ->keyListItem('remark','备注')
                ->keyListItem('ip','IP')
                ->keyListItem('create_time','执行时间')
                ->keyListItem('right_button', '操作', 'btn')
                ->setListData($data_list)     // 数据列表
                ->setListPage($data_list->render())  // 数据列表分页
                ->addRightButton('edit',['href'=>url('detail',['id'=>'__data_id__']),'title'=>'详情'])->addRightButton('delete')  // 添加删除按钮
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

		$info = $this->actionLogModel->alias('a')->join('__USERS__ b','a.uid = b.uid')->join('__ACTION__ c','a.action_id = c.id')->order('a.create_time desc')->field('a.*,b.nickname,c.name,c.title')->find();
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
		$ids = input('post.ids/a');
		if (empty($ids)) {
			$this->error("非法操作！", '');
		}
		$map['id'] = array('IN', $ids);
		$res       = ActionLog::where($map)->delete();
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