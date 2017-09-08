<?php
// 分类模型       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use app\common\model\Base;

class Terms extends Base {
    protected $name = 'terms';
    // protected $_validate =array(
    //     array('name','require','分类名称必填！',1,regex,3),//默认情况下用正则验证
    //     array('taxonomy','require','分类类型必填！',1,regex,3),//默认情况下用正则验证
    // );
    
    // protected $_auto = array(
    //     array('slug', 'strtolower', self::MODEL_BOTH, 'function'),//别名转换为小写
    //     array('create_time',NOW_TIME, self::MODEL_INSERT),
    //     array('update_time', NOW_TIME, self::MODEL_BOTH), // 对update_time字段在更新的时候写入当前时间戳     );
    // );

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
    function term_relation_count($term_id,$table='posts'){
        if ($term_id) {
            $map['term_id']=$term_id;
            $map['table']=$table;
            return db('term_relationships')->where($map)->count();
        }

    }

}