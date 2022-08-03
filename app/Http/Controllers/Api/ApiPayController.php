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

class ApiPayController extends BaseController
{
    private $payUrlProd = "https://securepayments.sberbank.ru/payment/rest/";
    private $payUrlTest = "https://3dsec.sberbank.ru/payment/rest/";
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
          
    }
    
    /**
     * Проверка платежа сбер
     * Входные параметры:
     * 
    */
    public function checkPaidfor(Request $request){
        \Log::channel('sberpay')->info(print_r($request->all(), true));
        if($this->checkPaid($request->all(), false)){
            \Log::channel('sberpay')->info(print_r("успешно ".$request->all()["orderNumber"], true));
            return response('оплата прошла', 200);
        }
        else{
            $data = $request->all();
            $msg = "ошибка ";
            if($data && key_exists('orderNumber', $data))
                $msg .= $data["orderNumber"];
            \Log::channel('sberpay')->info(print_r($msg, true));
            return response('оплата не прошла', 200);
        }
    }
    /**
     * Проверка платежа сбер тестовый акаунт
     * Входные параметры:
     * 
    */
    public function checkPaidforTest(Request $request){
        if($this->checkPaid($request->all(), true))
            return response()->json(['msg'=>'оплата прошла', 'code' => 1], JSON_UNESCAPED_UNICODE);
        else
            return response()->json(['msg'=>'оплата не прошла', 'code' => 0], JSON_UNESCAPED_UNICODE);
    }
    /**
     * Проверка платежа сбер
     * Входные параметры:
     * 
    */
    private function checkPaid($data, $test=true){
        // file_put_contents( storage_path("logs/sber.log"), print_r($data, true), FILE_APPEND);
        if(!$data || !key_exists("orderNumber", $data) || $data['status']==0 || trim($data['operation']) != 'approved')
            return false;
        $orderNumber = $data["orderNumber"];
        $pos = strpos($orderNumber, '#');
        if($pos)
            $orderNumber = substr($orderNumber, 0, strpos($orderNumber, '#'));
        
        $order = Orders::find($orderNumber);
        if(!$order)
            return false;
        
        if(key_exists("checksum", $data)){
            $checksum = $data["checksum"];
            unset($data["checksum"]);
            ksort($data);
            reset($data);
            $d = "";
            foreach($data as $k=>$row)
                $d .= "$k;$row;";
            
            $key = ($test ? config('shop.IPAY_SECRET_TOKEN') : config('shop.IPAY_SECRET_TOKEN_APP_PROD'));
            $hmac = hash_hmac ( 'sha256' , $d , $key);
            // file_put_contents( storage_path("logs/sberhmac.log"), print_r($hmac."\n".$d."\n".$key, true));
            if(mb_strtoupper($hmac) == mb_strtoupper($checksum)){
                if(round($order->getSumPay())!=($data["amount"]/100))
                    $status = 9;
                else
                    $status = 8;
                $dat = ['orderNumber' => $data['mdOrder'], 'status' => $status, 'sum_pay' => ($data["amount"]/100)]; // отправка накладной к отгрузке в 1С
                // file_put_contents( storage_path("logs/sber1.log"), print_r($dat, true));
                $order->update($dat);
                return true;
            }
        }
        return false;
    }
    /**
     * Запрос регистрации заказа в Сбер
     * Входные параметры:
     * 
    */
    private function sberOrderCreate($orderId, $amount, $email){
        if(!$orderId || $amount<=0)
            return false;
        if(env('APP_IPAY_DEBUG', true)){
            $token = config('shop.IPAY_API_TOKEN');
            $payUrl = $this->payUrlTest."registerPreAuth.do?userName=T6102074806-api&password=T6102074806";
            $returnUrl = "https://mt.delivery/paidfor";
            $failUrl = "https://mt.delivery/paidfail";
            $dynamicCallbackUrl = "https://mt.delivery/Api/checkPaidforTest";
        }else{
            $token = config('shop.IPAY_API_TOKEN_APP_PROD');
            $payUrl = $this->payUrlProd."registerPreAuth.do?token={$token}";
            $returnUrl = "https://mt.delivery/paidfor";
            $failUrl = "https://mt.delivery/paidfail";
            $dynamicCallbackUrl = "https://mt.delivery/Api/checkPaidfor";
        }
        $amount = $amount*100;
        
        $expirationDate = Carbon::now()->endOfDay()->timezone('Europe/Moscow')->format('Y-m-d H:i:s');
        $expirationDate = str_replace(" ", "T", $expirationDate);
        $orderNumber = urlencode($this->getOrderNumber($orderId));
        $url = $payUrl . "&email={$email}&amount={$amount}&currency=643&language=ru&orderNumber={$orderNumber}&returnUrl={$returnUrl}&failUrl={$failUrl}&dynamicCallbackUrl={$dynamicCallbackUrl}&expirationDate={$expirationDate}&description=". urlencode('оплата заказа МТ Доставка №'.$orderId);
        // $data = ["amount"=>$amount, "currency" => 643, "language" => "ru",
        //          "orderNumber" => $orderNumber,"returnUrl" => $returnUrl,
        //          "dynamicCallbackUrl" => $dynamicCallbackUrl,
        //          "expirationDate" =>$expirationDate,
        //          "description" => urlencode('оплата заказа МТ Доставка №'.$orderId)];
        // dd($url);
        $data = [];
        $data_string = json_encode($data);
        $result = $this->get_curl($url, $data_string);
        $order = Orders::findOrFail($orderId);
        if(!$order)
            return false;
        $res = json_decode($result);
        if(property_exists($res, "errorCode") && $res->errorCode==1){
            //print($res->errorMessage);
            return false;
        }
         //dd($res);
        $dat = ['url_pay' => $res->formUrl]; 
        $order->update($dat);
        // print_r($result);
    }
    /**
     * Регистрация заказа в Сбер
     * Входные параметры:
     * email -   email для отправки чеков
     * orderId - номер заказа
     * 
    */
    public function getPaymentUrl(Request $request){
        $this->validate($request, ['email' => 'required|email', 'orderId'=>'required|integer']);
        if(!$request->email)
            return response()->json(["url" => "", "msg" => "нет email"], JSON_UNESCAPED_UNICODE);
        if(!$request->orderId)
            return response()->json(["url" => "", "msg" => "нет номера заказа"], JSON_UNESCAPED_UNICODE);
        $order = Orders::find($request->orderId);
        if(!$order)
            return response()->json(["url" => "", "msg" => "заказ не найден"], JSON_UNESCAPED_UNICODE);
        $this->sberOrderCreate($order->id, $order->getSumPay(), $request->email);
        $order = Orders::with('webUser')->find($request->orderId);
        if(!$order->webUser->email || empty($order->webUser->email)){
            WebUsers::where('id', $order->webUserId)->update(['email' => $request->email]);
        }
        return response()->json([ "url" => $order->url_pay]);
    }
    /**
     * 
    */
    public function getOrderNumber($orderId){
        return $orderId . "#" . date('U');
    }
    /**
     * Возвращает сгенерированный уникальный номер (идентификатор) заказа в системе продавца (используется для Gpay, Apay) 
    */
    public function getOrderNumberToPay(Request $request){
        if(!$request->orderId)
            return response()->json(["orderNumber" => "", "msg" => "нет номера заказа"], JSON_UNESCAPED_UNICODE);
        return response()->json([ "orderNumber" => $this->getOrderNumber($request->orderId)]);
    }
    /**
     * запрос состояния заказа
     * Входные параметры:
     * orderNumber - Номер заказа в системе магазина банка
     */
    private function checkOrderNumberPay($orderNumber){
        if(!$orderNumber)
            return ["pay" => false, "msg" => "нет номера заказа"];
        $url = $this->payUrlProd."getOrderStatusExtended.do?userName=P6166107366-api&password=BaTRak1902&language=ru"
                                . "&orderNumber=".urlencode($orderNumber);
        $data = [];
        $data_string = json_encode($data);
        $result = $this->get_curl($url, $data_string);
        $res = json_decode($result);
        if(!$res)
            return ["pay" => false, "msg" => "нет ответа"];
        if(property_exists($res, "errorCode") && $res->errorCode==1){
            return ["pay" => false, "msg" => "запрос вернул ошибку"];
        }
        if(property_exists($res, "orderStatus") && ($res->orderStatus==1 || $res->orderStatus==2)){
            return ["pay" => $res->amount, "msg" => "заказ оплачен"];
        }
        return ["pay" => false, "msg" => "заказ не оплачен"];
    }
    /**
     * внешний запрос состояния заказа
     * Входные параметры:
     * orderId - ID заказа
     * Number - ID заказа в 1С
     */
    public function checkOrderPay(Request $request){
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
            $c = $this->checkOrderNumberPay($paytry->orderNumber);
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
