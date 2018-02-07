<?php
// 主题控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\Theme as ThemeModel;

class Theme extends Admin {
    
    protected $themeModel;
    
    function _initialize()
    {
        parent::_initialize();
        $this->themeModel = new ThemeModel();
    }

    /**
     * 主题列表
     * @param  string $from_type [description]
     * @return [type] [description]
     * @date   2018-01-13
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index($from_type = 'oneline') {
        $tab_list = [
            'local'=>['title'=>'已安装','href'=>url('index',['from_type'=>'local'])],
            'oneline'=>['title'=>'主题市场','href'=>url('index',['from_type'=>'oneline'])],
        ];

        $this->assign('tab_list',$tab_list);
        $this->assign('from_type',$this->request->param('from_type','oneline'));


        if ($from_type == 'local') {
            $data_list = $this->themeModel->getAll();
            $meta_title = '本地主题';

        } elseif ($from_type == 'oneline') {
            $data_list = $this->getCloudAppstore();
            $meta_title = '主题市场';

        }

        $this->assign('data_list',$data_list);
        $this->assign('meta_title',$meta_title);
        return $this->fetch('extension/themes');
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
        $res_count = ThemeModel::where(['id'=>$id,'current'=>1])->count();
        if (!$res_count) {
            $result = ThemeModel::destroy($id);
            if ($result) {
                $this->success('卸载成功！');
            } else {
                $this->error('卸载失败',$this->themeModel->getError());
            }
        } else{
            $this->error('卸载失败，请保留至少一种主题');
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

    /**
     * 刷新缓存
     * @return [type] [description]
     * @date   2017-10-30
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function refresh()
    {
        Extension::refresh('theme');
        $this->success('成功清理缓存','');
    }

    /**
     * 删除
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-11-07
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function del($name='')
    {
        if ($name) {
            @rmdirs(APP_PATH.$name);
            Extension::refresh('theme');
            $this->success('删除主题成功');
        }
        $this->error('删除主题失败');
    }

    /**
     * 获取主题市场数据
     * @return [type] [description]
     * @date   2017-09-21
     * @author 心云间、凝听 <981248356@qq.com>
     */
    private function getCloudAppstore($paged = 1)
    {
        $store_data = cache('eacoo_appstore_themes_'.$paged);
        if (empty($store_data) || !$store_data) {
            $url        = config('eacoo_api_url').'/api/appstore/themes';
            $params = [
                'paged'=>$paged
            ];
            $result = curl_post($url,$params);
            $result = json_decode($result,true);
            $store_data = $result['data'];
            cache('eacoo_appstore_themes_'.$paged,$store_data,3600);
        }
        if (!empty($store_data)) {
            $extensionObj = new Extension();
            $local_themes = $extensionObj->localApps('theme');
            foreach ($store_data as $key => &$val) {

                $val['publish_time'] = friendly_date($val['publish_time']);
                $val['right_button'] = '<a class="btn btn-primary btn-sm app-online-install" data-name="'.$val['name'].'" data-type="theme" href="javascript:void(0);" data-install-method="install">现在安装</a> ';
                if (!empty($local_themes)) {
                    foreach ($local_themes as $key => $row) {
                        if ($row['name']==$val['name']) {
                            if ($row['version']<$val['version']) {
                                $val['right_button'] = '<a class="btn btn-success btn-sm app-online-install"  data-name="'.$val['name'].'" data-type="theme" href="javascript:void(0);" data-install-method="upgrade">升级</a> ';
                            } elseif(isset($row['status']) && $row['status']==3){
                                $val['right_button'] = '<a class="btn btn-default btn-sm" href="'.url('index',['from_type'=>'local']).'">已下载</a> ';
                            } else{
                                $val['right_button'] = '<a class="btn btn-default btn-sm" href="'.url('index',['from_type'=>'local']).'">已安装</a> ';
                            }
                            
                        }
                    }
                }

                //$val['right_button'] .= '<a class="btn btn-info btn-sm" href="http://www.eacoo123.com" target="_blank">更多详情</a> ';
            }
        }
        return $store_data;
    }
}
