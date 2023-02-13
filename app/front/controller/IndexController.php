<?php

declare (strict_types = 1);

namespace app\front\controller;

use think\facade\View;
use app\front\BaseController;

class IndexController extends BaseController
{

    public function index()
    {
        View::assign('title', '首頁');
        return View::fetch();
    }

}
