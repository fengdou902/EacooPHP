<?php
// 附件逻辑
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\logic;

use think\File;
class Attachment extends Base {

    /**
     * 获取详细信息
     */
    public static function info($id=0) {
        if (!$id) return false;
        //获取上传文件的地址
        $result = cache('Attachment_'.$id);
        if (!$result || empty($result)) {
            $info = self::get($id);
            if (!empty($info)) {
                $info   = $info->toArray();
                $result = self::extendInfo($info);
                $result = array_merge($info,$result);
                cache('Attachment_'.$id, $result,1200);
            }
        }

        return $result;
    }

    /**
     * 查找后置操作
     */
    public function afterSelect(&$result) {
        foreach($result as &$record){
            self::extendInfo($record);
        }
    }

    /**
     * @param  array $map 查询过滤
     * @param  integer $page 分页值
     * @param  string $order 排序参数
     * @param  string $field 结果字段
     * @param  integer $r 每页数量
     * @return 结果集
     */
    public function getListByPage($map,$order='sort asc,update_time desc',$field='*',$r=20,$show_page=false)
    {
        $list=$this->where($map)->order($order)->field($field)->page(1,$r)->select();
        if ($list) {
            $this->afterSelect($list);
        }
        $page_totalCount=$this->where($map)->count();
        return array($list,$page_totalCount);
    }
    /**
     * 查询置扩展信息
     * @param  array  $result [description]
     * @return [type]         [description]
     */
    protected static function extendInfo($result=[])
    {
        if ($result['location']=='link') {
            $result['url']= $result['src'] = $result['path'];
        } else {
            $result['real_path']= PUBLIC_PATH.$result['path'];
            $result['src'] = getImgSrcByExt($result['ext'],$result['path'],true);
            $result['url'] = cdn_img_url($result['path']);
            
        }
        $result['size']       = format_file_size($result['size']);
        $result['uploadtime'] = $result['create_time'];
        $result['author']     = get_user_info($result['uid'],'nickname')['nickname'] ? :'未知';
        $result['term_id']    = get_term_info($result['id'],'term_id','attachment')['term_id']?:0;//获取该附件管理的分类
        return $result;
    }

    /**
     * 下载指定文件
     * @param  number  $root 文件存储根目录
     * @param  integer $id   文件ID
     * @param  string   $args     回调函数参数
     * @return boolean       false-下载失败，否则输出下载文件
     */
    public function download($root, $id, $callback = null, $args = null){
        /* 获取下载文件信息 */
        $file = $this->find($id);
        if(!$file){
            $this->error = '文件不存在';
            return false;
        }

        /* 下载文件 */
        switch ($file['location']) {
            case 0: //下载本地文件
                $file['rootpath'] = $root;
                return $this->downLocalFile($file, $callback, $args);
            case 1: //TODO: 下载远程FTP文件
                break;
            default:
                $this->error =  '未指明下载图片信息';
                return false;

        }

    }

    /**
     * 检测当前上传的文件是否已经存在
     * @param  array   $file 文件上传数组
     * @return boolean       文件信息， false - 不存在该文件
     */
    public function isFile($file){
        if(empty($file['md5'])){
            throw new \Exception('缺少参数:md5');
        }
        /* 查找文件 */
		$map = array('md5' => $file['md5'],'sha1'=>$file['sha1'],);
        return $this->where($map)->find();
    }

    /**
     * 下载本地文件
     * @param  array    $file     文件信息数组
     * @param  callable $callback 下载回调函数，一般用于增加下载次数
     * @param  string   $args     回调函数参数
     * @return boolean            下载失败返回false
     */
    private function downLocalFile($file, $callback = null, $args = null){
        if(is_file($file['rootpath'].$file['savepath'].$file['savename'])){
            /* 调用回调函数新增下载数 */
            is_callable($callback) && call_user_func($callback, $args);

            /* 执行下载 */ //TODO: 大文件断点续传
            header("Content-Description: File Transfer");
            header('Content-type: ' . $file['type']);
            header('Content-Length:' . $file['size']);
            if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) { //for IE
                header('Content-Disposition: attachment; filename="' . rawurlencode($file['name']) . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
            }
            readfile($file['rootpath'].$file['savepath'].$file['savename']);
            exit;
        } else {
            $this->error = lang('_FILE_HAS_BEEN_DELETED_WITH_EXCLAMATION_');
            return false;
        }
    }

