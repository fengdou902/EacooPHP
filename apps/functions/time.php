<?php
// 时间处理
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
/**
 * 友好的时间显示
 *
 * @param int    $sTime 待显示的时间
 * @param string $type  类型. normal | mohu | full | ymd | other
 * @param string $alt   已失效
 * @return string
 */
function friendly_date($sTime,$type = 'normal',$alt = 'false') {
    if (!$sTime)
        return '';
    //sTime=源时间，cTime=当前时间，dTime=时间差
    $cTime      =   time();
    $dTime      =   $cTime-$sTime;
    $dDay       =   intval(date("z",$cTime)) - intval(date("z",$sTime));
    //$dDay     =   intval($dTime/3600/24);
    $dYear      =   intval(date("Y",$cTime)) - intval(date("Y",$sTime));
    //normal：n秒前，n分钟前，n小时前，日期
    if($type=='normal'){
        if( $dTime < 60 ){
            if($dTime < 10){
                return '刚刚';    //by yangjs
            }else{
                return intval(floor($dTime / 10) * 10).'秒前';
            }
        } elseif( $dTime < 3600 ){
            return intval($dTime/60).'分钟前';
            //今天的数据.年份相同.日期相同.
        } elseif( $dYear==0 && $dDay == 0  ){
            //return intval($dTime/3600).L('_HOURS_AGO_');
            return '今天'.date('H:i',$sTime);
        } elseif( $dDay > 0 && $dDay<=3 ){
            return intval($dDay).'天前';
        } elseif($dYear==0){
            return date("m月d日 H:i",$sTime);
        } else{
            return date("Y-m-d H:i",$sTime);
        }
    } elseif($type=='mohu'){
        if( $dTime < 60 ){
            return $dTime.'秒前';
        } elseif( $dTime < 3600 ){
            return intval($dTime/60).'分钟前';
        } elseif( $dTime >= 3600 && $dDay == 0  ){
            return intval($dTime/3600).'小时前';
        } elseif( $dDay > 0 && $dDay<=7 ){
            return intval($dDay).'天前';
        } elseif( $dDay > 7 &&  $dDay <= 30 ){
            return intval($dDay/7) . '周前';
        } elseif( $dDay > 30 ){
            return intval($dDay/30) .'个月前';
        } else{
           return date("Y-m-d H:i",$sTime); 
        }
        //full: Y-m-d , H:i:s
    } elseif($type=='full'){
        return date("Y-m-d , H:i:s",$sTime);
    } elseif($type=='ymd'){
        return date("Y-m-d",$sTime);
    } else{
        if( $dTime < 60 ){
            return $dTime.'秒前';
        } elseif( $dTime < 3600 ){
            return intval($dTime/60).'分钟前';
        } elseif( $dTime >= 3600 && $dDay == 0  ){
            return intval($dTime/3600).'小时前';
        } elseif($dYear==0){
            return date("Y-m-d H:i:s",$sTime);
        } else{
            return date("Y-m-d H:i:s",$sTime);
        }
    }
}

/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 * @author huajie <banhuajie@163.com>
 */
function time_format($time = NULL, $format = 'Y-m-d H:i') {
    $time = $time === NULL ? time() : intval($time);
    return date($format, $time);
}

/**
 * [getUserAge 获取用户年龄]
 * @param string $birthday    生日时间  例如：1987年11月09日 ||  1987-11-09
 * @param string $type        时间显示  说说： 0 1987-11-09  1 1987年11月09日
 * @return [type]           [description]
 */
function getUserAge($birthday) {
    $str = substr($birthday,0,4);               //出生日期
    $year = date('Y',time());                   //本年  
    return $age = $year - $str;                 //个人年龄
}

//转换剩余时间格式
function gettime($time){
    if ($time < 0) {  
        return '已结束';  
    } else {  
        if ($time < 60) {  
            return $time . '秒';  
        } else {  
            if ($time < 3600) {  
                return floor($time / 60) . '分钟';  
            } else {  
                if ($time < 86400) {  
                    return floor($time / 3600) . '小时';  
                } else {  
                    if ($time < 259200) {//3天内  
                        return floor($time / 86400) . '天';  
                    } else {  
                        return floor($time / 86400) . '天';  
                    }  
                }  
            }  
        }  
    }  
}

/**
 * 判断是否日期时间
 * @return string
 */
function check_date_time($str_time, $format="Y-m-d H:i:s") {
    $unix_time = strtotime($str_time);
    $check_date= date($format, $unix_time);
    if ($check_date == $str_time) {
        return true;
    } else {
        return false;
    }
}

/**
 * get_some_day  获取n天前0点的时间戳
 * @param int $some n天
 * @param null $day 当前时间
 * @return int|null
 * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
 */
function get_some_day($some = 30, $day = null)
{
    $time = $day ? $day : time();
    $some_day = $time - 60 * 60 * 24 * $some;
    $btime = date('Y-m-d' . ' 00:00:00', $some_day);
    $some_day = strtotime($btime);
    return $some_day;
}