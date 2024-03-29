<?php

namespace app\front\controller;

use think\facade\View;
use app\front\BaseController;
use app\front\validate\User as UserValidate;
use app\front\service\User as UserService;
use Carbon\Carbon;
use think\facade\Cookie;
use app\front\facade\ApiFacade;

class RegisterController extends BaseController
{

    public function index()
    {

        if ($this->request->isAjax())
        {
            $username = $this->request->param("username", "", "trim");
            $password = $this->request->param("password", "", "trim");
            $repassword = $this->request->param("repassword", "", "trim");
            $email = $this->request->param("email", "", "trim");
            $email_captcha = $this->request->param("email_captcha", "", "trim");
            $captcha = $this->request->param("captcha", "", "trim");

            $data = [
                'username' => $username,
                'password' => $password,
                'repassword' => $repassword,
                'email' => $email,
                'email_captcha' => $email_captcha,
                'captcha' => $captcha,
            ];
            $validate = new UserValidate();
            if (!$validate->scene('register')->check($data))
            {
                return output(0, $validate->getError());
            }
            try
            {
                $result = (new UserService)->register($data);
            } catch (\Exception $e)
            {
                return output(0, $e->getMessage());
            }

            if ($result)
            {
                return output(1, "注册成功");
            }
            else
            {
                return output(0, "注册失败");
            }
        }
        else
        {
            View::assign('title', '註冊');
            return View::fetch();
        }
    }

    public function send_mail()
    {
        if ($this->request->isAjax())
        {
            $email = $this->request->post("email", "", "trim");
            $email_captcha = set_salt(6);
            $content = '本次驗證碼為：' . $email_captcha;
            Cookie::set('email_captcha', $email_captcha, 3600);
            try
            {
                ApiFacade::sendEmail(["email" => $email, "subject" => '驗證碼', "content" => $content]);
            } catch (\Exception $e)
            {
                return output(0, $e->getMessage());
            }
            output(1, '发送成功');
//            tp6发邮件
//            $result = send_email($email, '验证码', $content);
//            $result = true;
//            if ($result)
//            {
//                output(1, '发送成功');
//            }
//            else
//            {
//                output(0, '发送失败');
//            }
        }
    }

}
