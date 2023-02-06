<?php


namespace app\front\exception;


use think\exception\Handle;
use think\Response;
use Throwable;

class Http extends Handle
{
    public function render($request, Throwable $e): Response
    {


        // 其他错误交给系统处理
        return parent::render($request, $e);
    }
}