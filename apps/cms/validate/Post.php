<?php
namespace app\common\validate;

use think\Validate;

class Post extends Validate
{
    // 验证规则
    protected $rule = [
        'title'       => 'require|length:1,30',
        'slug'        => 'alphaDash',
        'type'        => 'require|alphaDash|length:1,20',
        'source'      => 'chsDash|length:1,20',
        //'excerpt'     => 'chsDash',
        //'content'     => 'require',
        'author_id'   => 'number|gt:0',
        'img'         => 'number|gt:0',
        'istop'       => 'number',
        'recommended' => 'number',
    ];

    protected $message = [
        'title.require'       => '标题不能为空',
        'title.length'       => '标题长度不正确',
        'slug.alphaDash'      => '别名格式不正确',
        'type.require'        => '类型不能为空',
        'type.alphaDash'      => '类型格式不正确',
        'type.length'         => '类型长度为1-30位',
        'source.length'       => '来源长度为1-20位',
        'source.chsDash'      => '来源格式不正确', 
        'author_id.number'    =>'作者ID必须为大于0数字',
        'author_id.gt'        =>'作者ID必须为大于0数字',
        'img.number'          =>'作者ID必须为大于0数字',
        'img.gt'              =>'作者ID必须为大于0数字',
        'istop.number'       => '是否置顶格式不正确',
        'recommended.number' => '是否推荐格式不正确',

    ];

    protected $scene=[
        'edit' => ['title','slug','type','source','author_id','img','istop','recommended'],
    ];
}