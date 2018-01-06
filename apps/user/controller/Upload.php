<?php
/**
 * Created by PhpStorm.
 * User: Xiaoya
 * Date: 2018-01-01
 * Time: 0:32
 */
namespace app\user\controller;
use app\home\controller\Home;

class Upload extends Home{
  /**
   * 上传头像
   * @author yyyvy <76836785@qq.com>
   * @Time 2018-1-1 00:33:49
   */
  public function uploadAvatar(){

    $uid = input('param.uid',0,'intval');

    $controller = controller('common/Upload');
    $return = $controller->uploadAvatar($uid);

    return json($return);
  }
}