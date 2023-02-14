<?php

declare (strict_types = 1);

namespace app\front\controller;

use app\front\BaseController;
use Ecpay\Sdk\Factories\Factory;
use Ecpay\Sdk\Services\UrlService;
use Ecpay\Sdk\Response\VerifiedArrayResponse;
use think\facade\Db;
use Carbon\Carbon;

class PayController extends BaseController
{

    public function index()
    {
        $user_session = get_login_user();
        if (empty($user_session))
        {
            return redirect('/front/login/index');
        }
        else
        {
            $order_id = get_params('order_id');
            $user_id = $user_session['id'];

            $orderInfo = Db::name('order')->where(['id' => $order_id, 'user_id' => $user_id, 'status' => 1])->find();
            if (!empty($orderInfo))
            {
                $cateIdArr = Db::name('order_course')->where(['order_id' => $order_id])->column('cate_id');
                if (empty($cateIdArr))
                {
                    return output(0, '訂單信息錯誤');
                }
                $ItemNameArr = Db::name('yoga_cate')->where('id', 'in', $cateIdArr)->column('title');
                $ItemName = implode(',', $ItemNameArr);

                $factory = new Factory([
                    'hashKey' => 'sk3HyPe1EidjUEZp',
                    'hashIv' => 'jWFpwRZKTlmeUFj4',
                ]);
                $autoSubmitFormService = $factory->create('AutoSubmitFormWithCmvService');

                $input = [
                    'MerchantID' => '3079837',
                    'MerchantTradeNo' => $orderInfo['order_no'],
                    'MerchantTradeDate' => date('Y/m/d H:i:s'),
                    'PaymentType' => 'aio',
                    'TotalAmount' => $orderInfo['total_price'],
                    'TradeDesc' => UrlService::ecpayUrlEncode('ASHA YOUGA'),
                    'ItemName' => '【' . $ItemName . '】瑜伽課程',
                    'ChoosePayment' => 'ALL',
                    'EncryptType' => 1,
                    // 請參考 example/Payment/GetCheckoutResponse.php 範例開發
                    'ReturnURL' => 'http://xwcms.deve/front/pay/receive',
                ];
                $action = 'https://payment.ecpay.com.tw/Cashier/AioCheckOut/V5';

                return $autoSubmitFormService->generate($input, $action);
            }
            else
            {
                return output(0, '訂單信息錯誤');
            }
        }
    }

    public function receive()
    {
        $factory = new Factory([
            'hashKey' => 'sk3HyPe1EidjUEZp',
            'hashIv' => 'jWFpwRZKTlmeUFj4',
        ]);
        $checkoutResponse = $factory->create(VerifiedArrayResponse::class);

        $_POST = get_params();
//        $_POST = [
//            'MerchantID' => '3079837',
//            'MerchantTradeNo' => 'uw1676348264',
//            'PaymentDate' => '2019/05/09 00:01:21',
//            'PaymentType' => 'Credit_CreditCard',
//            'PaymentTypeChargeFee' => '1',
//            'RtnCode' => '1',
//            'RtnMsg' => '交易成功',
//            'SimulatePaid' => '0',
//            'TradeAmt' => '500',
//            'TradeDate' => '2019/05/09 00:00:18',
//            'TradeNo' => '1905090000188278',
//            'CheckMacValue' => '59B085BAEC4269DC1182D48DEF106B431055D95622EB285DECD400337144C698',
//        ];
        try
        {
            $checkoutResponse->get($_POST);
            $order_no = $_POST['MerchantTradeNo'];
            $total_price = Db::name('order')->where(['order_no' => $order_no])->value('total_price');
            if ($total_price == $_POST['TradeAmt'])
            {
                Db::name('order')->where(['order_no' => $order_no])->update(['status' => 2, 'update_time' => Carbon::now()->toDateTimeString()]);
            }
            else
            {
                return output(0, '訂單金額不等');
            }
        } catch (\Exception $e)
        {
            return output(0, $e->getMessage());
        }
    }

}
