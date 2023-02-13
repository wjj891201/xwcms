<?php

declare (strict_types = 1);

namespace app\front\controller;

use think\facade\View;
use app\front\BaseController;
use think\facade\Db;

class YogaController extends BaseController
{

    public function course()
    {
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
        $user_session = get_login_user();
        if (empty($user_session))
        {
            return redirect('/front/login/index');
        }

        $cate = Db::name('yoga_cate')->order(['sort_order' => 'asc'])->select()->toArray();
        $temp = [];
        foreach ($cate as $key => $vo)
        {
            $vo['course'] = Db::name('yoga_course')->where(['cate_id' => $vo['id']])->order(['sort_order' => 'asc'])->select()->toArray();
            $temp[] = $vo;
        }
        $cate = $temp;
//        var_dump($cate);
//        exit;
        View::assign(['title' => '購物車', 'cate' => $cate]);
        return View::fetch();
    }

    public function cart_r()
    {
        View::assign('title', '購物車');
        return View::fetch();
    }

}
