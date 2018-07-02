<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\user\admin;
use app\admin\controller\Admin;

use app\common\model\User;

use think\Db;

class Tongji extends Admin {
    public $begin;
    public $end;

    function _initialize()
    {
        parent::_initialize();
        $timegap = input('timegap');
        if($timegap){
            $gap = explode('—', $timegap);
            $begin = $gap[0];
            $end = $gap[1];
        } else {
            $lastweek = date('Y-m-d',strtotime("-1 month"));//30天前
            $begin = input('begin',$lastweek);
            $end =  input('end',date('Y-m-d'));
        }
        $this->begin = strtotime($begin);
        $this->end = strtotime($end)+86399;
        $this->assign('timegap',date('Y-m-d',$this->begin).'—'.date('Y-m-d',$this->end));
    }

    //会员统计分析
    public function analyze(){
        $this->assign('meta_title','会员统计');

        $today = strtotime(date('Y-m-d'));
        $month = strtotime(date('Y-m-01'));

        $user = [
            'today'      => User::where("reg_time>$today")->count(),//今日新增会员
            'month'      => User::where("reg_time>$month")->count(),//本月新增会员
            'total'      => User::count(),//会员总数
            'user_money' => User::sum('money'),//会员余额总额
            'hasorder'   => 36,
        ];
        $this->assign('user',$user);

        $db_prefix = config('database.prefix');//数据库表前缀
        $sql = "SELECT COUNT(*) as num,FROM_UNIXTIME(reg_time,'%Y-%m-%d') as gap from {$db_prefix}users where reg_time>$this->begin and reg_time<$this->end group by gap";
        $new = Db::query($sql);//新增会员趋势        
        foreach ($new as $val){
            $arr[$val['gap']] = $val['num'];
        }
        
        for($i=$this->begin;$i<=$this->end;$i=$i+24*3600){
            $brr[] = empty($arr[date('Y-m-d',$i)]) ? 0 : $arr[date('Y-m-d',$i)];
            $day[] = date('Y-m-d',$i);
        }       
        $result = array('data'=>$brr,'time'=>$day);
        $this->assign('result',json_encode($result));
        return $this->fetch();
    }
    

}