<?php

declare (strict_types = 1);

namespace app\front\controller;

use think\facade\View;
use app\front\BaseController;

class YogaController extends BaseController
{

    public function index()
    {
        View::assign('title', '首頁');
        return View::fetch();
    }

    public function course()
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

        View::assign('title', '全套課程');
        return View::fetch();
    }

    public function join()
    {


        View::assign('title', '加入');
        return View::fetch();
    }

    public function my_course()
    {
        View::assign('title', '我的課程');
        return View::fetch();
    }

    public function video()
    {
        View::assign('title', '視頻');
        return View::fetch();
    }

    public function cart_l()
    {
        View::assign('title', '購物車');
        return View::fetch();
    }

    public function cart_r()
    {
        View::assign('title', '購物車');
        return View::fetch();
    }

}
