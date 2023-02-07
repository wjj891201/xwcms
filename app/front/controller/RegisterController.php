<?php

namespace app\front\controller;

use think\facade\View;
use app\front\BaseController;
use app\front\validate\User as UserValidate;
use app\front\service\User as UserService;

class RegisterController extends BaseController
{

    public function index()
    {

        if ($this->request->isAjax())
        {
            $username = $this->request->param("username", "", "trim");
            $password = $this->request->param("password", "", "trim");
            $repassword = $this->request->param("repassword", "", "trim");
            $captcha = $this->request->param("captcha", "", "trim");

            $data = [
                'username' => $username,
                'password' => $password,
                'repassword' => $repassword,
                'captcha' => $captcha,
            ];
            $validate = new UserValidate();
            if (!$validate->scene('register')->check($data))
            {
                return show(0, $validate->getError());
            }
            try
            {
                $result = (new UserService)->register($data);
            } catch (\Exception $e)
            {
                return show(0, $e->getMessage());
            }

            if ($result)
            {
                return show(1, "注册成功");
            }
            else
            {
                return show(0, "注册失败");
            }
        }
        else
        {
            return View::fetch();
        }
    }

}
