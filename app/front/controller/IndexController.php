<?php

declare (strict_types = 1);

namespace app\front\controller;

class IndexController extends FrontBaseController
{

    public function index()
    {
        echo 'hello-user';
        exit;
    }

}
