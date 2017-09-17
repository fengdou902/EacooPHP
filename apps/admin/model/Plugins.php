<?php
namespace app\admin\model;
use think\Model;
/**
 * 插件模型
 * 该类参考了OneThink的部分实现
 */
class Plugins extends Model {

    protected $insert = ['sort'=>0,'status'=>1];
    // protected $auto = ['update_time'];

    
    /**
     * 插件类型
     */
    public function addon_type($id) {
        $list[0] = '系统插件';
        return $id ? $list[$id] : $list;
    }

    /**
     * 获取插件列表
     * @param string $addon_dir
     */
    public function getAll() {
        //$addon_dir = config('addon_path');
        $addon_dir = ROOT_PATH.'plugins/';
        $dirs = array_map('basename', glob($addon_dir.'*', GLOB_ONLYDIR));
        if ($dirs == false || !file_exists($addon_dir)) {
            $this->error = '插件目录不可读或者不存在';
            return false;
        }
        $addons      = [];
        $map['name'] = ['in', $dirs];
        $list = $this->where($map)
                     ->field(true)
                     ->order('sort asc,id desc')
                     ->select();
        foreach ($list as $addon) {
            $addons[$addon['name']] = $addon->toArray();
        }
        foreach ($dirs as $value) {
            if (!isset($addons[$value])) {
                $class = get_addon_class($value);
                if (!class_exists($class)) {  // 实例化插件失败忽略执行
                    \think\Log::record('插件'.$value.'的入口文件不存在！');
                    continue;
                }
                $addon_obj = new $class;
                $addons[$value] = $addon_obj->info;
                if ($addons[$value]) {
                    $addons[$value]['status'] = -1;  // 未安装
                }
            }
        }
        foreach ($addons as &$val) {
            switch ($val['status']) {
                case '-1':  // 未安装
                    $val['status'] = '<i class="fa fa-trash" style="color:red"></i>';
                    $val['right_button']  = '<a class="label label-success ajax-get" href="'.url('install?addon_name='.$val['name']).'">安装</a>';
                    break;
                case '0':  // 禁用
                    $val['status'] = '<i class="fa fa-ban" style="color:red"></i>';
                    $val['right_button']  = '<a class="label label-info " href="'.url('config',array('id'=>$val['id'])).'">设置</a> ';
                    $val['right_button'] .= '<a class="label label-success ajax-get" href="'.url('setStatus',array('status'=>'resume', 'ids' => $val['id'])).'">启用</a> ';
                    $val['right_button'] .= '<a class="label label-danger ajax-get" href="'.url('uninstall?id='.$val['id']).'">卸载</a> ';
                    if (!empty($val['admin_manage_into'])) {
                        $val['right_button'] .= '<a class="label label-success " href="'.url('adminManage',array('name'=>$val['name'])).'">后台管理</a>';
                    }
                    break;
                case '1':  // 正常
                    $val['status'] = '<i class="fa fa-check" style="color:green"></i>';
                    $val['right_button']  = '<a class="label label-info " href="'.url('config',['id'=>$val['id']]).'">设置</a> ';
                    $val['right_button'] .= '<a class="label label-warning ajax-get" href="'.url('setStatus',['status'=>'forbid', 'ids' => $val['id']]).'">禁用</a> ';
                    $val['right_button'] .= '<a class="label label-danger ajax-get" href="'.url('uninstall?id='.$val['id']).'">卸载</a> ';
                    if (!empty($val['admin_manage_into'])) {
                        $val['right_button'] .= '<a class="label label-success " href="'.url('adminManage',['name'=>$val['name']]).'">后台管理</a>';
                    }
                    break;
            }
        }
        return $addons;
    }

    /**
     * 插件显示内容里生成访问插件的url
     * @param string $url url
     * @param array $param 参数
     */
    public function getAddonUrl($url, $param = array()) {
        $url        = parse_url($url);
        $case       = config('url_case_insensitive');
        $addons     = $case ? parse_name($url['scheme']) : $url['scheme'];
        $controller = $case ? parse_name($url['host']) : $url['host'];
        $action     = trim($case ? strtolower($url['path']) : $url['path'], '/');
        // 解析URL带的参数
        if (isset($url['query'])) {
            parse_str($url['query'], $query);
            $param = array_merge($query, $param);
        }
        // 基础参数
        $params = [
            '_addons'     => $addons,
            '_controller' => $controller,
            '_action'     => $action,
        ];
        $params = array_merge($params, $param); //添加额外参数
        return url(MODULE_MARK . '/Plugin/execute', $params);
    }
}
