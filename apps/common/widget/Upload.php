<?php
namespace app\common\widget;
use app\common\controller\Base;

/**
 * Class Upload  上传图片组件
 * @package Common\Widget
 * @author:心云间、凝听 <981248356@qq.com>
 */
class Upload extends Base
{

    /**
     * 单图片
     * @param  array  $params [description]
     * @return [type]             [description]
     */
    public function picture($params = [])
    {
        $isLoadScript  = isset($params['isLoadScript']) && $params['isLoadScript'] ? 1 : 0;

        //$filetype = $this->rules['filetype'];

        //$id = $attributes_id;
        $params['config'] = ['text' =>'图片选择'];

        //$this->assign('img',$img);
        
        if (strpos($params['name'],'[')) {
            $params['id'] = str_replace(']','',str_replace('[','',$params['name']));
          } else{
            $params['id'] = $params['name'];
          }
        $this->assign('isLoadScript', $isLoadScript);
        $this->assign('field',$params);
        return $this->fetch('common@widget/picture');
    }

    /**
     * 上传多图
     * @param  array  $params [description]
     * @return [type]             [description]
     */
    public function pictures($params = [])
    {
        $value            = $params['value'];
        $images           = explode(',', $value);
        //$filetype = $this->rules['filetype'];

        //$id = $attributes_id;
        if (empty($params['config']['text']))
            $params['config'] = array('text' => lang('_FILE_SELECT_'));

        if (is_array($params['value'])) {
            $images = $params['value'];
            $input_value = implode(',', $params['value']);
        } else {
            $images = explode(',',$params['value']);
            $input_value = $params['value'];
        }

        $this->assign('images', $images);
        $this->assign('field',$params);

        return $this->fetch('common@widget/pictures');
    }
    
    /**
     * 单文件
     * @param  array  $params [description]
     * @return [type]             [description]
     */
    public function file($params = []){

        $params['id'] = $params['id'] ? $params['id'] : $params['name'];
        $config           = $params['config'];
        $class            = $params['class'];
        $value            = $params['value'];
        $name             = $params['name'];
        $width            = $params['width'] ? $params['width'] : 100;
        $height           = $params['height'] ? $params['height'] : 100;
        //$filetype = $this->rules['filetype'];

        $config = $config['config'];

        $params['config'] = ['text' =>'文件选择'];

        if($value){
           $file = db('File')->find($value);
            $this->assign('file',$file);
        }

        $this->assign($params);
        return $this->fetch('common@Widget/file');

    }
    
    /**
     * 多文件上传
     * @param  array  $params [description]
     * @return [type]             [description]
     */
    public function files($params = []){
        
        $params['id'] = $params['id']? $params['id'] : $params['name'];
        $config           = $params['config'];
        $class            = $params['class'];
        $value            = $params['value'];
        $name             = $params['name'];
        $width            = $params['width'] ? $params['width'] : 100;
        $height           = $params['height'] ? $params['height'] : 100;
        $isLoadScript     = $params['isLoadScript']?1:0;
        //$filetype       = $this->rules['filetype'];
        
        $config           = $config['config'];

        $params['config'] = ['text' =>  lang('_FILE_SELECT_')];

        $files_ids=explode(',',$value);
        if($files_ids){
            foreach ($files_ids as $v) {
               $files[] = db('attachment')->find($v);
            }
            unset($v);

        }
        $this->assign('isLoadScript',$isLoadScript);
        $this->assign('files',$files);
        $this->assign($params);
        return $this->fetch('common@widget/files');

    }
} 