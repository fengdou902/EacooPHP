<?php 
// 附件
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------

use app\common\logic\Attachment as AttachmentLogic;

if (!function_exists('mkdirs'))
{
    /**
     * 创建多级目录
     * @param  [type] $dir 路径
     * @return [type] [description]
     */
    function mkdirs($dir) {
        if (! is_dir ( $dir )) {
            if (! mkdirs ( dirname ( $dir ) )) {
                return false;
            }
            if (! mkdir ( $dir, 0755 )) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('rmdirs'))
{
    /**
     * 删除文件夹
     * @param string $dirname 目录
     * @param bool $withself 是否删除自身
     * @return boolean
     */
    function rmdirs($dirname, $withself = true)
    {
        if (!is_dir($dirname))
            return false;
        $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo)
        {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself)
        {
            @rmdir($dirname);
        }
        return true;
    }

}

if (!function_exists('copydirs'))
{
    /**
     * 复制文件夹
     * @param string $source 源文件夹
     * @param string $dest 目标文件夹
     */
    function copydirs($source, $dest)
    {
        if (!is_dir($dest))
        {
            mkdir($dest, 0755);
        }
        foreach (
        $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item
        )
        {
            if ($item->isDir())
            {
                $sontDir = $dest . DS . $iterator->getSubPathName();
                if (!is_dir($sontDir))
                {
                    mkdir($sontDir);
                }
            }
            else
            {
                copy($item, $dest . DS . $iterator->getSubPathName());
            }
        }
    }

}

/**
 * 改变用户组的权限
 * @param  [type] $path 递归路径
 * @param  [type] $uid 用户名
 * @param  [type] $gid 用户组
 * @param  integer $model [description]
 * @return [type] [description]
 * @date   2018-06-03
 * @author 心云间、凝听 <981248356@qq.com>
 */
function recurse_chown_chgrp_chmod($path, $uid, $gid,$model=0755) 
{ 
    $d = opendir ($path) ; 
    chown($path,'www');
    chgrp($path,'www');
    chmod($path,$model);
    while(($file = readdir($d)) !== false) { 
        if ($file != "." && $file != "..") { 

            $typepath = $path . "/" . $file ; 

            //print $typepath. " : " . filetype ($typepath). "<BR>" ; 
            if (filetype ($typepath) == 'dir') { 
                recurse_chown_chgrp_chmod ($typepath, $uid, $gid); 
            } 

            chown($typepath, $uid); 
            chgrp($typepath, $gid); 
            chmod($typepath,$model);
        } 
    } 
    return true;
}

/**
 * 快速获取文件的扩展名即后缀。
 * @param  [type] $filename [description]
 * @return [type] [description]
 * @author 心云间、凝听 <981248356@qq.com>
 */
function getExtension($filename){ 
  $myext = substr($filename, strrpos($filename, '.')); 
  return str_replace('.','',$myext); 
}

/**
 * 格式化字节大小 把字节数格式为 B K M G T 描述的大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function format_bytes($size, $delimiter = '') {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 获取文件的大小，并且转换成便于阅读的KB，MB等格式。
 * @param  [type] $size [description]
 * @return [type]       [description]
 * 使用方法
 * $thefile = filesize('test_file.mp3'); 
 * echo format_file_size($thefile);
 */
function format_file_size($size) { 
    $sizes = [" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB"]; 
    if ($size == 0) {  
        return('n/a');  
    } else { 
      return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]);  
    } 
}

/**
 * 获取远程附件文件尺寸
 * @return array
 * @author 心云间、凝听 <981248356@qq.com>
 */
 function fsockopen_remote_filesize($url) {
     $url = parse_url($url);

     if (empty($url['host'])) {
         return false;
     }

     $url['port'] = empty($url['post']) ? 80 : $url['post'];
     $url['path'] = empty($url['path']) ? '/' : $url['path'];

     $fp = fsockopen($url['host'], $url['port'], $error);

     if($fp) {
         fputs($fp, "GET " . $url['path'] . " HTTP/1.1\r\n");
         fputs($fp, "Host:" . $url['host']. "\r\n\r\n");

         while (!feof($fp)) {
             $str = fgets($fp);
             if (trim($str) == '') {
                 break;
             }elseif(preg_match('/Content-Length:(.*)/si', $str, $arr)) {
                 return trim($arr[1]);
             }
         }
         fclose ( $fp);
         return false;
     }else {
         return false;
     }
 }  
 
