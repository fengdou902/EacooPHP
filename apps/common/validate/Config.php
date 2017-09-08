<?php
namespace app\common\validate;

use think\Validate;

class Config extends Validate
{
    // 验证规则
    protected $rule = [
        'name'   => 'require',
        'title'  => 'require',
        'type'   => 'require',
        'sort'   => 'number',
        'group'  => 'require',
        'status' => 'number',
        'value'  => 'require',
    ];

    protected $message = [
        'name.require'   => '配置标识不能为空',
        'title.require'  => '标识说明不能为空',
        'sort.number'    => '排序必须为数字',
        'type.require'   => '配置类型不能为空',
        'type.number'    => '配置类型必须为数字',
        'group.require'  => '配置分组不能为空',
        'group.number'   => '配置分组必须为数字',
        'status.require' => '是否显示不得为空',
        'status.number'  => '是否显示必须为数字',
        'value.require'  => '配置值不能为空',
    ];

    protected $scene=[
        'edit' => ['name','title','sort','type','group','status','value','remark'],
    ];
}