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
        return View::fetch();
    }

    public function check()
    {
        if (!$this->request->isPost())
        {
            return show(0, "请求方式错误");
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
        if (!$validate->check($data))
        {
            return show(0, $validate->getError());
        }
        try
        {
            $result = (new UserService)->login($data);
        } catch (\Exception $e)
        {
            return show(0, $e->getMessage());
        }

        if ($result)
        {
            return show(1, "登录成功");
        }
        else
        {
            return show(0, "登录失败");
        }
    }

}