/**
 * 列出目录下的所有文件
 * @param  [type] $DirPath 路径地址
 * @return [type]          [description]
 */
function listDirFiles($DirPath){ 
    if($dir = opendir($DirPath)){ 
         while(($file = readdir($dir))!== false){ 
                if(!is_dir($DirPath.$file)) 
                { 
                    echo "filename: $file<br />"; 
                } 
         } 
    } 
}

//列出目录下所有的文件
function getfiles($path){ 
    foreach(scandir($path) as $afile){
        if($afile=='.'||$afile=='..'){
            continue;
        }
        
        if(is_dir($path.'/'.$afile)){ 
            getfiles($path.'/'.$afile); 
        } else { 
            echo $path.'/'.$afile.'<br />'; 
        } 
    } 
 } 

/**
 * 导出excel
 * @param $strTable 表格内容
 * @param $filename 文件名
 */
function downloadExcel($strTable,$filename)
{
    header("Content-type: application/vnd.ms-excel");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=".$filename.".xls");
    header('Expires:0');
    header('Pragma:public');
    echo '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.$strTable.'</html>';
}

/**
 * 文件上传驱动
 * @return [type] [description]
 * @date   2017-11-15
 * @author 心云间、凝听 <981248356@qq.com>
 */
function upload_drivers()
{
    $dirver_list = ['local'=>'本地'];
    $dirver_hook_id = db('hooks')->where('name','UploadFile')->value('id');
    if ($dirver_hook_id>0) {
        $dirvers = db('Hooks_extra')->where('hook_id',$dirver_hook_id)->where('depend_type',2)->column('depend_flag');
        if (!empty($dirvers)) {
            foreach ($dirvers as $key => $dirver) {
                $dirver_list[$dirver] = db('plugins')->where('name',$dirver)->value('title');
            }
        }
    }
    
    return $dirver_list;
}

/**
 * 获取cdn域名
 * @return [type] [description]
 * @date   2017-11-16
 * @author 心云间、凝听 <981248356@qq.com>
 */
function get_cdn_domain()
{
    $cdn_domain = cache('cdn_domain');
    if (!$cdn_domain) {
        $driver = !empty(config('attachment_options.driver')) ? config('attachment_options.driver') :'local';
        if ($driver!='local') {
            $check_res = check_install_plugin($driver);
            if ($check_res) {
                $class = get_plugin_class($driver);
                if (class_exists($class)) {
                    $plugin = new $class();
                    if(method_exists($plugin,'getDomain')){
                        $cdn_domain = $plugin->getDomain();
                        cache('cdn_domain',$cdn_domain,3600);
                        return $cdn_domain;
                    }
                    
                } 
            }
        } 
        $cdn_domain = request()->domain();
        cache('cdn_domain',$cdn_domain,3600);
    }
    return $cdn_domain;
    
}
/**
 * 获取cdn域名
 * @return [type] [description]
 * @date   2017-11-16
 * @author 心云间、凝听 <981248356@qq.com>
 */
function get_uploadpath_url()
{
    $cdn_uploadpath_url = cache('cdn_uploadpath_url');
    if (!$cdn_uploadpath_url) {
        $driver = !empty(config('attachment_options.driver')) ? config('attachment_options.driver') :'local';
        if ($driver!='local') {
            $check_res = check_install_plugin($driver);
            if ($check_res) {
                $class = get_plugin_class($driver);
                if (class_exists($class)) {
                    $plugin = new $class();
                    if(method_exists($plugin,'getUploadPathUrl')){
                        $cdn_uploadpath_url = $plugin->getUploadPathUrl();
                        cache('cdn_uploadpath_url',$cdn_uploadpath_url,3600);
                        return $cdn_uploadpath_url;
                    }
                    
                } 
            }
        } 
        $cdn_uploadpath_url = request()->domain();
        cache('cdn_uploadpath_url',$cdn_uploadpath_url,3600);
    }
    return $cdn_uploadpath_url;
    
}

