<?php
// 系统在线升级控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 https://www.eacoophp.com, All rights reserved.
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  yyyvy <76836785@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use ZipArchive;

class OnlineUpdate extends Admin {



    /*
     *  在线更新
     *  yyyvy
     *  2018-8-18
     * */
    public function index(){
        $this->assign('meta_title','在线更新');
        $isUpdate = self::isupdate();
        if ($isUpdate){
            $eacoo_version['version'] = $isUpdate['version'];
            $eacoo_version['build_version'] = $isUpdate['build_version'];
            $eacoo_version['description'] = $isUpdate['description'];
            $this->assign('eacoo_version',$eacoo_version);
        }
        return $this->fetch();
    }



    /**
     * 立即更新
     * 2018-8-21
     * yyyvy
     */
    public function updateNow(){
        $isupdate = self::isupdate();
        if($isupdate){
            $appTmpDir = RUNTIME_PATH . 'online_update' . DS . $isupdate['version'] . DS;   //缓存更新包路径
            $tmpFile = $appTmpDir . $isupdate['build_version'] . ".zip";    //更新包名称
            //print_r($tmpFile);die;
            if (!is_dir($appTmpDir))
            {
                @mkdir($appTmpDir, 0755, true);
            }
            //EACOOPHP_V
            echo "当前框架版本[".EACOOPHP_V."]    当前构建版本[".BUILD_VERSION."]<br>";
            echo "更新框架版本[".$isupdate['version']."]    更新构建版本[".$isupdate['build_version']."]<br>";
            echo "下载更新文件包...<br>";
            $zip = $this->download($isupdate['url'],$tmpFile);
            if($zip){
                echo '更新文件包下载完成！<br>';
                echo '解压更新文件包...<br>';
                //解压文件
                $unzipFile = $this->unzip($tmpFile,$appTmpDir.DS.$isupdate['build_version']);
                if($unzipFile){
                    echo '解压更新文件包完成！<br>';
                    $update_result = $this->read_all($appTmpDir.DS.$isupdate['build_version'],$isupdate['version'],$isupdate['build_version']);
                    if($update_result){
                        $this->deldir($appTmpDir);
                        echo '更新成功!';

                        $this->success('更新成功！');
                    }else{
                        echo '升级失败！';
                    }
                }else{
                    echo '解压更新文件包失败！';
                }
                //ROOT_PATH
            }else{
                echo '下载更新文件包失败！';
            }
        }else{
            echo '暂无更新！';
        }
        return;
    }



    /**
     * 检测云端版本，判断是否有更新
     * 2018-8-21
     * yyyvy
     */
    public function isUpdate(){
        $url = config('eacoo_api_url').'/online_update';
        $result = curl_get($url);
        $versions_list = json_decode($result,true);

        $eacoophp_v = EACOOPHP_V;   //当前框架版本号
        $buile_v = BUILD_VERSION;   //构建版本号
        $next_buile_v = []; //下一个构建版本号

        $versions_list_count = count($versions_list);   //取版本列表长度
        //循环出下一个版本号
        foreach ($versions_list as $key=>$value){
            //判断框架版本号相同
            if ($value['versions'] == $eacoophp_v){
                $build_list_count = count($value['build_version']);   //取构建版本列表长度
                foreach ($value['build_version'] as $twokey=>$for_version){
                    if($for_version['version'] == $buile_v){
                        //判断当前框架版本的构建版本是不是最后一个；
                        if($twokey < $build_list_count-1){
                            $updateinfo = $value['build_version'][++$twokey];
                            $next_buile_v['version'] = $eacoophp_v;
                            $next_buile_v['description'] = $value['description'];
                            $next_buile_v['build_version'] = $updateinfo['version'];
                            $next_buile_v['url'] = $updateinfo['url'];
                        }else{
                            //构架版本已经是最后一个了，取下一个框架版本的信息
                            if($key < $versions_list_count-1){
                                $next_info = $versions_list[++$key];
                                $next_buile_v['version'] = $next_info['versions'];
                                $next_buile_v['description'] = $next_info['description'];
                                $next_buile_v['build_version'] = $next_info['build_version'][0]['version'];
                                $next_buile_v['url'] = $next_info['build_version'][0]['url'];
                            }else{
                                //否则就是没有更新
                                $next_buile_v = [];
                            }
                        }

                    }
                }
            }
        }
        return $next_buile_v;
    }



