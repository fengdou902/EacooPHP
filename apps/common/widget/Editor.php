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
    public function ueditor($attributes = [])
    {
        if (!isset($attributes['id'])) $attributes['id']='myeditor';//ID标识
        if (!isset($attributes['name'])) $attributes['name']='content';//name值
        if (!isset($attributes['width'])) $attributes['width']='100%';//name值
        if (!isset($attributes['height'])) $attributes['height']='300px';//name值
        if (!isset($attributes['is_load_script'])) $attributes['is_load_script']=1;//是否加载脚本

        if (!isset($attributes['menus'])) {
            $attributes['menus']="toolbars:[['source','|','bold','italic','underline','fontsize','forecolor','fontfamily','backcolor','|','insertimage','insertcode','link','emotion','scrawl','wordimage']]";
        }
        $menus = $attributes['menus'];//菜单
        $width = $attributes['width'];
        $height = $attributes['height'];
        $zIndex  = isset($attributes['zIndex'])||!empty($attributes['zIndex']) ? $attributes['zIndex'] : '977';
        
        //$config.=(empty($menus) ? '' : ',').'zIndex:'.$zIndex;
        is_bool(strpos($width,'%')) && $menus.=',initialFrameWidth:'.str_replace('px','',$width);
        is_bool(strpos($height,'%')) && $menus.=',initialFrameHeight:'.str_replace('px','',$height);
        $menus.=',autoHeightEnabled: false';

        $this->assign('menus',$menus);
        /**
         * 字段：id,name,style,width,height,
         */
        $this->assign($attributes);

        return $this->fetch('common@widget/ueditor');
    }

    /**
     * wangeditor编辑器
     * @param  array $attributes 属性
     * @param  string $style 样式
     * @return [type] [description]
     * @date   2017-09-11
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function wangeditor($attributes = [])
    {
        if (!isset($attributes['id'])) $attributes['id']='myeditor';//ID标识
        if (!isset($attributes['name'])) $attributes['name']='content';//name值
        if (!isset($attributes['is_load_script'])) $attributes['is_load_script']=1;//是否加载脚本
        if (!isset($attributes['picturesModal'])) $attributes['picturesModal']=1;//是否显示多图按钮

        if (!isset($attributes['menus'])) {
            $attributes['menus']="'head', // 标题
                    'bold',  // 粗体
                    'italic',  // 斜体
                    'underline',  // 下划线
                    'strikeThrough',  // 删除线
                    'foreColor',  // 文字颜色
                    'backColor',  // 背景颜色
                    'link',  // 插入链接
                    'list',  // 列表
                    'justify',  // 对齐方式
                    'quote',  // 引用
                    'emoticon',  // 表情
                    'image',  // 插入图片
                    'table',  // 表格
                    'video',  // 插入视频
                    'code',  // 插入代码
                    'undo',  // 撤销
                    'redo'  // 重复
                ";
        }
        //上传配置
        if(!isset($attributes['upload']['path_type'])){
            $attributes['upload']['path_type'] = 'picture';
        }
        //上传图片的服务器地址
        if(!isset($attributes['upload']['upload_img_server'])){
            $attributes['upload']['upload_img_server'] = url('admin/Upload/upload');
        }
        //图片选择器弹窗地址
        if(!isset($attributes['pictures_dialog']['url'])){
            $attributes['pictures_dialog']['url'] = url('admin/Upload/attachmentLayer',['input_id_name'=>$attributes['id'],'path_type'=>$attributes['upload']['path_type'],'gettype'=>'multiple','from'=>'wangeditor']);
        }

        /**
         * 字段：id,name,style,width,height,
         */
        $this->assign($attributes);
        return $this->fetch('common@widget/wangeditor');
    }

}