/**
 * 图片地址转化为CDN
 * @param  string $path 图片路径
 * @param  string $style 样式
 * @return [type]       [description]
 */
function cdn_img_url($path = '', $style='')
{
    if($path=='' || !$path) return false;

    if (strpos($path, 'http://')!==false || strpos($path, 'https://')!==false) return $path;

    $cdn_path    = get_uploadpath_url().$path;
    if ($style!='') {
        $url = $cdn_path.'!'.$style;
    } else{
        $url = $cdn_path; 
    }
    
    return $url;
}

/*******************************images图片相关 start ********************************/

//获取媒体分类对象数量
function term_media_count($term_id,$path_type='picture',$map=[]){
    $media_ids = db('term_relationships')->where(['term_id'=>$term_id,'table'=>'attachment'])->select();
    if(count($media_ids)){
        $object_ids       = array_column($media_ids,'object_id');
        $map['id']        = ['in',$object_ids];
        
        $map['path_type'] = ['in',$path_type];//过滤目录
        $count            = model('Attachment')->where($map)->count();
    }
    return isset($count) && $count ? $count:0;
}

/**
 * 获取上传附件路径
 * @param  int $id 文件ID
 * @return string
 */
function get_image($id = 0 , $type='') {
    $url = '';
    if ((int)$id > 0) {
        $url = getThumbImageById($id,$type);
    }
    if (!$url) $url = config('view_replace_str.__PUBLIC__').'/img/noimage.gif';

    return $url;
}

/**
 * 获取缩略图
 * @param  string $path 图片路径
 * @param  string $style 缩略图样式
 * @return [type]       [description]
 */
function get_thumb_image($path = '', $style='small')
{
    if($path=='' || !$path) return false;
    if (strpos($path, 'http://')!==false || strpos($path, 'https://')!==false) return $path;
    
    $option   = config('attachment_options');//获取附件配置值

    if ($option['driver']!='local' && $option['driver']!='') {
        $url = cdn_img_url($path,$style);
    } else{
        
        if (isset($option['cut']) && $option['cut']) {
             if (!empty($option[$style.'_size'])) {//缩略图
                $path = thumb_image($path,$option[$style.'_size']['width'],$option[$style.'_size']['height']);
            }
        }
       
        $url = get_uploadpath_url().$path;
    }

    return $url;
}

/**
 * 获取文件信息
 * @param  int $id 文件ID
 * @return string
 */
function get_attachment_info($id) {
    if ((int)$id) {
        $info = AttachmentLogic::info($id);
        return $info;
    }
    return false;
}

/**
 * 通过ID获取到图片的缩略图
 * @param  [type] $img_id     图片ID
 * @param  string $thumb_type 缩略类型。小：small,中：medium,大：large
 * @return [type]             [description]
 */
function getThumbImageById($img_id,$thumb_type='small')
{
    $info = get_attachment_info($img_id);//附件信息

    if (empty($info)) {
        return root_full_path(config('view_replace_str.__PUBLIC__').'/img/file-default.png');
    }

    return get_thumb_image($info['path'],$thumb_type);
    
    // if ($info['location'] == 'local') {

    //     return get_thumb_image($info['path'],$thumb_type);

    // } else {
    //     $new_img = $info['path'];
    //     $name = get_plugin_class($info['location']);
    //     if (class_exists($name)) {
    //         $class = new $name();
    //         if (method_exists($class, 'small')) {
    //             $new_img = $class->thumb($info['path'],$thumb_type);
    //         }
    //     }
    //     return root_full_path($new_img);
    // }

}

/**通过文件格式返回通用的附件图片
 * @param $ext
 * @param $is_default 强制使用默认图像
 * @return mixed
 */
function getImgSrcByExt($ext,$path='',$is_default=false){
    /*if (in_array($ext,['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'wps', 'txt', 'zip', 'rar', 'gz', 'bz2', '7z'])) {
        if ($path=''||$is_default==true) {
            $path = config('view_replace_str.__PUBLIC__').'/img/file-default.png';
        }
    }elseif(in_array($ext,['mp3', 'wav', 'mp4', 'wmv', 'avi', 'rm', 'rmvb'])){
        if ($path=''||$is_default==true) {
            $path = config('view_replace_str.__PUBLIC__').'/img/file-default.png';
        }
    }*/
    if(!in_array($ext,['jpg','jpeg','bmp','png','gif'])){
        $path = config('view_replace_str.__PUBLIC__').'/img/file-'.$ext.'.png';
    }

    return root_full_path($path);
}

