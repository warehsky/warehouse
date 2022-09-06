<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Orders;
use App\Model\OrderItem;
use Carbon\Carbon;

class OrdersController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('authapi');
    }
    /**
     * 
     */
    public function index(Request $request){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return redirect()->route('home');
        }
        $paginate = $this->getOption('orders_paginate');
        $orders = Orders::orderBy('updated_at', 'desc')->paginate($paginate);
        return view('orders.index', compact('orders'));
    }
    /**
     * окно редактирования заказа
     */
    public function edit(Request $request, $id){
        
        $dFrom = $request->input('dFrom')&&!empty($request->input('dFrom'))?$request->input('dFrom'):Carbon::parse(Carbon::now());
        $dTo = $request->input('dTo')&&!empty($request->input('dTo'))?$request->input('dTo'):Carbon::parse(Carbon::now());
        $status = $request->input('status') ?? 0;
        $api_token = \Auth::guard('admin')->user()->getToken();
        
        
        $data = ['orderId' => $id, 'dFrom' => $dFrom, 'dTo' => $dTo, 'status' => $status, 'api_token' => $api_token];
        return view('orders.order', $data);
    }
    /**
     * Возвращает заказ по ID
    */
    public function getOrder(Request $request){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return response()->json(['code'=>700]);
        }
        $id = $request->input('orderId') ?? 0;
        $order = Orders::where("id", $id)->with("orderItems")->first();
        
        return response()->json(['code'=>200, 'order'=>$order]);
    }
    /**
     * Сохраняет заказ
     */
    public function saveOrder(Request $request){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return response()->json(['code'=>700]);
        }
        @$order = $request->input('params')['order'];
        
        if(!$order)
            return json_encode( ['code' => 700, 'msg' => 'заказ не обновлен - нет данных'], JSON_UNESCAPED_UNICODE );
        if($order['id']>0)
            $_order = Orders::find($order['id']);
        else
            $_order = null;
        if($_order && ($_order["status"] == 100 ))
            return json_encode( ['code' => 700, 'msg' => 'заказ не обновлен - редактирование запрещено'], JSON_UNESCAPED_UNICODE );
        \DB::beginTransaction();
        try{
            $data = [

            ];
            $order['operatorId'] = \Auth::guard('admin')->user()->id;
            if($order['id']>0){
                if($_order)
                    $update = $_order->update($order);
                else
                    $update = false;
            }else{
                $_order = Orders::create($order);
                $update = true;
            }
            if($update){
                // Сохраняем перечень услуг
                $sum = 0;
                $ids = [];
                foreach($order['order_items'] as $item){
                    // @$itm = json_decode($item);
                    // if(!$itm) continue;
                    $price = $item['price'];
                    $sum += $price * $item['quantity'];
                    $sql = "INSERT INTO orderItem (`orderId`, `itemId`, `price`, `quantity`) " .
                    "VALUES ({$_order['id']}, {$item['itemId']}, {$price}, {$item['quantity']}) ".
                    "ON DUPLICATE KEY UPDATE `price`=VALUES(`price`), `quantity`=VALUES(`quantity`)";
                    $result = \DB::connection()->select( $sql );
                    $ids[] = $item['itemId'];
                }
                $orderItems = OrderItem::where('orderId', $order['id'])->get();
                foreach($orderItems as $orderItem){
                    if(!in_array($orderItem->itemId, $ids)){
                        OrderItem::where('orderId', $order['id'])->where('itemId', $orderItem->itemId)->delete();
                    }
                }
                $order=[
                    
                    "sum_total" => $sum,
                ];
                $update = $_order->update($order);
            }

        }catch(\Exception $e){
            \DB::rollback();
            throw $e;
        }
        \DB::commit();
        return json_encode( ['code' => 200, 'msg' => 'Заказ обновлен'], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Возвращает остатки Клиента
    */
    public function getReminds(Request $request){
        if (! \Auth::guard('admin')->user()->can('orders_all')) {
            return response()->json(['code'=>700]);
        }
        $id = $request->input('clientId') ?? 0;
        $reminds = OrderItem::select('*', "items.item", \DB::raw('count(orderItem.id) as wcount'), \DB::raw('count(orderItem.id) as remind'))
        ->join('orders', "orders.id", "orderItem.orderId")
        ->join("items", "items.id", "orderItem.itemId")
        ->where("orders.clientId", $id)
        ->groupBy("itemId")
         ->get();
        
        return response()->json(['code'=>200, 'reminds'=>$reminds], JSON_UNESCAPED_UNICODE );
    }
}
