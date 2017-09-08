<?php
namespace app\common\widget;
use app\common\controller\Base;

class UserRank extends Base{
    public function render($uid){
        $user=query_user(array('rank_link'),$uid);
        $this->assign('rank_link',$user['rank_link']);
        $this->display(T('Application://Common@Widget/userrank'));
    }
}