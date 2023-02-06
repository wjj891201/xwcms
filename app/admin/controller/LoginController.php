<?php


declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\validate\MemberCheck;
use Carbon\Carbon;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Session;

class LoginController
{
    //登录
    public function index()
    {
        return View();
    }
	
    //提交登录
    public function do_login()
    {
        $param = get_params();
        try {
            validate(MemberCheck::class)->check($param);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            return output(1, $e->getError());
        }

        $admin = Db::name('admin')->where(['username' => $param['username']])->find();
        if (empty($admin)) {
            return output(1, '用户名或密码错误');
        }
        $param['password'] = set_password($param['password'], $admin['salt']);
        if ($admin['password'] !== $param['password']) {
            return output(1, '用户名或密码错误');
        }
        if ($admin['status'] == 0) {
            return output(1, '该用户禁止登录,请于系统所有者联系');
        }
        $data = [
            'last_login_at' => Carbon::now()->toDateTimeString(),
            'last_login_ip' => request()->ip(),
            'login_num' => $admin['login_num'] + 1,
        ];
        Db::name('admin')->where(['id' => $admin['id']])->update($data);
        $session_admin = get_config('app.session_admin');
        Session::set($session_admin, $admin);
        $token = make_token();
        set_cache($token, $admin, 7200);
        $admin['token'] = $token;
        add_log('login', $admin['id'], $data);
        return output(0, '登录成功', ['uid' => $admin['id']]);
    }

    //退出登录
    public function login_out()
    {
        $session_admin = get_config('app.session_admin');
        Session::delete($session_admin);
        //redirect('/')->send();
        return output(0, "退出成功");
    }
}
