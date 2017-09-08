<?php
// 上传控制器       
// +----------------------------------------------------------------------
// | PHP version 5.4+                
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2016 http://www.eacoomall.com, All rights reserved.
// +----------------------------------------------------------------------
// | Author: 心云间、凝听 <981248356@qq.com>
// +----------------------------------------------------------------------

namespace app\common\controller;

use app\common\model\Attachment;
use think\Request;

class Upload {

	protected $request;
	protected $path_type;
	/**
     * 架构函数
     * @param Request $request Request对象
     * @access public
     */
    public function __construct(Request $request = null)
    {
    	if (is_null($request)) {
            $request = Request::instance();
        }
        $this->request = $request;
        $this->attachment_model = new Attachment();
    }

	/**
	 * 上传控制器
	 */
	public function upload() {
		
		$upload_type = $this->request->param('uploadtype', 'picture', 'trim');//上传类型包括picture,file,avatar
		$config      = config($upload_type.'_upload');

        $this->path_type = $this->request->param('path_type', 'picture', 'trim');//路径类型

        // 上传文件钩子，用于七牛云、又拍云等第三方文件上传的扩展
        //hook('UploadFile', $upload_type);
        
        $rootPath = $this->path_type!='picture' && $this->path_type ? './uploads/'.$this->path_type : $config['rootPath'];
		$upload_path = $rootPath.'/'.call_user_func_array($config['subName'][0],[$config['subName'][1],time()]);
		// 获取表单上传文件 例如上传了001.jpg
		$file = $this->request->file('file');

		if ($file->validate(['size'=>$config['maxSize'],'ext'=>$config['exts']])) {//验证通过
			//进行图像处理
			if ($upload_type == 'picture') {
				$image              = \think\Image::open($file);
				
				$attachment_options = json_decode(config('attachment_options'),true);//获取附件配置值
				$processing_type    = $this->request->param('processing_type',$attachment_options['watermark_type'],'intval');//图像处理类型
				$watermark_scene = intval($attachment_options['watermark_scene']);//水印场景
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
	                    $image->water($attachment_options['water_img'],$attachment_options['water_position'], $attachment_options['water_opacity']);
	                    break;
	                case 7: // 文字水印
	                    $image->text('EacooMall', VENDOR_PATH . 'topthink/think-captcha/assets/ttfs/1.ttf', 20, '#ffffff');
	                    break;
	            }

				}
				
			}

			$info = $file->rule($config['saveName'])->move($upload_path, true, false);//保存文件
			$upload_info = $this->parseFile($info);
			
