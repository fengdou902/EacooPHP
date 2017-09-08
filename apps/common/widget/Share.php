<?php
namespace app\common\widget;
use app\common\controller\Base;

class Share extends Base{

    public function detailShare($data)
    {
        //支持参数“share_text”设置分享的文本内容
        $this->assign($data);
        $this->display(T('Application://Common@Widget/share'));
    }

} 