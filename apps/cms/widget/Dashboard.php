<?php
namespace app\cms\widget;
use app\common\controller\Base;
use app\cms\model\Posts;

/**
 * Class DashboardWidget
 * @package Cms\Widget
 * @author:赵俊峰 981248356@qq.com
 */
class Dashboard extends Base
{
    /**
     * cmstool  CMS工具条
     * @author:心云间、凝听 <981248356@qq.com>
     */
    public function tool()
    {
        //概括
        $generalize = [
            'usercount'=>db('Users')->where(array('uid'=>array('gt',0)))->count('uid'),//用户数
            'postcount'=>Posts::where('type','post')->count(),
            'pagecount'=>Posts::where('type','page')->count(),
            'commentcount'=>10,
        ];
            
        $this->assign('generalize', $generalize);    
        return $this->fetch('cms@adminWidget/tool');
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
        $paged=I('get.p/d',1);
        list($post_list,$totalCount) = model('Cms/posts')->getListByPage($map,$paged,'create_time desc','id,title,create_time',6);
        $this->assign('post_list', $post_list);    
        $this->fetch('cms@adminWidget/latestList');
    }

}