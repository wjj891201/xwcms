<?php

namespace app\front\validate;

use think\Validate;
use think\facade\Cookie;

class User extends Validate
{

    protected $rule = [
        'username' => 'require|length:6,10',
        'password' => 'require',
        'repassword' => 'require|confirm:password',
        'email' => 'require|email',
        'email_captcha' => 'require|checkEmailCapcha',
        'captcha' => 'require|checkCapcha',
    ];
    protected $message = [
        'username' => '用户名必须,请重新输入',
        'username.length' => '用户名长度6-10位',
        'username.unique' => '该账号已注册',
        'password' => '密码必须',
        'repassword' => '确认密码必须',
        'repassword.confirm' => '两次密码不一致',
        'email' => 'Email必须',
        'email.unique' => '该Email已注册',
        'email.email' => 'Email格式不正确',
        'email_captcha' => 'Email验证码必填',
        'captcha' => '验证码必须',
    ];

    protected function checkEmailCapcha($value, $rule, $data = [])
    {
        if ($value != Cookie::get('email_captcha'))
        {
            return "Email验证码不正确";
        }
        return true;
    }

    protected function checkCapcha($value, $rule, $data = [])
    {
        if (!captcha_check($value))
        {
            return "验证码不正确";
        }
        return true;
    }

    protected $scene = [
        'login' => ['username', 'password', 'captcha'],
        'register' => ['username', 'password', 'repassword', 'email', 'email_captcha', 'captcha'],
        'forget' => ['email', 'email_captcha', 'password'],
    ];

    // register 验证场景定义
    public function sceneRegister()
    {
        return $this->append('username', 'unique:user')->append('email', 'unique:user');
    }

}
