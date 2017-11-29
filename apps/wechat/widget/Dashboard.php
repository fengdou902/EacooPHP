<?php
namespace Sns\Widget;
use Think\Controller;

/**
 * Class DashboardWidget
 * @package Shop\Widget
 * @author:赵俊峰 981248356@qq.com
 */
class DashboardWidget extends Controller
{
    protected $workModel;
    protected $categoryModel;
    function _initialize()
    {
        $this->work_model = D('Sns/Work');
    }
    /**
     * cmstool  Shop工具条
     * @author:赵俊峰 981248356@qq.com
     */
    public function snstool()
    {
        $generalize=$info=array();
        //概括
        $generalize['usercount']=M('Users')->where(array('uid'=>array('gt',0)))->count('uid');//用户数
        $generalize['postcount']=M('sns_works')->where("type='%s'",'opus')->count('id');//文章数
        $generalize['pagecount']=M('sns_event')->count('id');//页面数
        $generalize['commentcount']=M('Comments')->count('id');//页面数
            
        $this->assign('generalize', $generalize);    
        $this->display(T('Sns@AdminWidget/Snstool'));
    }
    /**
     *   原创作品
     * @author:赵俊峰 981248356@qq.com
     */
    public function recent_opuss()
    {
        $map['status'] = array('egt', '0'); // 禁用和正常状态
        $map['type']='opus';
        $paged=I('get.p/d',1);
        list($data_list,$totalCount) =$this->work_model->getListByPage($map,$paged,'create_time desc','id,img,author,title,content,create_time,cat',4);
        foreach($data_list as $k=>$opus){
            $opus_category=M('sns_category')->where(array('id'=>$opus['cat']))->getField('name');
            $data_list[$k]['category_name']=$opus_category?:'暂无';//获得term名称
            $data_list[$k]['author_name']=get_user_info($opus['author'])['nickname'];//获取用户名
        }
        $this->assign('data_list', $data_list);    
        $this->display(T('Sns@AdminWidget/RecentOpuss'));
    }
    /**
     *   最近音频
     * @author:赵俊峰 981248356@qq.com
     */
    public function recent_audio()
    {
        $map['status'] = array('egt', '0'); // 禁用和正常状态
        $map['type']='audio';
        $paged=I('get.p/d',1);
        list($data_list,$totalCount) =$this->work_model->getListByPage($map,$paged,'create_time desc','id,img,author,title,content,create_time,cat',4);
        foreach($data_list as $k=>$opus){
            $opus_category=M('sns_category')->where(array('id'=>$opus['cat']))->getField('name');
            $data_list[$k]['category_name']=$opus_category?:'暂无';//获得term名称
            $data_list[$k]['author_name']=get_user_info($opus['author'])['nickname'];//获取用户名
        }
        $this->assign('data_list', $data_list);    
        $this->display(T('Sns@AdminWidget/RecentAudio'));
    }
    /**
     *   最近视频
     * @author:赵俊峰 981248356@qq.com
     */
    public function recent_video()
    {
        $map['status'] = array('egt', '0'); // 禁用和正常状态
        $map['type']='video';
        $paged=I('get.p/d',1);
        list($data_list,$totalCount) =$this->work_model->getListByPage($map,$paged,'create_time desc','id,img,author,title,content,create_time,cat',4);
        foreach($data_list as $k=>$opus){
            $opus_category=M('sns_category')->where(array('id'=>$opus['cat']))->getField('name');
            $data_list[$k]['category_name']=$opus_category?:'暂无';//获得term名称
            $data_list[$k]['author_name']=get_user_info($opus['author'])['nickname'];//获取用户名
        }
        $this->assign('data_list', $data_list);    
        $this->display(T('Sns@AdminWidget/RecentVideo'));
    }
    /**
     *   最近话题
     * @author:赵俊峰 981248356@qq.com
     */
    public function recent_topic()
    {
        $map['status'] = array('egt', '0'); // 禁用和正常状态
        $map['type']='topic';
        $paged=I('get.p/d',1);
        list($data_list,$totalCount) =$this->work_model->getListByPage($map,$paged,'create_time desc','id,img,author,title,content,create_time,cat',4);
        foreach($data_list as $k=>$opus){
            $opus_category=M('sns_category')->where(array('id'=>$opus['cat']))->getField('name');
            $data_list[$k]['category_name']=$opus_category?:'暂无';//获得term名称
            $data_list[$k]['author_name']=get_user_info($opus['author'])['nickname'];//获取用户名
        }
        $this->assign('data_list', $data_list);    
        $this->display(T('Sns@AdminWidget/RecentTopic'));
    }
    /**
     *   最近大赛
     * @author:赵俊峰 981248356@qq.com
     */
    public function recent_compete()
    {
        $map['status'] = array('egt', '0'); // 禁用和正常状态
        $map['type']='compete';
        $paged=I('get.p/d',1);
        list($data_list,$totalCount) =$this->work_model->getListByPage($map,$paged,'create_time desc','id,img,author,title,content,create_time,cat',4);
        foreach($data_list as $k=>$opus){
            $opus_category=M('sns_category')->where(array('id'=>$opus['cat']))->getField('name');
            $data_list[$k]['category_name']=$opus_category?:'暂无';//获得term名称
            $data_list[$k]['author_name']=get_user_info($opus['author'])['nickname'];//获取用户名
        }
        $this->assign('data_list', $data_list);     
        $this->display(T('Sns@AdminWidget/RecentCompete'));
    }
    /**
     *   最近活动
     * @author:赵俊峰 981248356@qq.com
     */
    public function recent_event()
    {
        $map['status'] = array('egt', '0'); // 禁用和正常状态
        $paged=I('get.p/d',1);
        list($data_list,$totalCount) =D('Sns/Event')->getListByPage($map,$paged,'create_time desc','id,cover,author,title,content,create_time,cat',4);
        foreach($data_list as $k=>$opus){
            $opus_category=M('sns_category')->where(array('id'=>$opus['cat']))->getField('name');
            $data_list[$k]['category_name']=$opus_category?:'暂无';//获得term名称
            $data_list[$k]['author_name']=get_user_info($opus['author'])['nickname'];//获取用户名
        }
        $this->assign('data_list', $data_list);     
        $this->display(T('Sns@AdminWidget/RecentEvent'));
    }
}