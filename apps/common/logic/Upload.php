<?php
// 上传处理      
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.eacoophp.com, All rights reserved.         
// +----------------------------------------------------------------------
// | [EacooPHP] 并不是自由软件,可免费使用,未经许可不能去掉EacooPHP相关版权。
// | 禁止在EacooPHP整体或任何部分基础上发展任何派生、修改或第三方版本用于重新分发
// +----------------------------------------------------------------------
// | Author:  心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------

namespace app\common\logic;

use app\common\model\Attachment as AttachmentModel;
use app\common\logic\Attachment as AttachmentLogic;
use think\Request;
use think\Hook;

class Upload {

	protected $request;
	protected $path_type;//路径类型
    protected $isAdmin = 0;//是否后台操作
    protected $doUid = 0;//操作用户ID

	/**
     * 构造函数
     * @param Request $request Request对象
     * @access public
     */
    public function __construct(Request $request = null)
    {
    	if (is_null($request)) {
            $request = Request::instance();
        }
        $this->request = $request;
        $this->attachmentModel = new AttachmentModel();
        //判断是否是后台
        $this->doUid = is_login();
        if (MODULE_MARK=='admin') {
            $this->isAdmin = 1;
            $this->doUid = is_admin_login();
        }
    }

	/**
	 * 上传控制器
	 */
	public function upload($param = []) {
		try {
			$upload_type = $this->request->param('type', 'picture', 'trim');//上传类型包括picture,file,avatar
			$config      = config('attachment_options');
			$config['subName']=['date','Y-m-d'];
			if ($upload_type=='picture') {
				$config['maxSize']  = $config['image_max_size'];
				$config['exts']     = $config['image_exts'];
				$config['saveName'] = $config['image_save_name'];
			} else{
				$config['maxSize']  = $config['file_max_size'];
				$config['exts']     = $config['file_exts'];
				$config['saveName'] = $config['file_save_name'];
			}

	        $this->path_type = $this->request->param('path_type', 'picture', 'trim');//路径类型

			$upload_path = './uploads/'.$this->path_type.'/'.call_user_func_array($config['subName'][0],[$config['subName'][1],time()]);
			// 获取表单上传文件 例如上传了001.jpg
			$file = $this->request->file('file');
			if (!$file) {
				throw new \Exception("file对象文件为空，或缺失环境组件。错误未知，请前往社区反馈",0);
				
			}

			if (!$file->validate(['size'=>$config['maxSize'],'ext'=>$config['exts']])) {//验证通过
				throw new \Exception($file->getError(), 0);
				
			}
			//进行图像处理
			if ($upload_type == 'picture') {
				
				$this->doImage($file);
			}

			$info = $file->rule($config['saveName'])->move($upload_path, true, false);//保存文件
			$upload_info = $this->parseFile($info);
            $upload_info['is_admin'] = $this->isAdmin;
            $upload_info['uid'] = isset($param['uid']) && $param['uid'] ? $param['uid'] : $this->doUid;//设置上传者；如果为空，保存的时候会自动处理为当前用户
            unset($info);   //释放文件，避免上传通文件无法删除
			
			$is_sql = $this->request->param('is_sql', 'on', 'trim');//是否保存入库
			$return = [
				'code'=>1,
				'msg' =>'上传成功',
				'data'=> $is_sql=='on' ? $this->save($config, $upload_type,$upload_info) : $upload_info
			];

			return $return;
		} catch (\Exception $e) {
			setAppLog($e->getMessage(),'error');
			$return = [
					'code' =>$e->getCode(),
					'msg'  =>$e->getMessage(),
					'data' =>[],
				];
			return $return;
		}
		
	}

	/**
	 * 多文件上传（待完成）
	 * @return [type] [description]
	 */
	public function multipleUpload()
	{
		return $return;
	}