    /**
     * 判断附件类型
     * @param $data
     */
    public function file_mimeType($ext){
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
            case 'gif':
            case 'png':
            case 'bmp':
            case 'ico':
                $mimeType='image/'.$ext;
                break;
            case 'psd':
            case 'rar':
            case '7z':
            case 'exe':
            case '3gp':
            case 'flv':
            case 'krc':
            case 'lrc':
            case 'chm':
            case 'sql':
            case 'con':
            case 'dat':
            case 'ini':
            case 'php':
            case 'ttf':
            case 'font':
            case 'dll':
                $mimeType='application/octet-stream';
                break;
            case 'mp3':
            case 'wav':
                $mimeType='audio/'.$ext;
                break;
            case 'zip':
            case 'pdf':
                $mimeType='application/'.$ext;
                break;
            case 'doc':
                $mimeType='application/msword';
                break;
            case 'xls':
            case 'xlsx':
                $mimeType='application/vnd.ms-excel';
                break;
            case 'ppt':
                $mimeType='application/vnd.ms-powerpoint';
                break;
            case 'log':
            case 'txt':
                $mimeType='text/plain';
                break;
            case 'js':
                $mimeType='application/x-javascript';
                break;
            default:
                # code...
                break;
        }
            $mime_types = [
            'apk'     => 'application/vnd.android.package-archive',
            '3gp'     => 'video/3gpp', 'ai' => 'application/postscript', 
            'aif'     => 'audio/x-aiff', 'aifc' => 'audio/x-aiff', 
            'aiff'    => 'audio/x-aiff', 'asc' => 'text/plain', 
            'atom'    => 'application/atom+xml', 'au' => 'audio/basic', 
            'avi'     => 'video/x-msvideo', 'bcpio' => 'application/x-bcpio', 
            'bin'     => 'application/octet-stream', 'bmp' => 'image/bmp', 
            'cdf'     => 'application/x-netcdf', 'cgm' => 'image/cgm', 
            'class'   => 'application/octet-stream', 
            'cpio'    => 'application/x-cpio', 
            'cpt'     => 'application/mac-compactpro', 
            'csh'     => 'application/x-csh', 'css' => 'text/css', 
            'dcr'     => 'application/x-director', 'dif' => 'video/x-dv', 
            'dir'     => 'application/x-director', 'djv' => 'image/vnd.djvu', 
            'djvu'    => 'image/vnd.djvu', 
            'dll'     => 'application/octet-stream', 
            'dmg'     => 'application/octet-stream', 
            'dms'     => 'application/octet-stream', 
            'doc'     => 'application/msword', 'dtd' => 'application/xml-dtd', 
            'dv'      => 'video/x-dv', 'dvi' => 'application/x-dvi', 
            'dxr'     => 'application/x-director', 
            'eps'     => 'application/postscript', 'etx' => 'text/x-setext', 
            'exe'     => 'application/octet-stream', 
            'ez'      => 'application/andrew-inset', 'flv' => 'video/x-flv', 
            'gif'     => 'image/gif', 'gram' => 'application/srgs', 
            'grxml'   => 'application/srgs+xml', 
            'gtar'    => 'application/x-gtar', 'gz' => 'application/x-gzip', 
            'hdf'     => 'application/x-hdf', 
            'hqx'     => 'application/mac-binhex40', 'htm' => 'text/html', 
            'html'    => 'text/html', 'ice' => 'x-conference/x-cooltalk', 
            'ico'     => 'image/x-icon', 'ics' => 'text/calendar', 
            'ief'     => 'image/ief', 'ifb' => 'text/calendar', 
            'iges'    => 'model/iges', 'igs' => 'model/iges', 
            'jnlp'    => 'application/x-java-jnlp-file', 'jp2' => 'image/jp2', 
            'jpe'     => 'image/jpeg', 'jpeg' => 'image/jpeg', 
            'jpg'     => 'image/jpeg', 'js' => 'application/x-javascript', 
            'kar'     => 'audio/midi', 'latex' => 'application/x-latex', 
            'lha'     => 'application/octet-stream', 
            'lzh'     => 'application/octet-stream', 
            'm3u'     => 'audio/x-mpegurl', 'm4a' => 'audio/mp4a-latm', 
            'm4p'     => 'audio/mp4a-latm', 'm4u' => 'video/vnd.mpegurl', 
            'm4v'     => 'video/x-m4v', 'mac' => 'image/x-macpaint', 
            'man'     => 'application/x-troff-man', 
            'mathml'  => 'application/mathml+xml', 
            'me'      => 'application/x-troff-me', 'mesh' => 'model/mesh', 
            'mid'     => 'audio/midi', 'midi' => 'audio/midi', 
            'mif'     => 'application/vnd.mif', 'mov' => 'video/quicktime', 
            'movie'   => 'video/x-sgi-movie', 'mp2' => 'audio/mpeg', 
            'mp3'     => 'audio/mpeg', 'mp4' => 'video/mp4', 
            'mpe'     => 'video/mpeg', 'mpeg' => 'video/mpeg', 
            'mpg'     => 'video/mpeg', 'mpga' => 'audio/mpeg', 
            'ms'      => 'application/x-troff-ms', 'msh' => 'model/mesh', 
            'mxu'     => 'video/vnd.mpegurl', 'nc' => 'application/x-netcdf', 
            'oda'     => 'application/oda', 'ogg' => 'application/ogg', 
            'ogv'     => 'video/ogv', 'pbm' => 'image/x-portable-bitmap', 
            'pct'     => 'image/pict', 'pdb' => 'chemical/x-pdb', 
            'pdf'     => 'application/pdf', 
            'pgm'     => 'image/x-portable-graymap', 
            'pgn'     => 'application/x-chess-pgn', 'pic' => 'image/pict', 
            'pict'    => 'image/pict', 'png' => 'image/png', 
            'pnm'     => 'image/x-portable-anymap', 
            'pnt'     => 'image/x-macpaint', 'pntg' => 'image/x-macpaint', 
            'ppm'     => 'image/x-portable-pixmap', 
            'ppt'     => 'application/vnd.ms-powerpoint', 
            'ps'      => 'application/postscript', 'qt' => 'video/quicktime', 
            'qti'     => 'image/x-quicktime', 'qtif' => 'image/x-quicktime', 
            'ra'      => 'audio/x-pn-realaudio', 
            'ram'     => 'audio/x-pn-realaudio', 'ras' => 'image/x-cmu-raster', 
            'rdf'     => 'application/rdf+xml', 'rgb' => 'image/x-rgb', 
            'rm'      => 'application/vnd.rn-realmedia', 
            'roff'    => 'application/x-troff', 'rtf' => 'text/rtf', 
            'rtx'     => 'text/richtext', 'sgm' => 'text/sgml', 
            'sgml'    => 'text/sgml', 'sh' => 'application/x-sh', 
            'shar'    => 'application/x-shar', 'silo' => 'model/mesh', 
            'sit'     => 'application/x-stuffit', 
            'skd'     => 'application/x-koan', 'skm' => 'application/x-koan', 
            'skp'     => 'application/x-koan', 'skt' => 'application/x-koan', 
            'smi'     => 'application/smil', 'smil' => 'application/smil', 
            'snd'     => 'audio/basic', 'so' => 'application/octet-stream', 
            'spl'     => 'application/x-futuresplash', 
            'src'     => 'application/x-wais-source', 
            'sv4cpio' => 'application/x-sv4cpio', 
            'sv4crc'  => 'application/x-sv4crc', 'svg' => 'image/svg+xml', 
            'swf'     => 'application/x-shockwave-flash', 
            't'       => 'application/x-troff', 'tar' => 'application/x-tar', 
            'tcl'     => 'application/x-tcl', 'tex' => 'application/x-tex', 
            'texi'    => 'application/x-texinfo', 
            'texinfo' => 'application/x-texinfo', 'tif' => 'image/tiff', 
            'tiff'    => 'image/tiff', 'tr' => 'application/x-troff', 
            'tsv'     => 'text/tab-separated-values', 'txt' => 'text/plain', 
            'ustar'   => 'application/x-ustar', 
            'vcd'     => 'application/x-cdlink', 'vrml' => 'model/vrml', 
            'vxml'    => 'application/voicexml+xml', 'wav' => 'audio/x-wav', 
            'wbmp'    => 'image/vnd.wap.wbmp', 
            'wbxml'   => 'application/vnd.wap.wbxml', 'webm' => 'video/webm', 
            'wml'     => 'text/vnd.wap.wml', 
            'wmlc'    => 'application/vnd.wap.wmlc', 
            'wmls'    => 'text/vnd.wap.wmlscript', 
            'wmlsc'   => 'application/vnd.wap.wmlscriptc', 
            'wmv'     => 'video/x-ms-wmv', 'wrl' => 'model/vrml', 
            'xbm'     => 'image/x-xbitmap', 'xht' => 'application/xhtml+xml', 
            'xhtml'   => 'application/xhtml+xml', 
            'xls'     => 'application/vnd.ms-excel', 
            'xml'     => 'application/xml', 'xpm' => 'image/x-xpixmap', 
            'xsl'     => 'application/xml', 'xslt' => 'application/xslt+xml', 
            'xul'     => 'application/vnd.mozilla.xul+xml', 
            'xwd'     => 'image/x-xwindowdump', 'xyz' => 'chemical/x-xyz', 
            'zip'     => 'application/zip' ];

        return $mimeType;
    }

}
