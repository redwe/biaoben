<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class XmwhValidate extends Validate
{
    protected $rule = [
        'pname' => 'require',
        'price'  => 'require',
        'platform' => 'require',
        'template'  => 'require',
    ];

    protected $message = [
        'pname.require' => '名称不能为空',
        'price.require'  => '价格不能为空',
        'platform.require' => '检测技术平台',
        'template.require'  => '报告模板名称',
    ];

}