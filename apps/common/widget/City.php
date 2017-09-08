<?php

namespace app\common\widget;
use app\common\controller\Base;

/**城市选择组件
 * Class SubMenuWidget
 * @package Common\Widget
 * Date: 16-7-31 18：34
 * @author 赵俊峰<981248356@qq.com>
 */
class City extends Base
{
    public function render($field = array(),$importCity=1)
    {
        $this->assign('field',$field);
        $this->assign('importCity',$importCity);
        $this->display(T('Application://Common@Widget/city'));
    }
}