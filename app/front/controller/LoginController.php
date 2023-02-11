<?php

namespace app\front\controller;

use think\facade\View;
use app\front\validate\User as UserValidate;
use app\front\service\User as UserService;

class LoginController extends FrontBaseController
{

    public function initialize()
    {
        if ($this->isLogin())
        {
            return $this->redirect(url("index/index"));
        }
    }

    public function index()
    {
        View::assign('title', '登陆');
        return View::fetch();
    }

    public function check()
    {
        if (!$this->request->isPost())
        {
            return output(0, "请求方式错误");
        }

        $username = $this->request->param("username", "", "trim");
        $password = $this->request->param("password", "", "trim");
        $captcha = $this->request->param("captcha", "", "trim");

        $data = [
            'username' => $username,
            'password' => $password,
            'captcha' => $captcha,
        ];
        $validate = new UserValidate();
        if (!$validate->scene('login')->check($data))
        {
            return output(0, $validate->getError());
        }
        try
        {
            $result = (new UserService)->login($data);
        } catch (\Exception $e)
        {
            return output(0, $e->getMessage());
        }

        if ($result)
        {
            return output(1, "登录成功");
        }
        else
        {
            return output(0, "登录失败");
        }
    }

}
