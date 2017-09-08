<?php
namespace app\common\widget;

use app\common\controller\Base;

class Editor extends Base
{
    //百度编辑器
    public function ueditor($attributes = [],$param='',$style='')
    {
        $id      = isset($attributes['id'])? $attributes['id']:'myeditor';
        $name    = isset($attributes['name']) ? $attributes['name']:'content';
        $default = isset($attributes['default']) ? $attributes['default']:'';
        $width   = isset($attributes['width'])||empty($attributes['width']) ? $attributes['width'] : '100%';
        $height  = isset($attributes['height'])||empty($attributes['height']) ? $attributes['height'] : '300px';
        $config  = isset($attributes['config'])||empty($attributes['config']) ? $attributes['config'] : 'simple';

        $this->assign('id',$id);
        $this->assign('name',$name);
        $this->assign('default',$default);

        $this->assign('width',$width);
        $this->assign('height',$height);
        $this->assign('style',$style);
        if($config=='')
        {
            $config="toolbars:[['source','|','bold','italic','underline','fontsize','forecolor','fontfamily','backcolor','|','insertimage','insertcode','link','emotion','scrawl','wordimage']]";
        } elseif ($config == 'all'){
            $config='';
        }
        empty($param['zIndex']) && $param['zIndex'] = 977;
        $config.=(empty($config)?'':',').'zIndex:'.$param['zIndex'];
        is_bool(strpos($width,'%')) && $config.=',initialFrameWidth:'.str_replace('px','',$width);
        is_bool(strpos($height,'%')) && $config.=',initialFrameHeight:'.str_replace('px','',$height);
        $config.=',autoHeightEnabled: false';

        $param['is_load_script']=0;
        $this->assign('config',$config);
        $this->assign('param',$param);
        //cookie('video_get_info',U('Core/Public/getVideo'));

        return $this->fetch('common@widget/ueditor');
    }

    /**
     * wangeditor编辑器
     * @param  string $id      id标示
     * @param  string $name    name值
     * @param  string $default 默认值
     * @param  string $width   宽度
     * @param  string $height  高度
     * @param  string $config  配置
     * @param  array  $param   [description]
     * @param  string $style   [description]
     * @return [type]          [description]
     */
    public function wangeditor($attributes = [],$param=['importWangEditor'=>1,'open_attachmentModal'=>'multiple'],$style='')
    {
        $id      = isset($attributes['id'])? $attributes['id']:'myeditor';
        $name    = isset($attributes['name']) ? $attributes['name']:'content';
        $default = isset($attributes['default']) ? $attributes['default']:'';
        $width   = isset($attributes['width']) ? $attributes['width'] : '100%';
        $height  = isset($attributes['height']) ? $attributes['height'] : '300px';
        $config  = isset($attributes['config']) ? $attributes['config'] : 'simple';

        if (!$param||$param=='') {
            $param = array('importWangEditor'=>1,'open_attachmentModal'=>'multiple');
        }
        $this->assign('id',$id);
        $this->assign('name',$name);
        $this->assign('default',$default);

        $this->assign('width',$width);
        $this->assign('height',$height);
        $this->assign('style',$style);
        if(MODULE_MARK=='admin' && in_array($param['open_attachmentModal'],['single','multiple'])) {
            $this->assign('field',$attributes);
            $admin_more = ",'attachmentModal'";
        } else{
            $admin_more = "";
        }
        switch ($config) {
            case 'all':
                $menu="'source','|','bold','indent','underline','italic','strikethrough','eraser','forecolor','bgcolor','|','alignleft','aligncenter','alignright','|','quote','fontfamily','fontsize','head','lineheight','unorderlist','orderlist','|','link','unlink','table','emotion','symbol','|','img'{$admin_more},'video','location','insertcode','|','undo','redo','fullscreen'";
                break;
            case 'home_publish':
                $menu="'bold','underline','italic','strikethrough','eraser','forecolor','bgcolor','alignleft','aligncenter','alignright','quote','fontfamily','fontsize','unorderlist','orderlist','img','video','emotion','undo','redo','fullscreen'";
                break;
            case 'topic_publish':
                $menu="'bold','underline','italic','strikethrough','eraser','forecolor','bgcolor','alignleft','aligncenter','alignright','quote','fontfamily','fontsize','unorderlist','orderlist','img','emotion','undo','redo','fullscreen'";
                break;
            case 'simple':
                $menu="'source','|','bold','underline','italic','strikethrough','eraser','forecolor','bgcolor','emotion'";
                break;
            default:
                $menu="'source','|','bold','underline','italic','strikethrough','eraser','forecolor','bgcolor','img'";
                break;
        }
        
        $this->assign('config',$config);
        $this->assign('menus',$menu);
        $this->assign('param',$param);
        //cookie('video_get_info',U('Core/Public/getVideo'));

        return $this->fetch('common@widget/wangeditor');
    }

}