	/**
	 * 上传base64文件
	 * @return [type] [description]
	 */
	public function uploadBase64($post_field = 'data',$upload_type = 'picture',$path_type='picture')
	{
		$aData = input('post.'.$post_field);

        if ($aData == '' || $aData == 'undefined') {
            return ['code'=>0,'msg'=>'参数错误'];
        }

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $aData, $result)) {
            $base64_body = substr(strstr($aData, ','), 1);
            empty($aExt) && $aExt = $result[2];
        } else {
            $base64_body = $aData;
        }

        empty($aExt) && $aExt = 'jpg';

		$md5  = md5($base64_body);
		$sha1 = sha1($base64_body);

        $check = $this->attachmentModel->where(['md5' => $md5, 'sha1' => $sha1])->find();

        if (!empty($check)) {//已存在则直接返回信息
        	$check['already']=1;
        	$return = [
				'code' =>1,
				'msg'  =>'文件已存在，信息获取成功！',
				'data' =>$check
        	];

        } else {
            //不存在则上传并返回信息
			$config      = config('attachment_options');
			$config['subName']=['date','Y-m-d'];
			if ($upload_type=='picture') {
				$config['maxSize']  = $config['image_max_size'];
				$config['exts']     = $config['image_exts'];
				$config['saveName'] = $config['image_save_name'];
			} else{
				$config['maxSize']  = $config['file_max_size'];
				$config['exts']     = $config['file_exts'];
				$config['saveName'] = $config['file_save_name'];
			}
			$this->path_type = $path_type;//路径类型
			$savePath = './uploads/'.$this->path_type.'/'.call_user_func_array($config['subName'][0],[$config['subName'][1],time()]);

			$driver   = $config['driver'];
			$saveName = uniqid();
			$path     = $savePath .'/'. $saveName . '.' . $aExt;
            if($driver == 'local'){
                //本地上传
                if (!file_exists($savePath)) {
				    mkdir($savePath, 0777, true);
				}
				$data = base64_decode($base64_body);
				$res  = file_put_contents($path, $data);
            } else {
                $res = false;
                //使用云存储
                $name = get_plugin_class($driver);
                if (class_exists($name)) {
                    $class = new $name();
                    if (method_exists($class, 'uploadBase64')) {
                        $path = $class->uploadBase64($base64_body,$path);
                        $res = true;
                    }
                }
            }
            if ($res) {
            	$info = [
					'create_time' => time(),
					'ext'         => $aExt,
					'name'        => $saveName,
					'alt'        => $saveName,
					'path_type'   => 'picture',
					'mime_type'   => 'image',
					'path'        => str_replace("\\", '/', substr($path, 1)),
					'url'         => cdn_img_url($path),
					'size'        => filesize($path),
					'md5'         => $md5,
					'sha1'        => $sha1,
            	];
                $return['code'] = 1;
                $is_sql = $this->request->param('is_sql', 'on', 'trim');//是否保存入库
				$return['data']   = $is_sql=='on' ? $this->save($config, $upload_type,$info) : $info;
                
            } else {
            	$return = [
					'code' => 0,
					'msg'  => '图片上传失败。',
					'data' => []
            	];

            }
			
        }
        
        return $return;
	}

	/**
     * 上传远程文件
     * @param  string  $url            远程文件地址
     * @param  boolean $download_local 是否同时下载到本地
     * @return [type]                  [description]
     * @author 心云间、凝听 <981248356@qq.com>
     */
	public function uploadRemoteFile($url='', $download_local=false)
	{
		if (!$url) return false;
		$data=[];
        $data['url']=$data['path']=$url;
        //$file_content=file_get_contents($url);
        //$data['md5']  = md5_file($file_content);
        //$data['sha1'] = sha1_file($file_content);
        //$data['size'] = strlen($file_content);
        $data['uid']      = $this->doUid;
        $data['is_admin'] = $this->isAdmin;
        $data['size']     = fsockopen_remote_filesize($url);
        $file_ext         = strrchr($url,'.');
        $data['ext']      = str_replace('.','',$file_ext);//截取格式并替换掉点.
        $data['name']     = str_replace('/','',str_replace($file_ext,'',strrchr($url,'/')));//获取文件名称
        $data['location'] ='link';//外链形式
        if (!$data['ext']||!$data['name']) {
            return false;
        }
        $this->attachmentModel->allowField(true)->data($data)->save();
		$id = $this->attachmentModel->id;

		if ($id>0) {
			$data = logic('common/attachment')->info($id);
			return $data;
		} else {
			return false;
		}
	}

	/**
	 * 上传用户头像
	 * @param  integer $uid           用户ID
	 * @param  integer $method 上传方式配置。method 1:表单，2:base64
	 * @return [type]                 [description]
	 */
	public function uploadAvatar($uid = 0, $method =1,$post_field='data' )
	{
		try {
			if (!$uid) {
				throw new \Exception("用户ID非法", 0);
			}

			$config             = config('attachment_options');
			$config['saveName'] = $config['image_save_name'];
			$upload_path        = './uploads/avatar/'.$uid;
			$driver             = $config['driver'];
			if ($method==1) {//表单提交
				// 获取表单上传文件
				$file = $this->request->file('file');
				$info = $file->validate(['size'=>$config['image_max_size'],'ext'=>$config['image_exts']])->rule($config['saveName'])->move($upload_path, true, false);
				if (empty($info)) {
					throw new \Exception($file->getError(), 0);
					
				}
				$upload_info = $this->parseFile($info);
				
				$return = [
					'code' =>1,
					'msg'  =>'上传成功',
					'data' =>$this->parseFile($info)
				];
				
			} elseif ($method==2) {//base64
				$aData = input('param.'.$post_field);

		        if ($aData == '' || $aData == 'undefined') {
		            throw new \Exception("参数错误", 0);
		            
		        }

		        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $aData, $result)) {
		            $base64_body = substr(strstr($aData, ','), 1);
		            empty($aExt) && $aExt = $result[2];
		        } else {
		            $base64_body = $aData;
		        }

		        empty($aExt) && $aExt = 'jpg';

				$md5  = md5($base64_body);
				$sha1 = sha1($base64_body);

				$savePath = $upload_path;

				$saveName = uniqid();
				$path     = $savePath .'/'. $saveName . '.' . $aExt;

				//本地上传
	            if (!file_exists($savePath)) {
				    mkdir($savePath, 0777, true);
				}
				$data = base64_decode($base64_body);
				$res  = file_put_contents($path, $data);
				if (!$res) {
					throw new \Exception("写入文件失败", 0);
					
				}

	            //使用云存储
	            $name = get_plugin_class($driver);
	            if (class_exists($name)) {
	                $class = new $name();
	                if (method_exists($class, 'uploadBase64')) {
	                    $path = $class->uploadBase64($base64_body,$path);
	                }
	            }
	            $file_size = filesize($path);
	            $path = str_replace("\\", '/', substr($path, 1));
	            $upload_info = [
					'path' => $path,
				];
            	$info = [
					'ext'         => $aExt,
					'name'        => $saveName,
					'alt'         => $saveName,
					'path'        => $path,
					'url'         => cdn_img_url($path),
					'size'        => $file_size,
					'md5'         => $md5,
					'sha1'        => $sha1,
            	];

				$return = [
					'code' => 1,
					'msg'  => '图片上传成功',
					'data' => $info
            	];
  
			}

			// 上传文件钩子，用于阿里云oss、七牛云、又拍云等第三方文件上传的扩展
			$upload_info['driver'] = $driver;
        	hook('UploadFile', $upload_info);
        	return $return;
		} catch (\Exception $e) {
			return $return = [
						'code' => 0,
						'msg'  => $e->getMessage(),
						'data' => []
	            	];
		}

	}
	
	/**
	 * 百度编辑器使用
	 * @var view
	 * @access public
	 */
	public function ueditor() {
		$data = new \eacoo\Ueditor(session('auth_user.uid'));
		echo $data->output();
	}

	public function delete() {
		$data = [
			'code' => 1,
		];
		echo json_encode($data);
		exit();
	}

	/**
	 * 保存上传的信息到数据库
	 * @var view
	 * @access public
	 */
	public function save($config, $from_file_name, $file) {
        $file['is_admin'] = $this->isAdmin;
		$file['uid']      = isset($file['uid']) && $file['uid'] ? $file['uid'] : $this->doUid;
		$file['location'] = $config['driver'];
		$file['code']   = 1;
		$file_exist = AttachmentModel::where(['md5'=>$file['md5'],'sha1'=>$file['sha1']])->count();
		if ($file_exist>0) {//已存在
			unlink(PUBLIC_PATH.$file['path']);//删除存在的文件

			$id = AttachmentModel::where(['md5'=>$file['md5'],'sha1'=>$file['sha1']])->value('id');
			$data            = AttachmentLogic::info($id);;
			$data['already'] = 1;
			$data['msg']     = '文件已存在';
			return $data;
		} else {
        	// 上传文件钩子，用于阿里云oss、七牛云、又拍云等第三方文件上传的扩展
	        if ($config['driver'] != 'local') {
	            // $hook_result = Hook::listen('UploadFile', $file, ['driver' => $config['driver']], true);
	            // if (false !== $hook_result) {
	            //     return $hook_result;
	            // }
	            $file['driver'] = $config['driver'];
	            hook('UploadFile', $file);
	        }
            $file['mime_type'] = isset($file['mime_type']) && $file['mime_type'] ? $file['mime_type'] : AttachmentLogic::fileMimeType($file['ext']);
            $file['create_time'] = date('Y-m-d H:i:s');
			$this->attachmentModel->allowField(true)->isUpdate(false)->data($file)->save();
			$id  = $this->attachmentModel->id;
			if ($id>0) {
				$data = AttachmentLogic::info($id);
				return $data;
			} else {
				return false;
			}
		}

	}

	/**
	 * 处理图片
	 * @param  [type] $file [description]
	 * @return [type] [description]
	 * @date   2018-01-13
	 * @author 心云间、凝听 <981248356@qq.com>
	 */
	public function doImage($file=null)
	{
		$image       = \think\Image::open($file);
		$config      = config('attachment_options');		
		$processing_type    = $this->request->param('processing_type',$config['watermark_type'],'intval');//图像处理类型
		$watermark_scene = intval($config['watermark_scene']);//水印场景
		if ($watermark_scene==2||($watermark_scene==3 && $this->path_type=='picture')||($watermark_scene==4 && $this->path_type=='product')) {
			
			// 图片处理
            switch ($processing_type) {
                // case 1: // 图片裁剪
                //     $image->crop(300, 300);
                //     break;
                // case 2: // 缩略图
                //     $image->thumb(150, 150, Image::THUMB_CENTER);
                //     break;
                // case 3: // 垂直翻转
                //     $image->flip();
                //     break;
                // case 4: // 水平翻转
                //     $image->flip(Image::FLIP_Y);
                //     break;
                // case 5: // 图片旋转
                //     $image->rotate();
                //     break;
                case 6: // 图片水印
                    $image->water($config['water_img'],$config['water_position'], $config['water_opacity']);
                    break;
                case 7: // 文字水印
                    $image->text('EacooPHP', VENDOR_PATH . 'topthink/think-captcha/assets/ttfs/1.ttf', 20, '#ffffff');
                    break;
            }

		}
	}

	/**
	 * 获取文件信息
	 * @param  [type] $info [description]
	 * @return [type]       [description]
	 */
	protected function parseFile($info) {
		$data = [];
		if (!empty($info)) {
			$data['create_time'] = $info->getATime(); //最后访问时间
			//$data['basename']  = $info->getBasename(); //获取无路径的basename
			//$data['c_time']    = $info->getCTime(); //获取inode修改时间
			$data['ext']         = $info->getExtension(); //文件扩展名
			$data['name']        = $data['alt']= str_replace('.'.$data['ext'],'',$info->getInfo()['name']);
			//$data['name']      = $info->getFilename(); //获取文件名
			//$data['m_time']    = $info->getMTime(); //获取最后修改时间
			//$data['owner']     = $info->getOwner(); //文件拥有者
			$data['path_type']   = $this->path_type; //路径类型
			$data['mime_type']   = $info->getMime() ? strstr($info->getMime(),'/',true):''; //文件mime类型
			$data['savepath']    = $info->getPath(); //不带文件名的文件路径
			$data['url']         = $data['path']  = str_replace("\\", '/', substr($info->getPathname(), 1)); //全路径
			$data['size']        = $info->getSize(); //文件大小，单位字节
			$data['md5']         = md5_file($info->getPathname());
			$data['sha1']        = sha1_file($info->getPathname());
		}
		
		return $data;
	}
}