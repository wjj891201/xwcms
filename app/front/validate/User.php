<?php

namespace app\front\validate;

use think\Validate;

class User extends Validate
{

    protected $rule = [
        'username' => 'require|length:6,10',
        'password' => 'require',
        'repassword' => 'require|confirm:password',
        'captcha' => 'require|checkCapcha',
    ];
    protected $message = [
        'username' => '用户名必须,请重新输入',
        'username.length' => '用户名长度6-10位',
        'username.unique' => '该账号已存在',
        'password' => '密码必须',
        'repassword.require' => '确认密码必须',
        'repassword.confirm' => '两次密码不一致',
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

    protected $scene = [
        'login' => ['username', 'password', 'captcha'],
        'register' => ['username', 'password', 'repassword', 'captcha']
    ];

    // register 验证场景定义
    public function sceneRegister()
    {
        return $this->append('username', 'unique:user');
    }

}
