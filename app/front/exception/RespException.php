<?php

namespace app\front\exception;

class RespException extends \RuntimeException
{

    protected $data;

    public function __construct($code = 0, $message = "", $data = [])
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

}