    /**
     * 遍历升级文件夹里的文件，替换或新增文件，自动备份原来文件
     * @param $dir  升级文件路径
     * @param $versions    升级版本号
     */
    public function read_all($dir,$versions=0,$build_v=0){
        $handle = opendir($dir);
        if($handle){
            while(($fl = readdir($handle)) !== false){
                $temp = $dir.DIRECTORY_SEPARATOR.$fl;
                //如果不加  $fl!='.' && $fl != '..'  则会造成把$dir的父级目录也读取出来
                if(is_dir($temp) && $fl!='.' && $fl != '..'){
                    //替换为备份文件夹路径
                    $online_backup = str_replace('runtime\online_update'. DS . $versions . DS,'data\online_backups'. DS . $versions . DS,$temp);

                    //备份目录不存在就创建
                    if (!is_dir($online_backup)) {
                        @mkdir($online_backup, 0755, true);
                    }
                    //echo '目录：'.$temp.'<br>';
                    //文件目录不存在就创建
                    if (!is_dir($temp)) {
                        @mkdir($temp, 0755, true);
                    }
                    //递归
                    $this->read_all($temp,$versions,$build_v);
                }else{
                    if($fl!='.' && $fl != '..'){
                        $dest = str_replace('runtime\online_update'. DS . $versions . DS.DS . $build_v . DS,'',$temp);
                        //如果文件存在，进行文件备份
                        if(file_exists($dest)){
                            $backups_desta = ROOT_PATH.'data\online_backups'. DS . $versions . DS . $build_v . DS;
                            $backups_destb = str_replace(ROOT_PATH,'',$dest);
                            copy($dest,$backups_desta.$backups_destb);
                        }

                        //升级文件覆盖原来的文件
                        copy($temp,$dest);
                        //echo '文件：'.$temp.'<br>';
                    }
                }
            }
        }
        return true;
    }

    /*
     *  删除更新文件
     *  yyyvy
     *  2018-8-18
     * */
    public function deldir($dir) {
        //先删除目录下的文件：
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if($file != "." && $file!="..") {
                $fullpath = $dir. DS .$file;
                if(!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    $this->deldir($fullpath);
                }
            }
        }
        closedir($dh);

        //删除当前文件夹：
        if(rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

    /*
     *  下载更新包
     *  yyyvy
     *  2018-8-18
     * */
    public function download($url,$tmpFile){
        $options = [
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'X-REQUESTED-WITH: XMLHttpRequest'
            ]
        ];
        $result = curl_request($url, '', 'GET', $options);
        if ($result['status']==true) {
            if ($write = fopen($tmpFile, 'w'))
            {
                fwrite($write, $result['content']);
                fclose($write);
                return $tmpFile;
            }
            throw new \Exception("没有权限写入临时文件",1);
        }
    }


    /**
     * 解压应用压缩包
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-10-25
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public function unzip($file,$dir)
    {
        if (class_exists('ZipArchive'))
        {
            $zip = new ZipArchive;
            if ($zip->open($file) !== TRUE)
            {
                throw new \Exception('无法打开zip文件');
            }
            if (!$zip->extractTo($dir))
            {
                $zip->close();
                throw new \Exception('无法提取文件');
            }
            $zip->close();
            return $dir;
        }
        throw new \Exception("无法执行解压操作，请确保ZipArchive安装正确");
    }
}