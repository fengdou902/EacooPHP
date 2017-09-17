<?php
namespace Addons\SyncLogin\Model;
use Common\Model\CommonModel;
/**
 * 第三方登陆模型
 */
class SyncLoginModel extends CommonModel{
    /**
     * 数据库表名
     */
    protected $tableName = 'addon_sync_login'; 

    /**
     * 后台列表管理相关定义
     * @author jry <598821125@qq.com>
     */
    public $adminList = array(
        'title' => '第三方登录列表',
        'model' => 'addon_sync_login',
        'search_key'=>'type',
        'order'=>'id desc',
        'map' => null,
        'list_grid' => array(
            'uid' => array(
                'title' => 'UID',
                'type'  => 'text',
            ),
            'type' => array(
                'title' => '类别',
                'type'  => 'text',
            ),
            'openid' => array(
                'title' => 'openid',
                'type'  => 'text',
            ),
            'status' => array(
                 'title' => '状态',
                 'type'  => 'status',
             ),
        ),
        'field' => array( //后台新增、编辑字段
            'uid' => array(
                 'name'  => 'uid',
                 'title' => '用户',
                 'type'  => 'num',
                 'tip'=>'绑定的系统用户ID',
             ),
            'type' => array(
                 'name'  => 'type',
                 'title' => '类别',
                 'type'  => 'select',
                 'tip'=>'第三方账号类型',
                 'options'=>array(
                     'Weixin'=>'Weixin',
                     'Qq'=>'Qq',
                     'Sina'=>'Sina',
                     'Renren'=>'Renren',
                 ),
             ),
            'openid' => array(
                 'name'  => 'openid',
                 'title' => 'openid',
                 'type'  => 'text',
             ),
             'access_token' => array(
                 'name'  => 'access_token',
                 'title' => 'access_token',
                 'type'  => 'text',
             ),
            'refresh_token' => array(
                 'name'  => 'refresh_token',
                 'title' => 'refresh_token',
                 'type'  => 'text',
             ),
        ),
    );

    /**
     * 自动完成规则
     * @author jry <598821125@qq.com>
     */
    protected $_validate = array(
        array('uid', 'require', 'UID不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('type','require','type不能为空！', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('openid','require','openid不能为空！', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('access_token','require','access_token不能为空！', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('refresh_token','require','refresh_token不能为空！', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     * 自动完成规则
     */
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('sort', '0', self::MODEL_INSERT),
        array('status', '1', self::MODEL_INSERT),
    );

    /**
     * 新增SNS登录账号
     */
    public function update($id){
        $token = session('token');
        $user_sns_info = session('user_sns_info');
        $data['uid'] = $id;
        $data['type'] = $user_sns_info['type'];
        $data['openid'] = $token['openid'];
        $data['access_token'] = $token['access_token'];
        $data['refresh_token'] = $token['refresh_token'];
        $data = $this->create($data);
        return $this->add($data);
    }

    /**
     * 根据openid等参数查找同步登录表中的用户信息
     */
    public function getUserByOpenidAndType($openid, $type){
        $condition = array(
            'openid' => $openid,
            'type' => $type,
        );
        return $this->where($condition)->find();
    }

    /**
     * 更新Token
     */
    public function updateTokenByTokenAndType($token, $type){
        $condition = array(
            'openid' => $token['openid'],
            'type' => $type,
        );
        $data['access_token'] = $token['access_token'];
        $data['refresh_token'] = $token['refresh_token'];
        if($this->where($condition)->save($data)){
            return true;
        }
    }
}
