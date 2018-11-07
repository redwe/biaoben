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

class MemberValidate extends Validate
{
    protected $rule = [
        'user_com' => 'require|unique:member,user_com',
        'user_name' => 'require|unique:member,user_name',
        'mobile'  => 'require|unique:Member,mobile',
        'user_email' => 'require|email|unique:Member,user_email',
    ];
    protected $message = [
        'user_name.require' => '用户不能为空',
        'user_name.unique'  => '用户名已存在',
        'user_com.require' => '公司名不能为空',
        'user_com.unique'  => '公司名已存在',
        'mobile.require'  => '手机不能为空',
        'user_email.require' => '邮箱不能为空',
        'user_email.email'   => '邮箱不正确',
        'user_email.unique'  => '邮箱已经存在',
    ];

    protected $scene = [
        'add'  => ['user_com', 'user_name', 'user_email'],
        'edit' => ['user_com', 'user_email'],
    ];
}