<?php
namespace app\common\widget;

use app\common\controller\Base;

class Editor extends Base
{
    /**
     * 百度编辑器
     * @param  array $attributes [description]
     * @param  string $param [description]
     * @param  string $style [description]
     * @return [type] [description]
     * @date   2017-09-11
     * @author 心云间、凝听 <981248356@qq.com>
     */
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
     * @param  array $attributes 属性
     * @param  array $param 额外属性
     * @param  string $style 样式
     * @return [type] [description]
     * @date   2017-09-11
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function wangeditor($attributes = [],$param=[],$style='')
    {
        $id      = isset($attributes['id'])? $attributes['id']:'myeditor';//ID标识
        $name    = isset($attributes['name']) ? $attributes['name']:'content';//name值
        $default = isset($attributes['default']) ? $attributes['default']:'';//默认值
        $width   = isset($attributes['width']) ? $attributes['width'] : '100%';//宽度
        $height  = isset($attributes['height']) ? $attributes['height'] : '300px';//高度
        $config  = isset($attributes['config']) ? $attributes['config'] : 'simple';//菜单配置

        if (!$param || $param=='') {
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
