<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Api\ApiFreeController as ApiFree;
use App\Model\OrderItem;
use App\Model\OrderChangesHead;
use App\Model\OrderChanges;
use App\Model\Orders;
use App\Model\ItemsLink;
use App\Model\PickupControll;
use Carbon\Carbon;

class WareHouseController extends BaseController
{
    const selectedStatus = "2,5,6,8,7";   //Статусы с которыми выбираются заказы

    public function index()
    {
        $def = Carbon::now()->timezone('Europe/Moscow')->format('Y-m-d');
        return view('Admin.warehousePickup', compact('def'));
    }

    public function getOrders(Request $request)
    {
        $dateStart='2018-01-01 00:00:00';
        if ($request->dateStart)
            $dateStart = $request->dateStart;
        
        $dateEnd=Carbon::now()->timezone('Europe/Moscow')->endofDay();
        if ($request->dateEnd)
            $dateEnd = $request->dateEnd;

        $statuses = self::selectedStatus;
        $sql="SELECT i.id,i.number,i.deliveryDate,w.timeFrom,w.timeTo,j.title as payment,k.title as status,i.created_at,count(v.id) as count,l.pickupId FROM orders as i 
        JOIN payments as j ON j.id=i.payment
        JOIN statuses as k ON k.id=i.status
        JOIN timeWaves as w on i.waveId=w.id 
        LEFT JOIN pickupControll as l ON l.orderId=i.id
        JOIN orderItem as v ON v.orderId = i.id 
        WHERE i.status in ($statuses) AND i.created_at BETWEEN '{$dateStart}' AND '{$dateEnd}'
        GROUP BY i.id ORDER BY i.created_at DESC";
        $data = \DB::Connection()->select($sql);

        $ApiFree = new ApiFree();
        $items = $ApiFree->arrayPaginatorForManyPath($data, $request);

        $warehouseWorkersSQL = "SELECT j.id,j.name FROM model_has_roles as i
        JOIN admins as j ON i.model_id=j.id
        WHERE i.role_id=9";
        $warehouseWorkers = \DB::Connection()->select($warehouseWorkersSQL);


        return json_encode([array_values($items->items()),$warehouseWorkers, 'links' => $items->links()->toHtml()], JSON_UNESCAPED_UNICODE);
    }

    public function getItemFromOrder(Request $request)
    {
        $items = OrderItem::getOrderItems($request->orderId);
        return json_encode($items, JSON_UNESCAPED_UNICODE);
    }

