<?php

declare (strict_types = 1);

namespace app\front\controller;

use app\front\BaseController;
use Ecpay\Sdk\Factories\Factory;
use Ecpay\Sdk\Services\UrlService;
use Ecpay\Sdk\Response\VerifiedArrayResponse;

class PayController extends BaseController
{

    public function index()
    {
        $factory = new Factory([
            'hashKey' => 'sk3HyPe1EidjUEZp',
            'hashIv' => 'jWFpwRZKTlmeUFj4',
        ]);
        $autoSubmitFormService = $factory->create('AutoSubmitFormWithCmvService');

        $input = [
            'MerchantID' => '3079837',
            'MerchantTradeNo' => 'Test' . time(),
            'MerchantTradeDate' => date('Y/m/d H:i:s'),
            'PaymentType' => 'aio',
            'TotalAmount' => 100,
            'TradeDesc' => UrlService::ecpayUrlEncode('交易描述範例'),
            'ItemName' => '範例商品一批 100 TWD x 1',
            'ChoosePayment' => 'ALL',
            'EncryptType' => 1,
            // 請參考 example/Payment/GetCheckoutResponse.php 範例開發
            'ReturnURL' => 'http://xwcms.deve/front/pay/receive',
        ];
        $action = 'https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5';

        return $autoSubmitFormService->generate($input, $action);
    }

    public function receive()
    {
        $factory = new Factory([
            'hashKey' => 'sk3HyPe1EidjUEZp',
            'hashIv' => 'jWFpwRZKTlmeUFj4',
        ]);
        $checkoutResponse = $factory->create(VerifiedArrayResponse::class);

        $_POST = [
            'MerchantID' => '2000132',
            'MerchantTradeNo' => 'WPLL4E341E122DB44D62',
            'PaymentDate' => '2019/05/09 00:01:21',
            'PaymentType' => 'Credit_CreditCard',
            'PaymentTypeChargeFee' => '1',
            'RtnCode' => '1',
            'RtnMsg' => '交易成功',
            'SimulatePaid' => '0',
            'TradeAmt' => '500',
            'TradeDate' => '2019/05/09 00:00:18',
            'TradeNo' => '1905090000188278',
            'CheckMacValue' => '59B085BAEC4269DC1182D48DEF106B431055D95622EB285DECD400337144C698',
        ];

        var_dump($checkoutResponse->get($_POST));
    }

}
