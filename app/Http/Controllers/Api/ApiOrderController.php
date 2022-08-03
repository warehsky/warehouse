<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Model\WebUsers;
use App\Model\Orders;
use App\Model\OrderChanges;
use App\Http\Controllers\BaseController;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;
use phpseclib\Crypt\Random;

class ApiOrderController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
          
    }
    /**
     * получение документов на изменение для оператора
     * orderId - ID заказа
    */
    public function getOrderCorrects(Request $request){
        $this->validate($request, ['orderId'=>'required|integer']);
        $orderId = (int)($request->input('orderId') ?? 0);
        if($orderId == 0)
            return response()->json(['msg'=>'не указан заказ', 'code' => 0, 'changes' => []], JSON_UNESCAPED_UNICODE);
        $changes = OrderChanges::select('orderChanges.*', 'itemsLink.title')
        ->join('itemsLink', 'itemsLink.id', 'orderChanges.itemId')
        ->where('orderId', $orderId)
        ->where('closed', 0)
        ->get();
        return response()->json(['msg'=>'изменения в заказе', 'code' => 1, 'changes' => $changes], JSON_UNESCAPED_UNICODE);
    }
    /**
     * Изменение одного поля заказа
     * orderId - ID заказа
    */
    public function setOrderFeature(Request $request){
        $this->validate($request, ['orderId'=>'required|integer', 'field' => 'required|string', 'value' => 'required']);
        $orderId = (int)($request->input('orderId') ?? 0);
        if($orderId == 0)
            return response()->json(['msg'=>'не указан заказ', 'code' => 0], JSON_UNESCAPED_UNICODE);
        $order = Orders::find($orderId);
        if(!$order)
            return response()->json(['msg'=>"заказ <{$orderId}> не найден", 'code' => 0], JSON_UNESCAPED_UNICODE);
        $order->update([$request->input('field') => $request->input('value')]);
        return response()->json(['msg'=>"заказ <{$orderId}> обновлен", 'code' => 1], JSON_UNESCAPED_UNICODE);
    }
    
}
