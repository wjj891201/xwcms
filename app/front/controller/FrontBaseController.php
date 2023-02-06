<?php

namespace app\front\controller;

use app\front\BaseController;
use think\exception\HttpResponseException;

class FrontBaseController extends BaseController
{

    public $user = null;

    public function initialize()
    {

        parent::initialize();
        // 判断是否登录  判断是否登录 切换到 中间件Auth中
        //if(empty($this->isLogin())) {
        //return $this->redirect(url("login/index"), 302);
        //}
    }

    /**
     * 判断是否登录
     * @return bool
     */
    public function isLogin()
    {
        $this->user = session(config("user.session_front"));
        if (empty($this->user))
        {
            return false;
        }
        return true;
    }

    public function redirect(...$args)
    {
        throw new HttpResponseException(redirect(...$args));
    }

}
