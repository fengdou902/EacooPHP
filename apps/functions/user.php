<?php
use app\common\model\User;
use app\admin\model\Action;
use app\admin\model\ActionLog;

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_login() {
	return User::isLogin();
}

/**
 * 检测当前用户是否为管理员
 * @return boolean true-管理员，false-非管理员
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function is_administrator($uid = null) {
	$uid = is_null($uid) ? is_login() : $uid;
	return $uid && (intval($uid) === config('user_administrator'));
}

/**
 * 根据用户ID获取用户信息
 * @param  integer $id 用户ID
 * @return array  用户信息
 */
function get_user_info($uid) {
    if ($uid>0) {
        return User::info($uid);
    }
    return false;
    
}

/**
 * 获取用户名
 * @param  integer $uid [description]
 * @return [type] [description]
 * @date   2017-09-25
 * @author 心云间、凝听 <981248356@qq.com>
 */
function get_nickname($uid=0)
{
    if ($uid>0) {
        return User::where('uid',$uid)->value('nickname');
    }
    return false;
}

/**
 * 数据签名认证
 * @param  array $data 被认证的数据
 * @return string       签名
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function data_auth_sign($data)
{
    //数据类型检测
    if (!is_array($data)) {
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}


if (!function_exists('action_log')) {
    /**
     * 记录行为日志，并执行该行为的规则
     * @param null $action 行为标识
     * @param null $model 触发行为的模型名
     * @param string $record_id 触发行为的记录id
     * @param null $uid 执行行为的用户id
     * @param int $action_type 执行类型。1自定义操作，2记录操作
     * @param string $details 详情
     * @author huajie <banhuajie@163.com>
     * @return bool|string
     */
    function action_log($action = null, $model = null, $record_id = '', $uid = null, $action_type = 1, $details = '')
    {
        // 参数检查
        if(empty($action)){
            return '参数不能为空';
        }
        if(empty($uid)){
            $uid = is_login();
        }
        // if (strpos($action, '.')) {
        //     list($module, $action) = explode('.', $action);
        // } else {
        //     $module = request()->module();
        // }

        // 查询行为,判断是否执行
        $action_info = Action::get(['name'=>$action]);
        if($action_info['status'] != 1){
            return '该行为被禁用或删除';
        }

        // 插入行为日志
        $data = [
            'action_id'   => $action_info['id'],
            'uid'         => $uid,
            'action_ip'   => get_client_ip(),
            'model'       => $model,
            'record_id'   => $record_id,
            'create_time' => request()->time()
        ];

        // 解析日志规则,生成日志备注
        if(!empty($action_info['log'])){
            if(preg_match_all('/\[(\S+?)\]/', $action_info['log'], $match)){
                $log = [
                    'user'    => $uid,
                    'record'  => $record_id,
                    'model'   => $model,
                    'time'    => request()->time(),
                    'data'    => ['user' => $uid, 'model' => $model, 'record' => $record_id, 'time' => request()->time()],
                    'details' => $details
                ];

                $replace = [];
                foreach ($match[1] as $value){
                    $param = explode('|', $value);
                    if(isset($param[1])){
                        $replace[] = call_user_func($param[1], $log[$param[0]]);
                    }else{
                        $replace[] = $log[$param[0]];
                    }
                }

                $data['remark'] = str_replace($match[0], $replace, $action_info['log']);
            } else {
                $data['remark'] = $action_info['log'];
            }
        } else {
            // 未定义日志规则，记录操作url
            $data['remark'] = '操作url：'.$_SERVER['REQUEST_URI'];
        }

        // 保存日志
        model('admin/ActionLog')->insert($data);

        if(!empty($action_info['rule'])){
            // 解析行为
            $rules = parse_action($action, $uid);
            // 执行行为
            $res = execute_action($rules, $action_info['id'], $uid);
            if (!$res) {
                return '执行行为失败';
            }
        }

        return true;
    }
}

if (!function_exists('parse_action')) {
    /**
     * 解析行为规则
     * 规则定义  table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
     * 规则字段解释：table->要操作的数据表，不需要加表前缀；
     *            field->要操作的字段；
     *            condition->操作的条件，目前支持字符串，默认变量{$self}为执行行为的用户
     *            rule->对字段进行的具体操作，目前支持四则混合运算，如：1+score*2/2-3
     *            cycle->执行周期，单位（小时），表示$cycle小时内最多执行$max次
     *            max->单个周期内的最大执行次数（$cycle和$max必须同时定义，否则无效）
     * 单个行为后可加 ； 连接其他规则
     * @param string $action 行为id或者name
     * @param int $self 替换规则里的变量为执行用户的id
     * @author huajie <banhuajie@163.com>
     * @return boolean|array: false解析出错 ， 成功返回规则数组
     */
    function parse_action($action = null, $self){
        if(empty($action)){
            return false;
        }

        // 参数支持id或者name
        if(is_numeric($action)){
            $map = ['id' => $action];
        }else{
            $map = ['name' => $action];
        }

        // 查询行为信息
        $info = Action::where($map)->find();
        if(!$info || $info['status'] != 1){
            return false;
        }

        // 解析规则:table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
        $rule   = $info['rule'];
        $rule   = str_replace('{$self}', $self, $rule);
        $rules  = explode(';', $rule);
        $return = [];
        foreach ($rules as $key => &$rule){
            $rule = explode('|', $rule);
            foreach ($rule as $k => $fields){
                $field = empty($fields) ? array() : explode(':', $fields);
                if(!empty($field)){
                    $return[$key][$field[0]] = $field[1];
                }
            }
            // cycle(检查周期)和max(周期内最大执行次数)必须同时存在，否则去掉这两个条件
            if (!isset($return[$key]['cycle']) || !isset($return[$key]['max'])) {
                unset($return[$key]['cycle'],$return[$key]['max']);
            }
        }

        return $return;
    }
}

if (!function_exists('execute_action')) {
    /**
     * 执行行为
     * @param array|bool $rules 解析后的规则数组
     * @param int $action_id 行为id
     * @param array $uid 执行的用户id
     * @author huajie <banhuajie@163.com>
     * @return boolean false 失败 ， true 成功
     */
    function execute_action($rules = false, $action_id = null, $uid = null){
        if(!$rules || empty($action_id) || empty($uid)){
            return false;
        }

        $return = true;
        foreach ($rules as $rule){
            // 检查执行周期
            $map = ['action_id' => $action_id, 'uid' => $uid];
            $map['create_time'] = ['gt', request()->time() - intval($rule['cycle']) * 3600];
            $exec_count = ActionLog::where($map)->count();
            if($exec_count > $rule['max']){
                continue;
            }

            // 执行数据库操作
            $field = $rule['field'];
            $res   = db($rule['table'])->where($rule['condition'])->setField($field, array('exp', $rule['rule']));

            if(!$res){
                $return = false;
            }
        }
        return $return;
    }
}