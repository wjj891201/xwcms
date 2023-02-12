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
        'username' => '賬戶名必填，請重新輸入',
        'username.length' => '賬戶長度6-10位',
        'username.unique' => '該賬戶已註冊',
        'password' => '密碼必填',
        'repassword' => '確認密碼必填',
        'repassword.confirm' => '兩次密碼不一致',
        'email' => 'Email必填',
        'email.unique' => '該Email已註冊',
        'email.email' => 'Email格式不正確',
        'email_captcha' => 'Email驗證碼必填',
        'captcha' => '圖形驗證碼必填',
    ];

    protected function checkEmailCapcha($value, $rule, $data = [])
    {
        if ($value != Cookie::get('email_captcha'))
        {
            return "Email驗證碼不正確";
        }
        return true;
    }

    protected function checkCapcha($value, $rule, $data = [])
    {
        if (!captcha_check($value))
        {
            return "圖形驗證碼不正確";
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