/**获取第一张图（性能高但不适合带有表情的内容）
 * @param $html_content
 * @return mixed
 */
function get_first_pic($html_content)
{
    preg_match_all("/<img.*\>/isU", $html_content, $ereg); //正则表达式把图片的整个都获取出来了
    $img = $ereg[0][0]; //图片
    $p = "#src=('|\")(.*)('|\")#isU"; //正则表达式
    preg_match_all($p, $img, $img1);
    $img_path = $img1[2][0]; //获取第一张图片路径
    if (strpos($img_path,'static/emotions')===false) {//排除表情
        return $img_path;
    }  
}

/**带有排查字符串的第一张图（性能稍低但更准确）
 * @param $html_content
 * @param $check_str 包含该字符串的去除
 * @return mixed
 */
function get_first_img($html_content,$check_str='static/emotions')
{
    preg_match_all("/<img.*\>/isU", $html_content, $ereg); //正则表达式把图片的整个都获取出来了
    //$img = $ereg[0][0]; //图片
    $imgs = [];
    foreach ($ereg[0] as $key => $img) {
        $p = "#src=('|\")(.*)('|\")#isU"; //正则表达式
        preg_match_all($p, $img, $img1);
        $img_path = $img1[2][0]; //获取第一张图片路径
        if (strpos($img_path,$check_str)===false) {//排除表情
                $imgs[]=$img_path;
            }
    }

    if (!empty($imgs)) {//排除表情
        return $imgs[0];
    }  
}

/**获取图片数量
 * @param $html_content
 * @return mixed
 */
function pic_total($html_content) {
    $post_img = '';
    ob_start();
    ob_end_clean();
    $output = preg_match_all('/\<img.+?src="(.+?)".*?\/>/is ',$html_content, $matches, PREG_SET_ORDER);
    $post_img_src = $matches [0][1];
    $cnt = count($matches);
    return $cnt;
}

/*******************************************************************************/
/**
 * 图片缩略图
 * @param  [type]  $path   路径
 * @param  [type]  $width  宽度
 * @param  [type]  $height 高度
 * @param  integer $type   缩略图类型
 * @return [type]          [description]
 */
function thumb_image($path, $width, $height, $type = 3){

    if(empty($width) && empty($height)){
        return $path;
    }
    $imgDir = realpath(PUBLIC_PATH.$path);
    if(!is_file($imgDir)){
        return $path;
    }
    $imgInfo = pathinfo($path);
    $newImg = $imgInfo['dirname'].'/thumb_'.$width.'_'.$height.'_'.$imgInfo["basename"];
    $newImgDir = PUBLIC_PATH.$newImg;
    if(!is_file($newImgDir)){
        $image =\think\Image::open($imgDir);
        $image->thumb($width, $height,$type)->save($newImgDir);
    }
    return $newImg;
}

/**
 * 图像裁剪
 * @param  [type]  $path    图片路径
 * @param  [type]  $w      [description]
 * @param  [type]  $h      [description]
 * @param  integer $x      [description]
 * @param  integer $y      [description]
 * @param  [type]  $width  [description]
 * @param  [type]  $height [description]
 * @return [type]          [description]
 */
function crop_image($path,$w, $h, $x = 0, $y = 0, $width = null, $height = null){
    if(empty($width)&&empty($height)){
        return $path;
    }
    $imgDir = realpath(PUBLIC_PATH.$path);
    if(!is_file($imgDir)){
        return $path;
    }
    $imgInfo = pathinfo($path);
    $newImg = $imgInfo['dirname'].'/cut_'.$width.'_'.$height.'_'.$imgInfo["basename"];
    $newImgDir = PUBLIC_PATH.$newImg;
    if(!is_file($newImgDir)){
        $image =\think\Image::open($imgDir);
        $image->crop($w, $h, $x = 0, $y = 0, $width = null, $height = null)->save($newImgDir);
    }
    return $newImg;
}
/*******************************images图片相关 end ********************************/