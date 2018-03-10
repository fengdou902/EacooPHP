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
use app\admin\logic\Theme as ThemeLogic;

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
        //$this->assign('page_config',['self'=>logic('admin/AppStore')->getAppsCenterTabList('theme')]);
        if (IS_AJAX) {
             if ($from_type == 'local') {
                $data_list = ThemeLogic::getAll();

            } elseif ($from_type == 'oneline') {
                $data_list = $this->getCloudAppstore(input('param.paged'));
            }
            
            $return = [
                'code'=>1,
                'msg'=>'成功获取应用',
                'data'=>$data_list
            ];
            return json($return);
        } else{
            $tab_list = [
                'local'=>['title'=>'本地主题','href'=>url('index',['from_type'=>'local'])],
                'oneline'=>['title'=>'主题市场','href'=>url('index',['from_type'=>'oneline'])],
            ];

            $this->assign('tab_list',$tab_list);
            $this->assign('from_type',$this->request->param('from_type','oneline'));
            if ($from_type == 'local') {
                $meta_title = '本地主题';

            } elseif ($from_type == 'oneline') {
                $meta_title = '主题市场';

            }

            $this->assign([
                'meta_title'=>$meta_title,
                'page_tips'=>'主题是前台显示的网页，系统会自动根据启用的主题来展示。当只启用了一种设备主题，系统会自动判断只显示一种！'
            ]);
            return $this->fetch('extension/themes');
        }
        


        
    }

    /**
     * 安装主题
     * @param  [type] $name [description]
     * @return [type] [description]
     * @date   2018-03-03
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function install($name=''){
        // 获取当前主题信息
        $extensionObj = new Extension;
        $extensionObj->initInfo('theme',$name);
        $info = $extensionObj->getInfoByFile();//从文件获取

        $config = ThemeLogic::getDefaultConfig($name);//获取文件中的默认配置值
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
        $extensionObj = new Extension;
        $extensionObj->initInfo('theme',$name);
        $info = $extensionObj->getInfoByFile();//从文件获取

        $config = ThemeLogic::getDefaultConfig($name);//获取文件中的默认配置值
        $info['config'] = !empty($config) ? json_encode($config) : '';

        $info['id'] = $id;
        //$data里包含主键id，则editData就会更新数据，否则是新增数据
        if ($this->themeModel->editData($info)) {
            $this->success('更新成功');
        } else {
            $this->error($this->themeModel->getError());
        }

    }

    /**
     * 切换主题(设置电脑和移动端)
     * @param  integer $id 主题ID
     * @param  integer $type 当前主题类型，1PC端，2手机端。默认0
     * @date   2018-03-03
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function setCurrent($id=0,$type=0) {
        try {
            $res_count = $this->themeModel->where('id',$id)->count();
            if (!$res_count) {
                throw new \Exception("主题不存在", 0);
                
            }
            // 当前主题current字段置为1
            $map = [
                'id'=>$id
            ];
            $res = $this->themeModel->where($map)->update(['current'=>$type]);
            if (!$res) {
                $this->error('设置当前主题失败',$this->themeModel->getError());
            }
            // 其它主题current字段置为0
            $map = [
                'id'      =>['neq', $id],
                'current' =>$type
            ];
            if ($this->themeModel->where($map)->count() > 0) {
                $res = $this->themeModel->where($map)->update(['current'=>0]);
                if (!$res) {
                    throw new \Exception("设置当前主题失败".$this->themeModel->getError(), 0);
                    
                } 
            }
            
        } catch (\Exception $e) {
            $this->error($e);
        }

        $this->success('前台主题设置成功！');
        
    }

    /**
     * 取消主题
     */
    public function cancel() {
        $map = [
            'current' => ['in', '1,2']
        ];
        $this->themeModel->where($map)->update(['current'=>0]);
        if ($this->themeModel->where($map)->count() == 0) {
            $this->success('重置主题成功！');
        } else {
            $this->error('重置主题失败', $this->themeModel->getError());
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
                'paged'=>$paged,
                'eacoophp_version'=>EACOOPHP_V
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
