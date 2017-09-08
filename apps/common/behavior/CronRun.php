<?php 
//定时任务行为
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://www.eacoo123.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------
namespace app\common\behavior;

class CronRun {

	 public function run(&$params) {
		if (config('cron_config_on')) { 
	          // 锁定自动执行
	        $lockfile	 =	 RUNTIME_PATH.'cron.lock';
	        if(is_writable($lockfile) && filemtime($lockfile) > $_SERVER['REQUEST_TIME'] - config('cron_max_time',null,60)) {
	            return ;
	        } else {
	            touch($lockfile);
	        }
	        set_time_limit(1000);
	        ignore_user_abort(true);

	        // 载入cron配置文件
	        // 格式 return array(
	        // 'cronname'=>array('filename',intervals,nextruntime),...
	        // );
	        if(is_file(RUNTIME_PATH.'~crons.php')) {
	            $crons	=	include RUNTIME_PATH.'~crons.php';
	        }elseif(is_file(APP_PATH.'crons.php')){
	            $crons	=	include APP_PATH.'crons.php';
	        }
	        if(isset($crons) && is_array($crons)) {
	            $update	 =	 false;
	            $log	=	[];
	            foreach ($crons as $key=>$cron){
	                if(empty($cron[2]) || $_SERVER['REQUEST_TIME']>=$cron[2]) {
	                    // 到达时间 执行cron文件
	                    G('cronStart');
	                    include APP_PATH.'cron/'.$cron[0].'.php';
	                    G('cronEnd');
	                    $_useTime	 =	 G('cronStart','cronEnd', 6);
	                    // 更新cron记录
	                    $cron[2]	=	$_SERVER['REQUEST_TIME']+$cron[1];
	                    $crons[$key]	=	$cron;
	                    $log[] = "Cron:$key Runat ".date('Y-m-d H:i:s')." Use $_useTime s\n";
	                    $update	 =	 true;
	                }
	            }
	            if($update) {
	                // 记录Cron执行日志
	                \think\Log::write(implode('',$log));
	                // 更新cron文件
	                $content  = "<?php\nreturn ".var_export($crons,true).";\n?>";
	                file_put_contents(RUNTIME_PATH.'~crons.php',$content);
	            }
	        }
	        // 解除锁定
	        unlink($lockfile);
	        return ;
        } 
	 }

}