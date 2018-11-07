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

class BbjsValidate extends Validate
{
    protected $rule = [
        'uname' => 'require',
        'sex' => 'require',
        'age' => 'require',
        'mobile'  => 'require',
        'code' => 'require',
    ];
    protected $message = [
        'uname.require' => '姓名不能为空',
        'sex.require'  => '性别不能为空',
        'age.require' => '请输入年龄',
        'mobile.require'  => '手机不能为空',
        'code.require' => '请输入实验室编号',
    ];

    protected $scene = [
        'add'  => ['uname', 'mobile', 'code'],
        'edit' => ['uname', 'mobile'],
    ];
}