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
    static public $pluginName;
    //插件目录
    static public $pluginDir = ROOT_PATH.'plugins/'; 
    //安装描述文件名
    static public $infoFile = 'info.json';

    //安装菜单文件名
    static public $menusFile = 'menus.php';

    //安装选项文件名
    static public $optionsFile = 'options.php';

    /**
     * 插件类型
     */
    public function plugin_type($id) {
        $list[0] = '系统插件';
        return $id ? $list[$id] : $list;
    }

    /**
     * 获取插件列表
     * @param string $plugin_dir
     */
    public function getAll() {
        //$plugin_dir = config('plugin_path');
        $plugin_dir = self::$pluginDir;
        $dirs = array_map('basename', glob($plugin_dir.'*', GLOB_ONLYDIR));
        if ($dirs == false || !file_exists($plugin_dir)) {
            $this->error = '插件目录不可读或者不存在';
            return false;
        }
        $plugins      = [];
        $map['name'] = ['in', $dirs];
        $list = $this->where($map)
                     ->field(true)
                     ->order('sort asc,id desc')
                     ->select();
        foreach ($list as $plugin) {
            $plugins[$plugin['name']] = $plugin->toArray();
        }
        foreach ($dirs as $value) {
            if (!isset($plugins[$value])) {
                $info = $this->getInfoByFile($value);
                $info_flag = $this->checkInfoFile($value);
                if (!$info || !$info_flag) {
                    \think\Log::record('插件'.$value.'的信息缺失！');
                    continue;
                }

                $plugins[$value] = $info;
                if ($plugins[$value]) {
                    $plugins[$value]['status'] = -1;  // 未安装
                }
            }
        }
        
        foreach ($plugins as &$val) {
            switch ($val['status']) {
                case '-1':  // 未安装
                    $val['status'] = '<i class="fa fa-trash" style="color:red"></i>';
                    $val['right_button']  = '<a class="label label-success ajax-get" href="'.url('install?name='.$val['name']).'">安装</a>';
                    break;
                case '0':  // 禁用
                    $val['status'] = '<i class="fa fa-ban" style="color:red"></i>';
                    $val['right_button']  = '<a class="label label-info " href="'.url('config',array('id'=>$val['id'])).'">设置</a> ';
                    $val['right_button'] .= '<a class="label label-success ajax-get" href="'.url('setStatus',array('status'=>'resume', 'ids' => $val['id'])).'">启用</a> ';
                    $val['right_button'] .= '<a class="label label-danger" href="'.url('uninstallBefore?id='.$val['id']).'">卸载</a> ';
                    if (!empty($val['admin_manage_into'])) {
                        $val['right_button'] .= '<a class="label label-success " href="'.url('adminManage',array('name'=>$val['name'])).'">后台管理</a>';
                    }
                    break;
                case '1':  // 正常
                    $val['status'] = '<i class="fa fa-check" style="color:green"></i>';
                    $val['right_button']  = '<a class="label label-info " href="'.url('config',['id'=>$val['id']]).'">设置</a> ';
                    $val['right_button'] .= '<a class="label label-warning ajax-get" href="'.url('setStatus',['status'=>'forbid', 'ids' => $val['id']]).'">禁用</a> ';
                    $val['right_button'] .= '<a class="label label-danger" href="'.url('uninstallBefore?id='.$val['id']).'">卸载</a> ';
                    if (!empty($val['admin_manage_into'])) {
                        $val['right_button'] .= '<a class="label label-success " href="'.url('adminManage',['name'=>$val['name']]).'">后台管理</a>';
                    }
                    break;
            }
        }
        return $plugins;
    }

    /**
     * 插件显示内容里生成访问插件的url
     * @param string $url url
     * @param array $param 参数
     */
    public function getPluginUrl($url, $param = array()) {
        $url        = parse_url($url);
        $case       = config('url_case_insensitive');
        $plugins    = $case ? parse_name($url['scheme']) : $url['scheme'];
        $controller = $case ? parse_name($url['host']) : $url['host'];
        $action     = trim($case ? strtolower($url['path']) : $url['path'], '/');
        // 解析URL带的参数
        if (isset($url['query'])) {
            parse_str($url['query'], $query);
            $param = array_merge($query, $param);
        }
        // 基础参数
        $params = [
            '_plugins'     => $plugins,
            '_controller' => $controller,
            '_action'     => $action,
        ];
        $params = array_merge($params, $param); //添加额外参数
        return url(MODULE_MARK . '/Plugin/execute', $params);
    }

    /**
     * 检测信息
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-09-18
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function checkInfoFile($name='') {
        if ($name=='') {
            $name = self::$pluginName;
        }
        $info_check_keys = ['name', 'title', 'description', 'author', 'version'];
        foreach ($info_check_keys as $value) {
            if (!array_key_exists($value, self::getInfoByFile($name))) {
                return false;
            }

        }
        return true;
    }

    /**
     * 获取插件依赖的钩子
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-09-18
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getDependentHooks($name='')
    {
        if ($name=='' || !$name) {
            return false;
        }
        $plugin_class = get_plugin_class($name);//获取插件名
        if (!class_exists($plugin_class)) {
            $this->error = "未实现{$name}插件的入口文件";
            return false;
        }
        $plugin_obj = new $plugin_class;
        // $info = self::getInfoByFile($name);
        // $dependent_hooks = !empty($info['dependences']['hooks']) ? $info['dependences']['hooks']:'';
        $dependent_hooks = $plugin_obj->hooks;
        return $dependent_hooks;
    }

    /**
     * 文件获取模块信息
     * @param  [type] $name [description]
     * @return [type] [description]
     * @date   2017-09-15
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getInfoByFile($name = '')
    {
        if ($name=='' || !$name) {
            return false;
        }
        $info_file = realpath(self::$pluginDir.$name).'/install/'.self::$infoFile;
        if (is_file($info_file)) {
            $module_info = file_get_contents($info_file);
            $module_info = json_decode($module_info,true);
            return $module_info;
        } else {
            return [];
        }

    }

    /**
     * 文件获取安装信息的后台菜单
     * @param  string $name 模块名
     * @return [type] [description]
     * @date   2017-09-16
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getAdminMenusByFile($name='')
    {
        if ($name=='' || !$name) {
            return false;
        }
        $file = realpath(self::$pluginDir.$name).'/install/'.self::$menusFile;

        if (is_file($file)) {

            $module_menus = include $file;

            return !empty($module_menus['admin_menus']) ? $module_menus['admin_menus'] : false;

        } else {
            return false;
        }
    }

    /**
     * 文件获取安装的前台导航
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-09-16
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getNavigationByFile($name='')
    {
        if ($name=='' || !$name) {
            return false;
        }
        $file = realpath(self::$pluginDir.$name).'/install/'.self::$menusFile;

        if (is_file($file)) {

            $module_menus = include $file;

            return !empty($module_menus['navigation']) ? $module_menus['navigation'] : false;

        } else {
            return false;
        }
    }

    /**
     * 文件获取安装的后台选项
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-09-16
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getOptionsByFile($name ='')
    {
        if ($name=='' || !$name) {
            return false;
        }
        $file = realpath(self::$pluginDir.$name).'/install/'.self::$optionsFile;

        if (is_file($file)) {

            $module_menus = include $file;

            return $module_menus;

        } else {
            return false;
        }
    }

    /**
     * 获取插件默认配置
     * @param  string $name [description]
     * @return [type] [description]
     * @date   2017-09-18
     * @author 心云间、凝听 <981248356@qq.com>
     */
    public static function getDefaultConfig($name ='')
    {
        if ($name=='') {
            $name = self::$pluginName;
        }

        $config = [];
        if ($name) {
            $options = self::getOptionsByFile($name);
            if (!empty($options) && is_array($options)) {
                $config = [];
                foreach ($options as $key => $value) {
                    if ($value['type'] == 'group') {
                        foreach ($value['options'] as $gkey => $gvalue) {
                            foreach ($gvalue['options'] as $ikey => $ivalue) {
                                $config[$ikey] = $ivalue['value'];
                            }
                        }
                    } else {
                        $config[$key] = $options[$key]['value'];
                    }
                }
            }
        }
        return $config;
    }
}
