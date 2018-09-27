<?php
// 仪表盘
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
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
        //获取安装信息
        $product_info = logic('index')->getInstallAccreditInfo();
        $server_info = [
                '产品型号'    =>$product_info.'<a class="btn btn-xs btn-default ajax-get f15 ml-10" href="'.url('admin/index/refreshAccreditInfo').'"><i class="fa fa-refresh"></i></a>',
                '编译版本'    => '<span class="text-warning">'.BUILD_VERSION.'</span>',
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
        //获取官网新闻
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
            'access_token'=>$this->request->param('access_token')
        ];
        $result = EacooAccredit::getEacooNews($data);
        return $result;
    }
}
