<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Model\WebUsers;
use App\Model\Orders;
use App\Model\Paytry;
use App\Http\Controllers\BaseController;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;
use phpseclib\Crypt\Random;

class ApiCRBPayController extends BaseController
{
    private $payUrlProd = "https://apipay-test.crb-dnr.ru/";
    private $payUrlTest = "https://apipay-test.crb-dnr.ru/";
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
          
    }
    
    /**
     * Проверка платежа ЦРБ боевой
     * Входные параметры:
     * 
    */
    public function checkPaidforCrb(Request $request){
        if($this->checkPaidCrb($request->all(), false)){
            \Log::channel('crbpay')->info(print_r("успешно ".$request->all()["orderNumber"], true));
            return response('оплата прошла', 200);
        }
        else{
            $data = $request->all();
            $msg = "ошибка ";
            if($data && key_exists('orderNumber', $data))
                $msg .= $data["orderNumber"];
            \Log::channel('crbpay')->info(print_r($msg, true));
            return response('оплата не прошла', 200);
        }
    }
    /**
     * Проверка платежа ЦРБ тестовый акаунт
     * Входные параметры:
     * 
    */
    public function checkPaidforTestCrb(Request $request){
        \Log::channel('crbpaytest')->info(print_r($request->all(), true));
        if($this->checkPaidCrb($request->all(), true))
            return response()->json(['msg'=>'оплата прошла', 'code' => 1], JSON_UNESCAPED_UNICODE);
        else
            return response()->json(['msg'=>'оплата не прошла', 'code' => 0], JSON_UNESCAPED_UNICODE);
    }
    /**
     * Проверка платежа ЦРБ
     * Входные параметры:
     * 
    */
    private function checkPaidCrb($data, $test=true){
        file_put_contents( storage_path("logs/crb.log"), print_r($data, true), FILE_APPEND);
        if(!$data || !key_exists("invoice_number", $data) || !key_exists("order_id", $data) || $data['errorCode']==0 )
            return false;
        $orderNumber = $data["invoice_number"];
        $pos = strpos($orderNumber, '-');
        if($pos)
            $orderNumber = substr($orderNumber, 0, strpos($orderNumber, '-'));

        $order = Orders::find($orderNumber);
        if(!$order)
            return false;
        
        if(key_exists("signature", $data)){
            $checksum = $data["signature"];
            $merchant_id = ($test ? config('shop.MERCHANT_ID_TEST_CRB') : config('shop.MERCHANT_ID_PROD_CRB'));
            $key = ($test ? config('shop.SECRET_KEY_CRB_TEST') : config('shop.SECRET_KEY_CRB_PROD'));
            $total = $data["total"];
            $currency = $data["currency"];  // код валюты руб.
            $invoice_number = $order->id;
            $invoice_date = $order->created_at;
            $description = ('orderN' . $order->id);
            $custom = ('orderN' . $order->id);
            $errorCode = $data["errorCode"];
            $secret_key = ($test ? config('shop.SECRET_KEY_CRB_TEST') : config('shop.SECRET_KEY_CRB_PROD'));
            $d = $data["order_id"].$merchant_id.$total.$currency.$invoice_number.$invoice_date.$description.$secret_key.$custom.$errorCode;
            
            $hmac = hash_hmac ( 'sha384' , $d , $key);
            // file_put_contents( storage_path("logs/sberhmac.log"), print_r($hmac."\n".$d."\n".$key, true));
            if(mb_strtoupper($hmac) == mb_strtoupper($checksum)){
                if(round($order->getSumPay())!=($total/100))
                    $status = 9;
                else
                    $status = 8;
                $dat = ['orderNumber' => $order->id, 'status' => $status, 'sum_pay' => ($total/100)]; // отправка накладной к отгрузке в 1С
                // file_put_contents( storage_path("logs/sber1.log"), print_r($dat, true));
                $order->update($dat);
                return true;
            }
        }
        return false;
    }
    /**
     * Запрос регистрации заказа в ЦРБ
     * Входные параметры:
     * 
    */
    private function crbOrderCreate($orderId, $orderDate, $amount){
        if(!$orderId || $amount<=0)
            return "нет заказа или суммы";
        $currency = 643;  // код валюты руб.
        if(env('APP_IPAY_DEBUG', true)){
            $merchantId = config('shop.MERCHANT_ID_TEST_CRB');
            $payUrl = $this->payUrlTest . "PSexternal/hs/orders/start";
            $returnUrl = "https://mt.delivery/paidfor";
            $failUrl = "https://mt.delivery/paidfail";
            $notifyUrl = "https://mt.delivery/Api/checkPaidforTestCrb";
            $secret_key = config('shop.SECRET_KEY_CRB_TEST');
            $url_pay = config('shop.URL_PAY_CRB_TEST');
        }else{
            $merchantId = config('shop.MERCHANT_ID_PROD_CRB');
            $payUrl = $this->payUrlProd . "";
            $returnUrl = "https://mt.delivery/paidfor";
            $failUrl = "https://mt.delivery/paidfail";
            $notifyUrl = "https://mt.delivery/Api/checkPaidforCrb";
            $secret_key = config('shop.SECRET_KEY_CRB_PROD');
            $url_pay = config('shop.URL_PAY_CRB_PROD'); 
        }
        $amount = $amount*100;
        $description = ('orderN'.$orderId);
        $сustom = ('orderN'.$orderId);
        $order_id = $this->getOrderNumber($orderId);

        $signature = hash('sha384', $merchantId.$amount.$currency.$order_id.$orderDate.$description.$secret_key.$сustom);
        $data = [
            "merchant_id"    => $merchantId,
            "currency"       => $currency,
            "total"          => $amount,
            "invoice_number" => $order_id,
            "invoice_date"   => $orderDate,
            "description"    => ($description),
            "custom"         => ($сustom),
            "signature"      => $signature,
            "returnUrl"      => $returnUrl,
            "failUrl"        => $failUrl,
            "notifyUrl"      => $notifyUrl
        ];
        
        $data_string = json_encode($data);
        
        $result = $this->get_raw_curl($payUrl, $data_string, "POST");
        
        $order = Orders::findOrFail($orderId);
        if(!$order)
            return "заказ не найден в БД";
        $res = json_decode($result);
        
        if((property_exists($res, "errorCode") && $res->errorCode > 0) || $res->status != 'processing'){
            return $res->invoice_number."||".$res->errorMessage."||".$data_string;
        }
         
        $dat = ['url_pay' => $url_pay.$res->order_id]; 
        $order->update($dat);
        Paytry::create(['orderId' => $order->id, 'orderNumber' => $order_id, 'order_id' => $res->order_id]);
    }
    /**
     * Регистрация заказа в ЦРБ
     * Входные параметры:
     * 
     * orderId - номер заказа
     * 
    */
    public function getCrbPaymentUrl(Request $request){
        
        if(!$request->orderId)
            return response()->json(["url" => "", "msg" => "нет номера заказа"], JSON_UNESCAPED_UNICODE);
        $order = Orders::find($request->orderId);
        if(!$order)
            return response()->json(["url" => "", "msg" => "заказ не найден"], JSON_UNESCAPED_UNICODE);
        $crb = $this->crbOrderCreate($order->id, $order->created_at, $order->getSumPay());
        if($crb != 'ok')
            return response()->json([ "url" => "", "msg" => $crb]);
        $order = Orders::with('webUser')->find($request->orderId);
        // if(!$order->webUser->email || empty($order->webUser->email)){
        //     WebUsers::where('id', $order->webUserId)->update(['email' => $request->email]);
        // }
        return response()->json([ "url" => $order->url_pay]);
    }
    /**
     * 
    */
    public function getOrderNumber($orderId){
        return $orderId . "-" . date('U');
    }
    /**
     * Возвращает сгенерированный уникальный номер (идентификатор) пока нигде не используется 
    */
    public function getOrderNumberToPayCRB(Request $request){
        if(!$request->orderId)
            return response()->json(["orderNumber" => "", "msg" => "нет номера заказа"], JSON_UNESCAPED_UNICODE);
        return response()->json([ "orderNumber" => $this->getOrderNumber($request->orderId)]);
    }
    /**
     * запрос состояния заказа
     * Входные параметры:
     * orderNumber - Номер заказа в системе магазина банка
     */
    private function checkOrderNumberPayCRB($orderNumber, $order_id, $order){

        if(!$orderNumber)
            return ["pay" => false, "msg" => "нет номера заказа"];
        $merchant_id = config('shop.MERCHANT_ID_PROD_CRB');
        $currency = "643";
        $total = $order->getSumPay()*100;
        $secret_key = config('shop.SECRET_KEY_CRB_PROD'); 
        $signature = hash('sha384', $order_id.$merchant_id.$total.$currency.$order->id.$secret_key);;
        $url = $this->payUrlProd."PSexternal/hs/orders/order_status?order_id=$orderNumber"
                                . "&merchant_id=" . $merchant_id . "&signature=" . $signature;
        $data = [];
        $data_string = json_encode($data);
        $result = $this->get_curl($url, $data_string);
        $res = json_decode($result);
        if(!$res)
            return ["pay" => false, "msg" => "нет ответа"];
        if(property_exists($res, "errorCode") && $res->errorCode>0){
            return ["pay" => false, "msg" => "запрос вернул ошибку:" . $res->errorMessage];
        }
        if(property_exists($res, "status") && ($res->status=="approved")){
            return ["pay" => $res->total, "msg" => "заказ оплачен"];
        }
        return ["pay" => false, "msg" => "заказ не оплачен"];
    }
    /**
     * внешний запрос состояния заказа
     * Входные параметры:
     * orderId - ID заказа
     * Number - ID заказа в 1С
     */
    public function checkOrderPayCRB(Request $request){
        $orderId = $request->orderId ?? 0;
        $number = $request->number ?? 0;
        if($orderId){
            $order = Orders::find($orderId);
        }else{
            if($number)
                $order = Orders::where('number','like', $number)->first();
            else
                $order = null;
        }
        if(!$order)
            return response()->json(["pay" => false, "msg" => "заказ не найден"], JSON_UNESCAPED_UNICODE);
        $paytrys = Paytry::where('orderId', $order->id)->orderBy("created_at", "desc")->get();
        foreach($paytrys as $paytry){
            $c = $this->checkOrderNumberPayCRB($paytry->orderNumber, $paytry->order_id, $order);
            if($c["pay"]){
                if($order->status==6){
                    if(round($order->getSumPay())!=($c["pay"]/100))
                        $status = 9;
                    else
                        $status = 8;
                    $dat = [
                            'orderNumber' => $paytry->orderNumber, 
                            'status' => $status, 
                            'sum_pay' => ($c["pay"]/100)
                           ]; // отправка накладной к отгрузке в 1С
                    $order->update($dat);
                }
                return response()->json(["pay" => round($c["pay"]/100,2), "msg" => "заказ оплачен"], JSON_UNESCAPED_UNICODE);
            }
        }
        return response()->json(["pay" => false, "msg" => "заказ не оплачен"], JSON_UNESCAPED_UNICODE);
    }
}
