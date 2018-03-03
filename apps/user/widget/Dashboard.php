<?php
namespace app\user\widget;
use think\controller;

/**
 * Class DashboardWidget
 * @package User\Widget
 * @author:赵俊峰 981248356@qq.com
 */
class Dashboard extends controller
{
    /**
     * Latest Members
     * @author:赵俊峰 981248356@qq.com
     */
    public function latestMembers()
    {   
        $map['status']=1;
        // 获取最近8个用户
        $member_list = model('user/User')->where($map)->order('create_time desc')->limit(10)->select();
        $totalCount  = model('user/User')->where($map)->limit(10)->count();
        $this->assign('member_list', $member_list);
        $this->assign('latestmember_total', $totalCount);    
        return $this->fetch('user@widget/LatestMembers');
    }
    
    /**
     * Latest Members
     * @author:赵俊峰 981248356@qq.com
     */
    public function recentMembers_lineChart()
    {
        // 获取所有文章
        $map['status'] = ['egt', '0']; // 禁用和正常状态
        $map['type']   ='post';
        list($post_list,$totalCount) = model('cms/posts')->getListByPage($map,'create_time desc','id,title,create_time',5);
        $this->assign('post_list', $post_list);    
        return $this->fetch('user@widget/RecentMembersLineChart');
    }
}