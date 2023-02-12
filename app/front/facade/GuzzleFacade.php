<?php

namespace app\front\facade;

use app\front\exception\RespException;
use GuzzleHttp\Client;
use think\Facade;
use think\facade\Config;

class GuzzleFacade extends Facade
{

    public static function createClient($type)
    {
        $client = new Client([
            'base_uri' => Config::get('user.' . $type . '.api_url'),
            'timeout' => Config::get('user.timeout'),
        ]);
        return $client;
    }

    public static function reqGet($client, $url, $param)
    {
        $resp = $client->request('GET', $url, [
            'query' => $param
        ]);
        $result = @json_decode($resp->getBody(), true);

        if ($result['code'] != 0)
        {
            throw new RespException(1, $result['message']);
        }
        return $result['data'];
    }

    public static function reqPost($client, $url, $param)
    {
        $resp = $client->request('POST', $url, [
            'json' => $param
        ]);
        $result = @json_decode($resp->getBody(), true);
        if ($result['code'] != 0)
        {
            throw new RespException(1, $result['message']);
        }
        return $result['data'];
    }

}
