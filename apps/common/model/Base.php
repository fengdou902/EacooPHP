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
        } else{
            $res=$this->data($data)->save();
        }
        return $res;
    }

    /**
     * 设置搜索
     * @param  [type] $fields 字段名（多个字段用|分开）
     * @param  string $rule 匹配规则
     * @return [type] [description]
     * @date   2018-02-06
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function search($fields='title',$rule='%[KEYWORD]%')
    {

        if (strpos($rule, '[KEYWORD]')!==false) {
            $keyword     = input('param.keyword',false);//关键字
            if (!empty($keyword)) {
                $rule = str_replace('[KEYWORD]', $keyword, $rule);
                $this->where($fields,'like',$rule);
            }
        }
        return $this;
        
    }

    /**
     * @param  array $map 查询过滤
     * @param  integer $page 分页值
     * @param  string $order 排序参数
     * @param  string $field 结果字段
     * @param  integer $page_size 每页数量
     * @return 结果集
     */
    public function getListByPage($map,$field=true,$order='sort asc',$page_size=null)
    {
        $paged     = input('param.paged',1);//分页值
        if (!$page_size) {
            $page_size = config('admin_page_size');
        }
        $page_size = input('param.page_size',$page_size);//每页数量
        $order     = input('param.order',$order);
        $list      = $this->where($map)->field($field)->order($order)->page($paged,$page_size)->select();
        $total     = $this->where($map)->count();
        return [$list,$total];
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

}