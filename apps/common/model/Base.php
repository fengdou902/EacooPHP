<?php
// 模型基类
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\model;

use think\Model;
use think\Request;

class Base extends Model
{
    /**
     * 新增或编辑数据
     * @param  array/object  $data 来源数据
     * @param  boolean $kv   主键值
     * @param  string  $key  主键名
     * @return [type]        执行结果
     */
    public function editData($data,$kv=false,$key='id',$confirm=false)
    {
        $this->allowField(true);
        
        if ($confirm) {//是否验证
            $this->validate($confirm); 
        }

        if($kv){//编辑
            $res=$this->save($data,[$key=>$kv]);
        }else{
            $res=$this->data($data)->save();
        }
        return $res;
    }

    /**
     * @param  array $map 查询过滤
     * @param  integer $page 分页值
     * @param  string $order 排序参数
     * @param  string $field 结果字段
     * @param  integer $page_number 每页数量
     * @return 结果集
     */
    public function getListByPage($map,$order='sort asc,update_time desc',$field=true,$page_number=20)
    {
        $list=$this->where($map)->order($order)->field($field)->paginate($page_number);
        $page=$list->render();
        return array($list,$page);
    }

    /**
     * @param  array $map 查询过滤
     * @param  string $field 获取的字段
     * @param  string $order 排序
     * @return 结果集
     */
    public function getList($map,$field=true,$order='sort asc')
    {
        $lists = $this->where($map)->field($field)->order($order)->select();
        return $lists;
    }
    
    /**
     * 通过$map获取列表
     * @param array $map 查询条件
     * @param $order 排序
     * @param null $fields 查询字段，true表示全部字段
     * @return mixed 结果列表
     */
    
    public function selectByMap($map=[],$order=null,$fields=true){
        $order = $order ? $order : "id asc";
        $list=$this->where($map)->order($order)->field($fields)->select();
        return $list;
    }

    /**
     * * 通过$map获取单条值
     * @param array $map 查询条件
     * @param string $order 排序
     * @param null $fields 查询字段，true表示全部字段
     * @return mixed 结果
     */
    public function getByMap($map=[],$order,$fields=true){
        $order=$order?$order:"id asc";
        $data=$this->where($map)->order($order)->field($fields)->find();
        
        return $data;
    }
}