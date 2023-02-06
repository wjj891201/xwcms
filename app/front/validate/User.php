<?php

namespace app\front\validate;

use think\Validate;

class User extends Validate
{

    protected $rule = [
        'username' => 'require',
        'password' => 'require',
        'captcha' => 'require|checkCapcha',
    ];
    protected $message = [
        'username' => '用户名必须,请重新输入',
        'password' => '密码必须',
        'captcha' => '验证码必须',
    ];

    protected function checkCapcha($value, $rule, $data = [])
    {
        if (!captcha_check($value))
        {
            return "您输入的验证码不正确";
        }
        return true;
    }

}
