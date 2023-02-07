<?php

declare (strict_types = 1);

namespace app\front\controller;

use think\facade\View;
use app\front\BaseController;

class AboutController extends BaseController
{

    public function a()
    {
        return View::fetch('a', ['title' => 'a']);
    }

    public function b()
    {
        return View::fetch('b', ['title' => 'b']);
    }

}
