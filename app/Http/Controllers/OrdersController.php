<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Orders;
use App\Model\OrderItem;
use App\Model\ExpenseItem;
use App\Model\OrderLocks;
use App\Model\Cargos;
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
        $orders = Orders::select('orders.*', 'clients.client')
        ->join('clients', 'orders.clientId', 'clients.id')
        ->with('orderItems')
        ->orderBy('updated_at', 'desc')->paginate($paginate);
        $now = Carbon::now()->timezone('Europe/Moscow')->startOfDay();
        
        $cargos = Cargos::where("deleted", 0)->orderBy('cargo', 'asc')->get();
        foreach($orders as $o){
            $last = Carbon::parse(($o->created_at));
            $o->days = $now->diffInDays($last);
            if($o->days==0)
                $o->days = 1;
            $o->count = count($o->orderItems);
            $o->sum_total = 0;
            foreach($o->orderItems as $item){
                $o->sum_total += $item->quantity*$item->price;
            }
            $o->sum_total *= $o->days;
        }
        return view('orders.index', compact('orders', 'cargos'));
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
        $order = Orders::select("orders.*", "clients.client")
        ->join("clients", "clients.id", "orders.clientId")
        ->where("orders.id", $id)
        ->with("orderItems")
        ->first();
        
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
                    $sql = "INSERT INTO orderItem (`orderId`, `itemId`, `price`, `quantity`,`note`) " .
                    "VALUES ({$_order['id']}, {$item['itemId']}, {$price}, {$item['quantity']}, '{$item['note']}') ".
                    "ON DUPLICATE KEY UPDATE `price`=VALUES(`price`), `quantity`=VALUES(`quantity`), `note`=VALUES(`note`)";
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

        $reminds = OrderItem::select('orderItem.price', 'orderItem.itemId', 'orderItem.quantity', 'orderItem.note', 'items.item', \DB::raw('sum(orderItem.quantity) as wcount'))
        ->join('orders', "orders.id", "orderItem.orderId")
        ->join("items", "items.id", "orderItem.itemId")
        ->where("orders.clientId", $id)
        ->groupBy("orderItem.itemId")
        ->groupBy("orderItem.price")
         ->get();
         $expenses = ExpenseItem::select('*', \DB::raw('sum(quantity) as ecount'))
        ->join('expenses', "expenses.id", "expenseItem.expenseId")
        ->where("expenses.clientId", $id)
        ->groupBy("itemId")
        ->groupBy("price")
        ->get();
        $exs = [];
        foreach($expenses as $ex)
            $exs[$ex->itemId] = $ex;
        
        foreach($reminds as $i=>$r){
            if(key_exists($r->itemId, $exs))
                $r->remind = (double)$r->wcount - (double)$exs[$r->itemId]->ecount;
            else
                $r->remind = (double)$r->wcount;
            if($r->remind <= 0)
                unset($reminds[$i]);
        }
        // array_values($reminds);
        return response()->json(['code'=>200, 'reminds'=>$reminds], JSON_UNESCAPED_UNICODE );
    }
    /**
     * Разблокировка заказов
     */
    public function orderUnlock(Request $request){
        $orderId = $request->input('orderId') ?? 0;
        if($orderId > 0)
            OrderLocks::where('orderId', $orderId)->delete();
        else
            OrderLocks::query()->truncate();
        return json_encode( ['success' => true], JSON_UNESCAPED_UNICODE );
    }
}
