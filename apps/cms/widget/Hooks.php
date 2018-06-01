<?php
// 钩子控制器       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2016 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author: 心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------

namespace app\cms\widget;
use app\common\controller\Widget;
use app\cms\model\Posts as PostsModel;
use app\common\model\User as UserModel;

class Hooks extends Widget
{
    public function _initialize() {
        parent::_initialize();

    }

    /**
     * @var array 模块钩子
     */
    public $hooks = [
        'AdminIndex'
    ];

    /**
     * 后台仪表盘
     * @date   2018-04-05
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function AdminIndex()
    {
        $this->tool();
    }

    /**
     * cmstool  CMS工具条
     * @author:心云间、凝听 <981248356@qq.com>
     */
    public function tool()
    {
        //概括
        $generalize = [
            'usercount'    => UserModel::where(['uid'=>['gt',0]])->count('uid'),//用户数
            'postcount'    => PostsModel::where('type','post')->count(),
            'pagecount'    => PostsModel::where('type','page')->count(),
            'commentcount' => 10,
        ];
            
        $this->assign('generalize', $generalize);    
        return $this->fetch('tool');
    }

    /**
     *   Latest posts
     * @author:心云间、凝听 <981248356@qq.com>
     */
    public function latestList()
    {
        // 获取所有文章
        $map['status'] = array('egt', '0'); // 禁用和正常状态
        $map['type']='post';
        $paged=input('get.p');
        list($post_list,$totalCount) = model('cms/posts')->getListByPage($map,$paged,'create_time desc','id,title,create_time',6);
        $this->assign('post_list', $post_list);    
        $this->fetch('cms@widget/latestList');
    }

}