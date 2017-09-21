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
    }

    /**
     * 安装主题
     */
    public function install($name){
        // 获取当前主题信息
        $info = ThemeModel::getInfoByFile($name);
        if (!$info) {
            $this->error('安装失败');
        }
        $config = ThemeModel::getDefaultConfig($name);//获取文件中的默认配置值
        $info['config'] = !empty($config) ? json_encode($config) : '';

        // 写入数据库记录
        $result = $this->themeModel->allowField(true)->isUpdate(false)->data($info)->save();
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
        $result = ThemeModel::destroy($id);
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
        $name = ThemeModel::where('id',$id)->value('name');
        // 获取当前主题信息
        $info = ThemeModel::getInfoByFile($name);
        if (!$info) {
            $this->error('安装失败');
        }
        $config = ThemeModel::getDefaultConfig($name);//获取文件中的默认配置值
        $info['config'] = !empty($config) ? json_encode($config) : '';

        $info['id'] = $id;
        
        if ($this->themeModel->editData($info,$id)) {
            $this->success('更新成功', url('index'));
        } else {
            $this->error($this->themeModel->getError());
        }

    }

    /**
     * 切换主题
     */
    public function setCurrent($id) {
        $is_res = $this->themeModel->where('id',$id)->count();
        if ($is_res) {
            // 当前主题current字段置为1
            $map = [
                'id'=>$id
            ];
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
