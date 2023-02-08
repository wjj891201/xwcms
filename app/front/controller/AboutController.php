<?php

declare (strict_types = 1);

namespace app\front\controller;

use think\facade\View;
use app\front\BaseController;

class AboutController extends BaseController
{

    public function a()
    {
//        $data = [
//            'smtp' => 'smtp.163.com',
//            'smtp_port' => '465',
//            'smtp_user' => '15195861092',
//            'smtp_pwd' => 'KETCZPYTXMWARSPR',
//            'email' => '15195861092@163.com',
//            'title' => '测试'
//        ];
//        var_dump(serialize($data));
//        exit;

//        $result = send_email('517987404@qq.com', '找回密码', '已经发给你了');
//        var_dump($result);
//        exit;

        return View::fetch('a', ['title' => 'a']);
    }

    public function b()
    {
        return View::fetch('b', ['title' => 'b']);
    }

}
