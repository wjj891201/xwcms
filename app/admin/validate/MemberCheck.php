<?php


namespace app\admin\validate;

use think\Validate;

class MemberCheck extends Validate
{
    protected $rule = [
        'username' => 'require',
        'password' => 'require',
        'captcha' => 'require|captcha',
    ];

    protected $message = [
        'username.require' => '用戶名不能為空',
        'password.require' => '密碼不能為空',
        'captcha.require' => '驗證碼不能為空',
        'captcha.captcha' => '驗證碼不正確',
    ];
}
