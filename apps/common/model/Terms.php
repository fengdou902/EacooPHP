<?php
// 分类模型       
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\model;

class Terms extends Base {
    protected $resultSetType = 'collection';
    /**
     * 获取父分类名称
     * @param  [type] $value [description]
     * @param  [type] $data  [description]
     * @return [type]        [description]
     */
    public function getParentAttr($value,$data)
    {
        $name = $this->where(['term_id'=>$data['pid']])->value('name');
        return $name ? :'顶级分类';
    }

}