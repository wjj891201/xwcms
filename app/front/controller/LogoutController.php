<?php

namespace app\front\controller;

class LogoutController extends FrontBaseController
{

    public function index()
    {
        // 清楚session
        session(config("user.session_front"), null);
        // 执行跳转
        return redirect(url("login/index"));
    }

}
