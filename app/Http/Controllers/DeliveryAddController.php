<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\DeliveryAdd;
use App\Model\Orders;
use App\Model\OrderItem;
use Carbon\Carbon;

class DeliveryAddController extends BaseController
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
     * Создает документ довоза
     * Входные параметры:
     * orderId - ID документа заказа с недостачей
     * itemId -  ID товара
     */
    public function createDeliveryAdd(Request $request){
        $u = \Auth::guard('admin')->user();
        $orderId = $request->orderId ?? 0;
        $itemId = $request->itemId ?? 0;
        $quantity = $request->quantity ?? 0;
        if(!$orderId) return response()->json(['success'=> false, 'data' => [], 'error' => ['msg' => 'нет ID заказа']], JSON_UNESCAPED_UNICODE);
        if(!$itemId) return response()->json(['success'=> false, 'data' => [], 'error' => ['msg' => 'нет ID товара']], JSON_UNESCAPED_UNICODE);
        if(!$quantity) return response()->json(['success'=> false, 'data' => [], 'error' => ['msg' => 'нет кол-ва']], JSON_UNESCAPED_UNICODE);
        $order = Orders::find($orderId);
        if(!$order || $order->status !=4) return response()->json(['success'=> false, 'data' => [], 'error' => ['msg' => 'заказ не найден или статус заказа не отгружен']], JSON_UNESCAPED_UNICODE);
        $item = OrderItem::where('orderId', $orderId)->where('itemId', $itemId)->first();
        if(!$item || $order->status !=4) return response()->json(['success'=> false, 'data' => [], 'error' => ['msg' => 'товар не найден в заказе '.$orderId]], JSON_UNESCAPED_UNICODE);
        $deliveryadd = DeliveryAdd::create([
            'orderId'  => $orderId,
            'itemId'   => $itemId,
            'createId' => $u->id,
            'quantity' => $quantity
        ]);
        return response()->json(['success'=> true, 'data' => ['deliveryadd' => $deliveryadd], 'error' => []], JSON_UNESCAPED_UNICODE);
    }
    /**
     * Закрывает документ довоза (передается в 1С)
     * Входные параметры:
     * id - ID документа довоза
     */
    public function closeDeliveryAdd(Request $request){
        $u = \Auth::guard('admin')->user();
        $id = $request->id ?? 0;
        $waveId = $request->waveId ?? 0;
        $deliveryDate = $request->deliveryDate ?? 0;
        $addedId = $request->addedId ?? 0;
        
        return $this->close_DeliveryAdd($id, $u->id, $addedId, $waveId, $deliveryDate);
    }
    /**
     * Закрывает документ довоза (передается в 1С)
     * Входные параметры:
     * id - ID документа довоза
     */
    public function close_DeliveryAdd($id, $userId, $addedId=0, $waveId=0, $deliveryDate=0){
        if(!$id) return response()->json(['success'=> false, 'data' => [], 'error' => ['msg' => 'нет ID довоза']], JSON_UNESCAPED_UNICODE);
        $deliveryadd = DeliveryAdd::find($id);
        if(!$deliveryadd || $deliveryadd->status!=2) return response()->json(['success'=> false, 'data' => [], 'error' => ['msg' => 'документ довоза не найден или не подтвержден']], JSON_UNESCAPED_UNICODE);
        if($addedId==0 && $waveId==0 && $deliveryDate==0 || ($addedId==0 && ($waveId==0 || $deliveryDate==0))) 
          return response()->json(['success'=> false, 'data' => [], 'error' => ['msg' => 'документ довоза нужно закрыть заказом или волной и датой доставки']], JSON_UNESCAPED_UNICODE);
        $data = ['closeId' => $userId, 'status' => 3];
        if($addedId)
            $data['addedId'] = $addedId;
        else{
            $data['waveId'] = $waveId;
            $deliveryDate = str_replace('.', '-', $deliveryDate);
            $deliveryDate = str_replace('/', '-', $deliveryDate);
            $d = explode('-', $deliveryDate);
            if(strlen($d[0])==2)
                $deliveryDate = $d[2].'-'.$d[1].'-'.$d[0];
            $data['deliveryDate'] = $deliveryDate;
        }
        $deliveryadd->update($data);
        return response()->json(['success'=> true, 'data' => ['msg' => 'документ довоза закрыт'], 'error' => []], JSON_UNESCAPED_UNICODE);
    }
}
