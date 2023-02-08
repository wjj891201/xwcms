<?php

namespace app\front\controller;

use think\facade\View;
use app\front\BaseController;
use app\front\validate\User as UserValidate;
use app\front\service\User as UserService;
use think\facade\Cookie;

class ForgetController extends BaseController
{

    public function index()
    {
        if ($this->request->isAjax())
        {
            $email = $this->request->param("email", "", "trim");
            $email_captcha = $this->request->param("email_captcha", "", "trim");
            $password = $this->request->param("password", "", "trim");
            $data = [
                'email' => $email,
                'email_captcha' => $email_captcha,
                'password' => $password
            ];
            $validate = new UserValidate();
            if (!$validate->scene('forget')->check($data))
            {
                return output(0, $validate->getError());
            }
            try
            {
                $result = (new UserService)->forget($data);
            } catch (\Exception $e)
            {
                return output(0, $e->getMessage());
            }

            if ($result)
            {
                return output(1, "更改成功");
            }
            else
            {
                return output(0, "更改失败");
            }
        }
        else
        {
            var_dump(Cookie::get('email_captcha'));
            return View::fetch();
        }
    }

}
