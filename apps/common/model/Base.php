<?php
// 模型基类
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
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
     * @param  string  $confirm  是否验证
     * @return [type]        执行结果
     */
    public function editData($data, $confirm=false)
    {
        $this->allowField(true);
        
        if ($confirm) {//是否验证
            $this->validate($confirm); 
        }
        //获取主键
        $pk = $this->getPk();
        if (isset($data[$pk]) && $data[$pk]>0) {
            //如果存在主键，则更新数据
            $res = $this->save($data,[$pk=>$data[$pk]]);
        } else{
            //如果不存在主键，则新增数据
            $res = $this->isUpdate(false)->data($data)->save();
        }
        if (!$res) {
            if (!$this->getError()) {
                $this->error = '数据操作失败！';
            }
        }
        return $res;
    }

    /**
     * 编辑列
     * @param  [type] $data [description]
     * @param  [type] $map [description]
     * @param  [type] $msg [description]
     * @return [type] [description]
     * @date   2018-02-28
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function editRow($data, $map) {
        $ids = array_unique((array)input('param.ids/a'));
        if ($ids) {
            $ids = is_array($ids) ? implode(',',$ids) : $ids;
            //如存在id字段，则加入该条件
            $pk = $this->getPk();
            if (!empty($ids)) {
                $map = array_merge(
                    [$pk => ['in', $ids]],
                    (array)$map
                );
            }
        }
        $result = $this->where($map)->update($data);
        if (!$result) {
            if (!$this->getError()) {
                $this->error = '数据操作失败！';
            }
        }
        return $result;
    }

    /**
     * 设置搜索
     * @param  [type] $condition 字段名（多个字段用|分开）
     * @param  string $rule 匹配规则
     * @return [type] [description]
     * @date   2018-02-06
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function search($condition='title',$rule='%[KEYWORD]%')
    {
        if (is_string($condition)) {
            if (strpos($rule, '[KEYWORD]')!==false) {
                $keyword     = input('param.keyword',false);//关键字
                if (!empty($keyword)) {
                    $rule = str_replace('[KEYWORD]', $keyword, $rule);
                    $this->where($condition,'like',$rule);
                }
            }
        } elseif ($condition instanceof \Closure) {
            call_user_func_array($condition, [ & $this]);
        }
        
        return $this;
        
    }

    /**
     * 获取分页列表数据
     * @param  array $condition 查询过滤条件(数组或闭包)
     * @param  string $fields 结果字段（多个字段用逗号隔开）
     * @param  integer $page 分页值
     * @param  string $order 排序参数
     * @param  integer $page_size 每页数量
     * @param  integer $cache 是否启用缓存
     * @return 结果集
     */
    public function getListByPage($condition, $fields = true, $order='', $page_size = null,$cache = false)
    {
        $paged     = input('param.paged',1);//分页值
        if (!$page_size) {
            $page_size = config('admin_page_size');
        }
        $page_size = input('param.page_size',$page_size);//每页数量
        $order     = input('param.order',$order);
        if ($cache) {
            $this->cache(true);
        }
        $list      = $this->where($condition)->field($fields)->order($order)->page($paged,$page_size)->select();
        $total     = $this->where($condition)->count();
        
        return [$list,$total];
    }

    /**
     * @param  array $condition 查询过滤(数组或闭包)
     * @param  string $field 获取的字段
     * @param  string $order 排序
     * @return 结果集
     */
    public function getList($condition, $fields = true, $order='create_time desc', $cache = false)
    {
        if ($cache) {
            $this->cache(true);
        }
        $lists = $this->where($condition)->field($fields)->order($order)->select();
        return $lists;
    }

}