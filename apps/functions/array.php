<?php 

/**
 * 把返回的数据集转换成Tree
 * @access public
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 */
function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0, $is_count = false) {
    // 创建Tree
    $tree = [];
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = [];
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }

        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId =  $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                    if($is_count==true) $parent['_count'] = count($parent[$child]);
                }
            }
        }
    }
    return $tree;
}

/**
 * 将数据格式化成树形结构
 * @param array $items
 * @return array 
 */
function genTree($items,$pk='id',$pid = 'pid', $child = '_child') {
    $tree = []; //格式化好的树
    foreach ($items as $item)
        if (isset($items[$item[$pid]]))
            $items[$item[$pid]][$child][] = &$items[$item[$pk]];
        else
            $tree[] = &$items[$item[$pk]];
    return $tree;
}

/**
 * 多个数组的笛卡尔积
*
* @param unknown_type $data
*/
function combineDika() {
    $data = func_get_args();
    $data = current($data);
    $cnt = count($data);
    $result = array();
    $arr1 = array_shift($data);
    foreach($arr1 as $key=>$item) 
    {
        $result[] = array($item);
    }       

    foreach($data as $key=>$item) 
    {                                
        $result = combineArray($result,$item);
    }
    return $result;
}

/**
 * 两个数组的笛卡尔积
 * @param  array $arr1 [description]
 * @param  array $arr2 [description]
 * @return [type] [description]
 * @date   2017-08-07
 * @author 赵俊峰 <981248356@qq.com>
 */
function combineArray($arr1 =[],$arr2=[]) {         
    $result = [];
    foreach ($arr1 as $item1) 
    {
        foreach ($arr2 as $item2) 
        {
            $temp = $item1;
            $temp[] = $item2;
            $result[] = $temp;
        }
    }
    return $result;
}

/**
 * 将二维数组以元素的某个值作为键 并归类数组
 * array( array('name'=>'aa','type'=>'pay'), array('name'=>'cc','type'=>'pay') )
 * array('pay'=>array( array('name'=>'aa','type'=>'pay') , array('name'=>'cc','type'=>'pay') ))
 * @param $arr 数组
 * @param $key 分组值的key
 * @return array
 */
function group_same_key($arr,$key){
    $new_arr = array();
    foreach($arr as $k=>$v ){
        $new_arr[$v[$key]][] = $v;
    }
    return $new_arr;
}

/**
 * @param $arr
 * @param $key_name
 * @return array
 * 将数据库中查出的列表以指定的 id 作为数组的键名 
 */
function convert_arr_key($arr, $key_name='id')
{
    $arr2 = [];
    foreach($arr as $key => $val){
        $arr2[$val[$key_name]] = $val;        
    }
    return $arr2;
}

/**
 * 数组 转 对象
 *
 * @param array $arr 数组
 * @return object
 */
 function array_to_object($arr) {
    if (gettype($arr) != 'array') {
        return;
    }
    foreach ($arr as $k => $v) {
        if (gettype($v) == 'array' || getType($v) == 'object') {
            $arr[$k] = (object)array_to_object($v);
        }
    }
 
    return (object)$arr;
 }
 
 /**
 * 对象 转 数组
 *
 * @param object $obj 对象
 * @return array
 */
 function object_to_array($obj) {
    $obj = (array)$obj;
    foreach ($obj as $k => $v) {
        if (gettype($v) == 'resource') {
            return;
        }
        if (gettype($v) == 'object' || gettype($v) == 'array') {
            $obj[$k] = (array)object_to_array($v);
        }
    }
 
    return $obj;
 }

/**
 * array_delete  删除数组中的某个值
 * @param $array
 * @param $value
 * @return mixed
 */
function array_delete($array, $value)
{
    $key = array_search($value, $array);
    if ($key !== false)
        array_splice($array, $key, 1);
    return $array;
}

 // 分析枚举类型配置值 格式 a:名称1,b:名称2
function parse_config_attr($value, $type = null) {
    switch ($type) {
        default: //解析"1:1\r\n2:3"格式字符串为数组
            $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
            if (strpos($value,':')) {
                $value  = array();
                foreach ($array as $val) {
                    list($k, $v) = explode(':', $val);
                    $value[$k]   = $v;
                }
            } else {
                $value = $array;
            }
            break;
    }
    return $value;
}


//array_column()函数兼容低版本PHP
if (!function_exists('array_column')) {
    function array_column($input, $columnKey, $indexKey = null) {
        $columnKeyIsNumber = (is_numeric($columnKey)) ? true : false;
        $indexKeyIsNull = (is_null($indexKey)) ? true : false;
        $indexKeyIsNumber = (is_numeric($indexKey)) ? true : false;
        $result = array();
        foreach ((array) $input as $key => $row) {
            if ($columnKeyIsNumber) {
                $tmp = array_slice($row, $columnKey, 1);
                $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : null;
            } else {
                $tmp = isset($row[$columnKey]) ? $row[$columnKey] : null;
            }
            if (!$indexKeyIsNull) {
                if ($indexKeyIsNumber) {
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && !empty($key)) ? current($key) : null;
                    $key = is_null($key) ? 0 : $key;
                } else {
                    $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                }
            }
            $result[$key] = $tmp;
        }
        return $result;
    }
 }