    public function create(Request $request)
    {
        if (!$request->pickupId)
            return json_encode(['code' => 404], JSON_UNESCAPED_UNICODE);
        
        $data = PickupControll::where('orderId',$request->orderId)->First();
        if (!$data)
        {
            $data = new PickupControll;
            $data->orderId=$request->orderId;
        }
        $data->pickupId=$request->pickupId;
        $data->moderatorId=\Auth::guard('admin')->user()->id;
        $data->save();
        return json_encode(['code' => 200], JSON_UNESCAPED_UNICODE);
    }
    /**
     * сохранение документов на изменение для оператора
     * orderId - ID заказа
    */
    public function saveOrderCorrects(Request $request){
        $this->validate($request, ['orderId'=>'required|integer', 'corrects'=>'required|array']);
        $order = Orders::find($request->input('orderId'));
        if(!$order)
            return response()->json(['msg'=>"заказ <{$request->input('orderId')}> не найден", 'code' => 0], JSON_UNESCAPED_UNICODE);
        $head = 0;
        foreach($request->corrects as $cor){
            if (!is_array($cor))
                @$cor = json_decode($cor, 1);
            if(!$cor) continue; // если фигня передана
            $price = ItemsLink::getItemPrice($cor['itemId'], $cor['quantity']);
            if($cor['id']==0){
                $base = OrderItem::where('orderId', $order->id)->where('itemId', $cor['itemId'])->first();
                if($base && $base->quantity==$cor['quantity']) // если нет изменений документ не создаем
                    continue;
                $change = OrderChanges::where('orderId', $order->id)
                ->where('itemId', $cor['itemId'])
                ->where('closed', 0)
                ->where('id', \DB::raw("(select max(oc.id) from orderChanges as oc where oc.orderId={$order->id} and oc.itemId=orderChanges.itemId and oc.closed=0 and oc.initiatorPlace={$this->initiatorPlace_operator})"))
                ->first();
                if(!is_object($head))
                    $head = OrderChangesHead::create(['orderId' => $order->id]);
                if(!$change){
                    OrderChanges::create([ // документ для склада
                        'headId'         => $head->id,
                        'orderId'        => $order->id,
                        'itemId'         => $cor['itemId'],
                        'quantity'       => $cor['quantity'],
                        'price'          => $price['price'],
                        'priceType'      => $price['priceType'],
                        'initiatorId'    => \Auth::guard('admin')->user()->id,
                        'initiatorPlace' => $this->initiatorPlace_operator,
                        'changeId' => 0
                    ]);
                }
                
            }else{
                $change = OrderChanges::find($cor['id']);
            }
            if($change){
                if($cor['initiatorPlace'] == $this->initiatorPlace_operator)
                {
                    $change->update([
                        'closed' => 1,
                        'initiatorId' => \Auth::guard('admin')->user()->id,
                    ]);
                    if(!is_object($head))
                        $head = OrderChangesHead::create(['orderId' => $order->id]);
                    OrderChanges::create([ // документ для склада
                        'headId'         => $head->id,
                        'orderId'        => $order->id,
                        'itemId'         => $cor['itemId'],
                        'quantity'       => $cor['quantity'],
                        'price'          => $price['price'],
                        'priceType'      => $price['priceType'],
                        'initiatorId'    => \Auth::guard('admin')->user()->id,
                        'initiatorPlace' => $this->initiatorPlace_operator,
                        'changeId' => 0
                    ]);
                }
                else{
                    if($change->quantity != $cor['quantity']) // если кол-во от оператора отличается от корректировки склада
                    {
                        if(!is_object($head))
                            $head = OrderChangesHead::create(['orderId' => $order->id]);
                        OrderChanges::create([ // документ для склада
                            'headId'         => $head->id,
                            'orderId'        => $order->id,
                            'itemId'         => $cor['itemId'],
                            'quantity'       => $cor['quantity'],
                            'price'          => $price['price'],
                            'priceType'      => $price['priceType'],
                            'initiatorId'    => \Auth::guard('admin')->user()->id,
                            'initiatorPlace' => $this->initiatorPlace_operator,
                            'changeId' => 0
                        ]);
                    }
                }
            }
        }
        // закрываем документ склада
        $this->applayCorrects($order->id, $this->initiatorPlace_warehouse, \Auth::guard('admin')->user());
        $change = OrderChanges::where('orderId', $order->id)->where('closed', 0)->count();
        if($change==0){ // нет корректировок для заказа
            $notpicked = OrderItem::where('orderId', $order->id)->where(function($q){
                $q->where('pickTm', '0000-00-00 00:00:00')
                ->orWhereNull('pickTm');
            })->count();
            if($notpicked == 0){
                $order->pickupStatus = 4; // собран
                $order->save();
            }
        }else{
            $notpicked = OrderItem::where('orderId', $order->id)
            ->where('pickTm', '<>', '0000-00-00 00:00:00')
            ->whereNotNull('pickTm')
            ->count();
            if($notpicked > 0){ // если сборка уже начиналась
                $order->pickupStatus = 3; // корректировка
                $order->save();
            }
        }
        return response()->json(['msg'=>"корректировки заказ <{$request->input('orderId')}> сохранил", 'code' => 1], JSON_UNESCAPED_UNICODE);
    }
}
