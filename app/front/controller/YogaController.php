<?php

declare (strict_types = 1);

namespace app\front\controller;

use think\facade\View;
use Carbon\Carbon;
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

    // 生成订单
    public function generate_order()
    {
        $user_session = get_login_user();
        if (empty($user_session))
        {
            return redirect('/front/login/index');
        }
        else
        {
            // 訂單
            $cate_id = get_params('cate_id');
            $cateIdArr = explode(',', $cate_id);
            $total_price = Db::name('yoga_cate')->where('id', 'in', $cate_id)->sum('price');
            $order = [
                'order_no' => set_salt(2) . time(),
                'user_id' => $user_session['id'],
                'total_price' => $total_price,
                'status' => 1,
                'create_time' => Carbon::now()->toDateTimeString()
            ];
            Db::name('order')->insert($order);
            $order_id = Db::name('order')->getLastInsID();
            // 訂單課程
            if ($cate_id == 4)
            {
                $cateIdArr = Db::name('yoga_cate')->where('id', '<>', $cate_id)->column('id');
            }
            foreach ($cateIdArr as $key => $vo)
            {
                $price = Db::name('yoga_cate')->where(['id' => $vo])->value('price');
                Db::name('order_course')->insert(['order_id' => $order_id, 'cate_id' => $vo, 'price' => $price]);
            }
            return output(1, "訂單生成成功", ['order_id' => $order_id]);
        }
    }

    public function cart_r()
    {
        View::assign('title', '購物車');
        return View::fetch();
    }

}
