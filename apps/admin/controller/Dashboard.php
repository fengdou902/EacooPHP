<?php
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2016 http://www.eacoomall.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author: 心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use think\Db;
use com\EacooAccredit;
class Dashboard extends Admin
{
    function _initialize()
    {
        parent::_initialize();
    }
    /**
     * 仪表盘
     */
    public function index() {
        $this->assign('meta_title','仪表盘');
        $this->assign('hide_panel',true);
        
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
