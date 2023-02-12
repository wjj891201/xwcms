<?php

namespace app\front\facade;

use GuzzleHttp\Client;
use think\Facade;
use think\facade\Config;

class ApiFacade extends Facade
{

    /**
     * 發送email
     * @param $param
     * @return mixed
     */
    public static function sendEmail($param)
    {
        $client = GuzzleFacade::createClient('common');
        $data = [
            'to' => $param['email'],
            'subject' => $param['subject'],
            'body' => $param['content'],
        ];

        return GuzzleFacade::reqPost($client, '/api/send-email', $data);
    }

}
