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
    protected $conditions = [];//查询条件

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
     * 编辑行
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
     * 设置搜索，兼容高级查询
     * @param  [type] $condition 字段名（多个字段用|分开）
     * @param  string $rule 匹配规则
     * @return [type] [description]
     * @date   2018-02-06
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function search($setting = [])
    {
        $params = input();
        //是否来源插件url
        if (input('?param._plugin')) {
            unset($params['_plugin']);
            unset($params['_controller']);
            unset($params['_action']);
        }
        unset($params['paged']);
        unset($params['order']);
        unset($params['sort_name']);
        unset($params['page_size']);
        unset($params['_']);
        $rule='%[KEYWORD]%';
        if (is_array($setting)) {
            if (strpos($rule, '[KEYWORD]')!==false) {
                
                if (isset($params['keyword']) && !empty($params['keyword'])) {
                    $keyword = $params['keyword'];
                    $rule = str_replace('[KEYWORD]', $keyword, $rule);
                    $keyword_condition = isset($setting['keyword_condition']) ? $setting['keyword_condition']:'title';
                    $this->conditions[$keyword_condition] = ['like',$rule];
                }
            }
            unset($setting['keyword_condition']);
            
            if (!empty($setting)) {
                //忽略请求参数中的部分keys，通常是数据库中不存在的字段
                if (isset($setting['ignore_keys']) && is_array($setting['ignore_keys'])) {
                    foreach ($setting['ignore_keys'] as $key => $igkey) {
                        unset($params[$igkey]);
                    }
                }

                //扩展的请求参数值
                if (isset($setting['extend_conditions']) && is_array($setting['extend_conditions']) && !empty($setting['extend_conditions'])) {
                    $params = array_merge($params,$setting['extend_conditions']);
                }
            }
            
        } elseif(is_string($setting)){
            if (strpos($rule, '[KEYWORD]')!==false) {
                
                if (isset($params['keyword'])) {
                    $keyword = $params['keyword'];
                    $rule = str_replace('[KEYWORD]', $keyword, $rule);
                    $keyword_condition = !empty($setting) ? $setting:'title';
                    $this->conditions[$keyword_condition] = ['like',$rule];
                }
            }
            
        }
        unset($params['keyword']);
        
        $params = array_filter($params,function($val){
            if ($val!=='') {
                return true;
            }
            return false;
        });
        $this->conditions = array_merge($this->conditions,$params);
        
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
    public function getListByPage($condition = [], $fields = true, $order='', $page_size = null,$cache = false)
    {
        if (!empty($condition)) {
            $this->conditions = array_merge($condition,$this->conditions);
        }

        $paged     = input('param.paged',1);//分页值
        if (!$page_size) {
            $page_size = config('admin_page_size');
        }
        $page_size = input('param.page_size',$page_size);//每页数量
        $order     = input('param.order',$order);
        if ($cache) {
            $this->cache(true);
        }
        
        $data_list  = $this->where($this->conditions)->field($fields)->order($order)->page($paged,$page_size)->select();
        $total = $this->where($this->conditions)->count();

        return [$data_list,$total];
    }

    /**
     * @param  array $condition 查询过滤(数组或闭包)
     * @param  string $field 获取的字段
     * @param  string $order 排序
     * @return 结果集
     */
    public function getList($condition, $fields = true, $order='create_time desc', $cache = false)
    {
        $this->conditions = array_merge($condition,$this->conditions);
        if ($cache) {
            $this->cache(true);
        }
        $lists = $this->where($this->conditions)->field($fields)->order($order)->select();
        return $lists;
    }

}