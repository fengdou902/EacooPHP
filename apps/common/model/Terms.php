<?php
// 分类模型       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\model;

class Terms extends Base {

    protected $insert = ['status' => 1];
    /**
     * 获取父分类名称
     * @param  [type] $value [description]
     * @param  [type] $data  [description]
     * @return [type]        [description]
     */
    public function getParentAttr($value,$data)
    {
        return $this->where(['term_id'=>(int) $data['pid']])->value('name');
    }
    
    /**
     * 获取文章数
     * @param  [type] $term_id [description]
     * @param  string $table   [description]
     * @return [type]          [description]
     */
    public static function termRelationCount($term_id,$table='posts'){
        if ($term_id) {
            $map['term_id'] = $term_id;
            $map['table']   = $table;
            return db('term_relationships')->where($map)->count();
        }

    }

}