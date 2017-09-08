<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://www.eacoo123.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\Theme as ThemeModel;
use app\admin\builder\Builder;
/**
 * 主题控制器
 */
class Theme extends Admin {
    
    protected $themeModel;
    
    function _initialize()
    {
        parent::_initialize();
        $this->themeModel = new ThemeModel();
    }

    /**
     * 默认方法
     */
    public function index() {
        $this->assign('meta_title','主题列表');

        $data_list = $this->themeModel->getAll();

        $attr['title'] = '取消多主题支持';
        $attr['class'] = 'btn btn-primary ajax-get';
        $attr['href']  = url('admin/Theme/cancel');

        $this->assign('theme_items',$data_list);
        return $this->fetch('extend/themes');
        // // 使用Builder快速建立列表页面。
        // $builder = new Builder();
        // $builder->setMetaTitle('主题列表')  // 设置页面标题
        //         ->addTopButton('self', $attr)
        //         ->keyListItem('name', '名称')
        //         ->keyListItem('title', '标题')
        //         ->keyListItem('description', '描述')
        //         ->keyListItem('developer', '开发者')
        //         ->keyListItem('version', '版本')
        //         //->keyListItem('create_time', '创建时间', 'time')
        //         ->keyListItem('status', '状态')
        //         ->keyListItem('right_button', '操作', 'btn')
        //         ->setListData($data_list)     // 数据列表
        //         ->fetch();
    }

    /**
     * 安装主题
     */
    public function install($name){
        // 获取当前主题信息
        //$config_file = realpath(THEME_PATH.$name).'/'.$this->themeModel->info_file();
        $config_file = realpath('./'.THEME_PATH.$name).'/'.$this->themeModel->info_file();
        if (!$config_file) {
            $this->error('安装失败');
        }
        $config = include $config_file;
        $data   = $config['info'];
        if ($config['config']) {
            $data['config'] = json_encode($config['config'],true);
        }
        //验证数据
        // $vali_result = $this->validateData('Theme',$data);
        // if (true !== $vali_result) {
        //     $this->error($vali_result);
        // }
        // 写入数据库记录
        $result = $this->themeModel->allowField(true)->isUpdate(false)->data($data)->save();
        if ($result) {
            $this->success('安装成功', url('index'));
        } else {
            $this->error($this->themeModel->getError());
        }

    }

    /**
     * 卸载主题
     */
    public function uninstall($id) {
        // 当前主题禁止卸载
        $result = $this->themeModel->delete($id);
        if ($result) {
            $this->success('卸载成功！');
        } else {
            $this->error('卸载失败',$this->themeModel->getError());
        }
    }

    /**
     * 更新主题信息
     */
    public function updateInfo($id) {
        $name = $this->themeModel->getFieldById($id, 'name');
        $config_file = realpath(THEME_PATH.$name).'/'.$this->themeModel->info_file();
        if (!$config_file) {
            $this->error('不存在安装文件');
        }
        $config = include $config_file;
        $data = $config['info'];
        if ($config['config']) {
            $data['config'] = json_encode($config['config']);
        }
        $data['id'] = $id;
        
        if ($this->themeModel->editData($data,$id)) {
            $this->success('更新成功', url('index'));
        } else {
            $this->error($this->themeModel->getError());
        }

    }

    /**
     * 切换主题
     */
    public function setCurrent($id) {
        $theme_info = $this->themeModel->find($id);
        if ($theme_info) {
            // 当前主题current字段置为1
            $map['id'] = array('eq', $id);
            $result1 = $this->themeModel->where($map)->update(['current'=>1]);
            if ($result1) {
                // 其它主题current字段置为0
                $map = [];
                $map['id'] = ['neq', $id];
                if ($this->themeModel->where($map)->count() == 0) {
                    $this->success('前台主题设置成功！');
                }
                $con['id'] = ['neq', $id];
                $result2 = $this->themeModel->where($con)->update(['current'=>0]);
                if ($result2) {
                    $this->success('前台主题设置成功！');
                } else {
                    $this->error('设置当前主题失败', $this->themeModel->getError());
                }
            } else {
                $this->error('设置当前主题失败',$this->themeModel->getError());
            }
        } else {
            $this->error('主题不存在');
        }
    }

    /**
     * 取消主题
     */
    public function cancel() {
        $this->themeModel->where(true)->update(['current'=>0]);
        $map = [];
        $map['current'] = ['eq', 1];
        if ($this->themeModel->where($map)->count() == 0) {
            $this->success('取消主题成功！');
        } else {
            $this->error('取消主题失败', $this->themeModel->getError());
        }
    }
}