			$is_sql = $this->request->param('is_sql', 'on', 'trim');//是否保存入库
			$return = [
				'code'=>1,
				'msg' =>'上传成功',
				'data'=> $is_sql=='on' ? $this->save($config, $upload_type,$upload_info) : $upload_info
			];
		} else {
			$return = [
				'code' =>0,
				'msg'  =>$file->getError(),
				'data' =>[],
			];
		}

		return $return;
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

        $check = $this->attachment_model->where(['md5' => $md5, 'sha1' => $sha1])->find();

        if (!empty($check)) {//已存在则直接返回信息
        	$check['already']=1;
        	$return = [
				'code' =>1,
				'msg'  =>'文件已存在，信息获取成功！',
				'data' =>$check
        	];

        } else {
            //不存在则上传并返回信息
			$config          = config($upload_type.'_upload');
			$this->path_type = $path_type;//路径类型

	        // 上传文件钩子，用于七牛云、又拍云等第三方文件上传的扩展
	        //hook('UploadFile', $upload_type);
	        
	        $rootPath = $this->path_type!='picture' && $this->path_type ? './uploads/'.$this->path_type : $config['rootPath'];
			$savePath = $rootPath.'/'.call_user_func_array($config['subName'][0],[$config['subName'][1],time()]);

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
                $name = get_addon_class($driver);
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
					'url'         => render_picture_path($path),
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
	public function uploadRemoteFile($url='',$download_local=false)
	{
		if (!$url) return false;
		$data=[];
        $data['url']=$data['path']=$url;
        //$file_content=file_get_contents($url);
        //$data['md5']  = md5_file($file_content);
        //$data['sha1'] = sha1_file($file_content);
        //$data['size'] = strlen($file_content);
        $data['size']     = fsockopen_remote_filesize($url);
        $file_ext         = strrchr($url,'.');
        $data['ext']      = str_replace('.','',$file_ext);//截取格式并替换掉点.
        $data['name']     = str_replace('/','',str_replace($file_ext,'',strrchr($url,'/')));//获取文件名称
        $data['location'] ='link';//外链形式
        if (!$data['ext']||!$data['name']) {
            return false;
        }
        $this->attachment_model->allowField(true)->data($file)->save();
		$id = $this->attachment_model->id;

		if ($id>0) {
			$data = $this->attachment_model->info($id);
			return $data;
		} else {
			return false;
		}
	}

	/**
	 * 上传用户头像
	 * @param  integer $uid           用户ID
	 * @param  integer $config 上传方式配置。method 1:文件，2:base64
	 * @return [type]                 [description]
	 */
	public function uploadAvatar($uid = 0, $upload_config = ['method'=>1])
	{
		if (!$uid) return false;
		$config = config('avatar_upload');
		$config = array_merge($config,$upload_config);
		// 上传文件钩子，用于七牛云、又拍云等第三方文件上传的扩展
        //hook('UploadFile', $uploadtype);
		$upload_path = $config['rootPath'].'/'.$uid;
		if ($config['method']==1) {//表单提交
			// 获取表单上传文件
			$file = $this->request->file('file');
			$info = $file->validate(['size'=>$config['maxSize'],'ext'=>$config['exts']])->rule($config['saveName'])->move($upload_path, true, false);
			if (!empty($info)) {
				$upload_info = $this->parseFile($info);
				//阿里云OSS
				if (config('aliyun_oss.enable')==1) {
					oss_upload($upload_info['path']);
				}
				$return = [
					'code' =>1,
					'msg'  =>'上传成功',
					'data' =>$this->parseFile($info)
				];
			} else {
				$return = [
					'code' =>0,
					'msg'  =>$file->getError(),
					'data' =>[]
				];
			}
		} elseif ($config['method']==2) {//base64
			$aData = input('post.'.$config['post_field']);

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

			$savePath = $upload_path;

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
				//阿里云OSS
				if (config('aliyun_oss.enable')==1) {
					oss_upload($path);
				}
            } else {
                $res = false;
                //使用云存储
                $name = get_addon_class($driver);
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
					'url'         => render_picture_path($path),
					'size'        => filesize($path),
					'md5'         => $md5,
					'sha1'        => $sha1,
            	];
				$return = [
					'code' => 1,
					'msg'  => '图片上传成功',
					'data' => $info
            	];
                
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
	 * 百度编辑器使用
	 * @var view
	 * @access public
	 */
	public function ueditor() {
		$data = new \com\Ueditor(session('auth_user.uid'));
		echo $data->output();
	}

	public function editor() {
		$callback        = $this->request->get('callback');
		$CKEditorFuncNum = $this->request->get('CKEditorFuncNum');
		$file            = $this->request->file('upload');
		$info            = $file->move(config('editor_upload.rootPath'), true, false);
		if ($info) {
			$fileInfo = $this->parseFile($info);
			$data = [
				"originalName" => $fileInfo['name'],
				"name"         => $fileInfo['name'],
				"url"          => $fileInfo['url'],
				"size"         => $fileInfo['size'],
				"type"         => $fileInfo['ext'],
				"state"        => 'SUCCESS'
			];
		} else {
			$data['state'] = $file->getError();
		}
		/**
		* 返回数据
		*/
		if($callback) {
			return '<script>'.$callback.'('.json_encode($data).')</script>';
		}elseif($CKEditorFuncNum) {
			return '<script>window.parent.CKEDITOR.tools.callFunction("'.$CKEditorFuncNum.'","'.$fileInfo['url'].'","'.$data['state'].'");</script>';
		} else {
			return json_encode($data);
		}
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
		$file['uid']      = is_login();
		$file['location'] = $config['driver'];
		$file['code']   = 1;
		$file_exist = Attachment::where(['md5'=>$file['md5'],'sha1'=>$file['sha1']])->count();

		if ($file_exist>0) {//已存在
			unlink(PUBLIC_PATH.$file['path']);//删除存在的文件

			$id = Attachment::where(['md5'=>$file['md5'],'sha1'=>$file['sha1']])->value('id');
			$data            = Attachment::info($id);;
			$data['already'] =1;
			$data['msg']     ='文件已存在';
			return $data;
		} else {
			//阿里云OSS
			if (config('aliyun_oss.enable')==1) {
				oss_upload($file['path']);
			}
			$this->attachment_model->allowField(true)->isUpdate(false)->data($file)->save();
			$id  = $this->attachment_model->id;
			if ($id>0) {
				$data = $this->attachment_model->info($id);
				return $data;
			} else {
				return false;
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
			//$data['basename']    = $info->getBasename(); //获取无路径的basename
			//$data['c_time']      = $info->getCTime(); //获取inode修改时间
			$data['ext']         = $info->getExtension(); //文件扩展名
			$data['name']    	 = $data['alt']= str_replace('.'.$data['ext'],'',$info->getInfo()['name']);
			//$data['name']        = $info->getFilename(); //获取文件名
			//$data['m_time']      = $info->getMTime(); //获取最后修改时间
			//$data['owner']       = $info->getOwner(); //文件拥有者
			$data['path_type']    = $this->path_type; //文件拥有者
			$data['mime_type']    = $info->getMime() ? strstr($info->getMime(),'/',true):''; //文件mime类型
			$data['savepath']    = $info->getPath(); //不带文件名的文件路径
			$data['url']         = $data['path']         = str_replace("\\", '/', substr($info->getPathname(), 1)); //全路径
			$data['size']        = $info->getSize(); //文件大小，单位字节
			$data['md5']         = md5_file($info->getPathname());
			$data['sha1']        = sha1_file($info->getPathname());
		}
		
		return $data;
	}
}