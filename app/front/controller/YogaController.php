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

    // 會員中心
    public function member_center()
    {
        $user_session = get_login_user();
        if (empty($user_session))
        {
            return redirect('/front/login/index');
        }
        else
        {
            $user_id = $user_session['id'];

            $orderIdArr = Db::name('order')->where(['user_id' => $user_id, 'status' => 2])->column('id');
            $cateIdArr = Db::name('order_course')->group("cate_id")->where('order_id', 'in', $orderIdArr)->column('cate_id');

            $cate = Db::name('yoga_cate')->where('id', 'in', $cateIdArr)->order(['sort_order' => 'asc'])->select()->toArray();
            $temp = [];
            foreach ($cate as $key => $vo)
            {
                $vo['course'] = Db::name('yoga_course')->where(['cate_id' => $vo['id']])->order(['sort_order' => 'asc'])->select()->toArray();
                $temp[] = $vo;
            }
            $cate = $temp;

            View::assign(['title' => '會員中心', 'cate' => $cate, 'orderIdArr' => $orderIdArr]);
            return View::fetch();
        }
    }

    // 視頻播放頁面
    public function video()
    {
        $user_session = get_login_user();
        if (empty($user_session))
        {
            return redirect('/front/login/index');
        }
        else
        {
            // 會員中心傳遞過來的課程
            $course_id = get_params('course_id');
            $info = Db::table('xw_yoga_course')->alias('y')->field('y.title,y.course_url,c.title as cate_title,c.desc')
                            ->join('xw_yoga_cate c', 'y.cate_id = c.id')->where(['y.id' => $course_id])->find();

            $user_id = $user_session['id'];
            $orderIdArr = Db::name('order')->where(['user_id' => $user_id, 'status' => 2])->column('id');
            $cateIdArr = Db::name('order_course')->group("cate_id")->where('order_id', 'in', $orderIdArr)->column('cate_id');

            $cate = Db::name('yoga_cate')->where('id', 'in', $cateIdArr)->order(['sort_order' => 'asc'])->select()->toArray();
            $temp = [];
            foreach ($cate as $key => $vo)
            {

                $course = Db::name('yoga_course')->where(['cate_id' => $vo['id']])->order(['sort_order' => 'asc'])->select()->toArray();
                $d = [];
                foreach ($course as $k => $v)
                {
                    $watch = Db::name('watch_course')->where(['user_id' => $user_id, 'course_id' => $v['id']])->find();
                    if (!empty($watch))
                    {
                        $v['had_watch'] = true;
                    }
                    else
                    {
                        $v['had_watch'] = false;
                    }
                    $d[] = $v;
                }
                $course = $d;

                $vo['course'] = $course;
                $temp[] = $vo;
            }
            $cate = $temp;
            View::assign(['title' => '視頻', 'cate' => $cate, 'info' => $info]);
            return View::fetch();
        }
    }

    public function cart()
    {
        $cate = Db::name('yoga_cate')->order(['sort_order' => 'asc'])->select()->toArray();
        $temp = [];
        foreach ($cate as $key => $vo)
        {
            $vo['course'] = Db::name('yoga_course')->where(['cate_id' => $vo['id']])->order(['sort_order' => 'asc'])->select()->toArray();
            $temp[] = $vo;
        }
        $cate = $temp;

        View::assign(['title' => '購物車', 'cate' => $cate]);
        return View::fetch();
    }

    // 生成订单
    public function generate_order()
    {
        $user_session = get_login_user();
        if (empty($user_session))
        {
            return output(0, "請您先登入", [], '/front/yoga/cart');
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

    public function get_order()
    {
        if ($this->request->isAjax())
        {
            $course_id = get_params('course_id');
            $user_id = get_login_user('id');
            if (empty($user_id))
            {
                return output(0, "賬戶没有登入");
            }
            else
            {
                $watch = Db::name('watch_course')->where(['user_id' => $user_id, 'course_id' => $course_id])->find();
                if (empty($watch))
                {
                    $data = [
                        'user_id' => $user_id,
                        'course_id' => $course_id,
                        'add_time' => Carbon::now()->toDateTimeString()
                    ];
                    Db::name('watch_course')->insert($data);
                }
            }
            $info = Db::table('xw_yoga_course')->alias('y')->field('y.title,y.course_url,c.title as cate_title,c.desc')
                            ->join('xw_yoga_cate c', 'y.cate_id = c.id')->where(['y.id' => $course_id])->find();
            return output(1, "選擇成功", ['info' => $info]);
        }
    }

}
