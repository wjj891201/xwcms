<?php

declare (strict_types = 1);

namespace app\front\controller;

use think\facade\View;
use app\front\BaseController;

class RegisterController extends BaseController
{

    public function index()
    {
        return View::fetch();
    }

}
