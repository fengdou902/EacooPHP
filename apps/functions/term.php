<?php 
// 分类函数
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
/**
 * 获取不同类型分类
 * @param  array $taxonomies 分类法
 * @param  array $args 自定义显示
 * @return array 返回结果数组
 * @author 
 */
function get_terms($taxonomies,$field=true){

}

/**
 * 通过对象ID获取分类ID
 * @param  int $object_id 对象ID
 * @param  string $field 字段
 * @param  string $table 表
 * @return array/string
 * @author 心云间、凝听 <981248356@qq.com>
 */
function get_the_category($object_id,$table='posts'){
    $map['object_id'] = $object_id;
    $map['table']     = $table;
	return db('term_relationships')->where($map)->value('term_id');
}

/**
 * 获取分类信息
 * @param  int $object_id 对象ID
 * @param  string $field 字段
 * @param  string $table 表
 * @return array/string
 * @author 心云间、凝听 <981248356@qq.com>
 */
function get_term_info($object_id,$field=true,$table='posts'){
    $rela_map['object_id'] = $object_id;
    $rela_map['table']     = $table;
    $term_id               = db('term_relationships')->where($rela_map)->value('term_id');
    $info                  = db('terms')->where('term_id',$term_id)->field($field)->find();
	return $info;
}

//获取多个分类信息
function get_terms_info($object_id,$table='posts'){
    $map['object_id'] = $object_id;
    $map['table']     = $table;
	return db('term_relationships')->where($map)->value('term_id');
}

/**
 * 获取参数的所有父级分类
 * @param int $cid 分类id
 * @return array 参数分类和父类的信息集合
 */
function get_parent_category($cid)
{
    if (empty($cid)) {
        return false;
    }
    $cates = db('terms')->where(array('status' => 1))->field('id,title,pid')->order('sort')->select();
    $child = get_category($cid);    //获取参数分类的信息
    $pid = $child['pid'];
    $temp = [];
    $res[] = $child;
    while (true) {
        foreach ($cates as $key => $cate) {
            if ($cate['id'] == $pid) {
                $pid = $cate['pid'];
                array_unshift($res, $cate);    //将父分类插入到数组第一个元素前
            }
        }
        if ($pid == 0) {
            break;
        }
    }
    return $res;
}

//更新或添加分类对象
function update_object_term($object_id,$term_id,$table='posts'){
    if ($object_id && $term_id) {
            $data['object_id']  = $object_id;
            $data['term_id']    = $term_id;
            $data['table']      = $table;
            $term_relationships = db('term_relationships');
            $info = $term_relationships->where('object_id='.$object_id)->find();
            if ($info) {
                $data['id']=$info['id'];
                $term_relationships->update($data);
            }else{
                $term_relationships->insert($data);
            }
            
    }
}
//删除分类对象
function delete_object_term($object_id,$term_id,$table){
    if ($object_id&&$term_id&&$table) {
            $map['object_id'] = $object_id;
            $map['term_id']   = $term_id;
            $map['table']     = $table;
            $res              = db('term_relationships')->where($map)->delete();     
    }
}