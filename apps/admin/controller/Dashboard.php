<?php
// 仪表盘
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
use eacoo\EacooAccredit;

class Dashboard extends Admin
{
    function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 仪表盘
     * @return [type] [description]
     * @date   2018-01-11
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function index() {
        $this->assign('meta_title','仪表盘');
        $this->assign('page_config',['disable_panel'=>true]);
        
        //系统信息
        $mysql   = Db::query("select VERSION() as version");
        $mysql_v = $mysql[0]['version'];
        $mysql_v = empty($mysql_v) ? '未知' : $mysql_v;

        $install_lock = json_decode(file_get_contents(APP_PATH . 'install.lock'),true);
        if (!isset($install_lock['status_show_text']) || !isset($install_lock['accredit_status'])) {
            EacooAccredit::execute();
            $install_lock = json_decode(file_get_contents(APP_PATH . 'install.lock'),true);
        }
        $product_info = $install_lock['status_show_text'];
        $server_info = [
                '产品型号'    =>$product_info,
                '操作系统'    => PHP_OS,
                '运行环境'    => $_SERVER["SERVER_SOFTWARE"],
                'PHP运行方式' => php_sapi_name(),
                'PHP版本'   => PHP_VERSION,
                'MYSQL版本' =>$mysql_v,
                '上传附件限制'  => ini_get('upload_max_filesize'),
                '执行时间限制'  => ini_get('max_execution_time') . "s",
                '剩余空间'    => format_file_size(@disk_free_space("."))//round((@disk_free_space(".") / (1024 * 1024)), 2) . 'M',
        ];
        $this->assign('server_info', $server_info);

        $user_list = db('users')->where(['status'=>1])->field('uid,username,nickname,avatar,reg_time')->limit(12)->order('reg_time desc')->select();
        $this->assign('user_list',$user_list);
        //用户数据分析
        $result = [
            'data'=>[10,26,21,35,43,45,50],
            'time'=>[
                date("m-d",strtotime("-7 day")),
                date("m-d",strtotime("-6 day")),
                date("m-d",strtotime("-5 day")),
                date("m-d",strtotime("-4 day")),
                date("m-d",strtotime("-3 day")),
                date("m-d",strtotime("-2 day")),
                date("m-d",strtotime("-1 day")),
                ]
            ];
        $this->assign('user_result',json_encode($result));
        $this->assign('eacoo_news_list',$this->getEacooNews());
        return $this->fetch();
    }

    /**
     * 获取官方动态
     * @param  string $value [description]
     * @return [type] [description]
     * @date   2017-09-06
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function getEacooNews()
    {
        $data = [
            'access_token'=>input('param.access_token')
        ];
        $result = EacooAccredit::getEacooNews($data);
        return $result;
    }
}
