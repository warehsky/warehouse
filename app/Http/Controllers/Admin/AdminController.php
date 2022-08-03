<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Orders;
use App\Model\OrderChanges;
use App\Model\OrderChangesHead;
use App\Http\Controllers\BaseController;
use App\Model\ItemsLink;
use App\Model\OrderLocks;
use Carbon\Carbon;

class AdminController extends BaseController
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
     * окно заказы
     */
    public function orders(Request $request){
        $phone = $request->input('phone') ?? '';
        $dFrom = $request->input('dFrom')||empty($request->input('dFrom'))?$request->input('dFrom'):Carbon::parse(Carbon::now());
        $dTo = $request->input('dTo')&&!empty($request->input('dTo'))?$request->input('dTo'):Carbon::parse(Carbon::now());
        $status = $request->input('status') ?? 0;
        $errorRate = $this->getOption('errorRate');
        $api_token = \Auth::guard('admin')->user()->getToken();
        
        $data = ['phone' => $phone, 'dFrom' => $dFrom, 'dTo' => $dTo, 'status' => $status, 'api_token' => $api_token, 'errorRate' => $errorRate];
        return view('Admin.orders', $data);
    }
    /**
     * окно сканирования весовых товаров
     */
    public function warehouse(Request $request){
        $phone = $request->input('phone') ?? '';
        $dFrom = $request->input('dFrom')||empty($request->input('dFrom'))?$request->input('dFrom'):Carbon::parse(Carbon::now());
        $dTo = $request->input('dTo')&&!empty($request->input('dTo'))?$request->input('dTo'):Carbon::parse(Carbon::now());
        $status = $request->input('status') ?? 1;
        $errorRate = $this->getOption('errorRate');
        $api_token = \Auth::guard('admin')->user()->getToken();
        $packs = collect(ItemsLink::getPacks());
        
        $data = ['phone' => $phone, 'dFrom' => $dFrom, 'dTo' => $dTo, 'status' => $status, 'api_token' => $api_token, 'errorRate' => $errorRate, 'packs' => $packs];
        return view('Admin.warehouse', $data);
    }
    /**
     * окно сканирования пакетов
     */
    public function warehousepacks(Request $request){
        $phone = $request->input('phone') ?? '';
        $dFrom = $request->input('dFrom')||empty($request->input('dFrom'))?$request->input('dFrom'):Carbon::parse(Carbon::now());
        $dTo = $request->input('dTo')&&!empty($request->input('dTo'))?$request->input('dTo'):Carbon::parse(Carbon::now());
        $status = $request->input('status') ?? "[5,8,4,2]";
        $errorRate = $this->getOption('errorRate');
        $api_token = \Auth::guard('admin')->user()->getToken();
        $packs = collect(ItemsLink::getPacks());
        
        $data = ['phone' => $phone, 'dFrom' => $dFrom, 'dTo' => $dTo, 'status' => $status, 'api_token' => $api_token, 'errorRate' => $errorRate, 'packs' => $packs];
        return view('Admin.warehousepacks', $data);
    }
    /**
     * сохранение документов на изменение для оператора
     * orderId - ID заказа
     * items - массив товаров
    */
    public function saveOrderChanges(Request $request){
        $orderId = (int)($request->input('orderId') ?? 0);
        $items = $request->input('items') ?? 0;
        if($orderId == 0)
            return response()->json(['msg'=>'не указан заказ', 'code' => 0], JSON_UNESCAPED_UNICODE);
        $order = Orders::find($orderId);
        if(!$order)
            return response()->json(['msg'=>'заказ не найден', 'code' => 0], JSON_UNESCAPED_UNICODE);
        if(!$items)
            return response()->json(['msg'=>'нет товаров', 'code' => 0], JSON_UNESCAPED_UNICODE);
        if(is_array($items))
            $_items = $items;
        else
            $_items = json_decode( $items );
        
        foreach($_items as $item){
            if($item->id){
                $ch = OrderChanges::find($item->id);
                $ch->updade(['quantity' => $item->quantity, 'moderatorId' => \Auth::guard('admin')->user()->id]);
            }else{
                $head = OrderChangesHead::create(['orderId' => $order->id]);
                $data = [
                          'headId' => $head->id,
                          'orderId' => $orderId,
                          'itemId' => $item->itemId,
                          'quantity' => $item->quantity, 
                          'initiatorId' => \Auth::guard('admin')->user()->id,
                          'initiatorPlace' => 1,
                        ];
                OrderChanges::create($data);
            }
        }
        return response()->json(['msg'=>'изменения сохранены', 'code' => 1], JSON_UNESCAPED_UNICODE);
    }
    /**
     * окно Документов
     */
    public function doc(Request $request){
        
        return view('Admin.doc');
    }
    /**
     * Проверка заказа на блокировку
     * Входные параметры:
     * orderId - ID заказа
     * edit - признак заказ берется на редактирование и блокируется
     * 
    */
    public function checkOrderLock(Request $request){
        $orderId = (int)($request->input('orderId') ?? 0);
        $locks = OrderLocks::find($orderId);
        $lock = [];
        if($locks){
            $user = $locks->user()->first();
            if($user->id != \Auth::guard('admin')->user()->id) // если не этот пользователь заблокировал
                $lock[] = ['id' => $user->id, 'name' =>  $user->name];
        }
        $edit = (int)($request->input('edit') ?? 0);
        if(count($lock)==0 && $edit > 0 && !$locks){
            OrderLocks::create(['orderId' => $orderId, 'userId' => \Auth::guard('admin')->user()->id]);
        }
        return response()->json(['lock' => $lock], JSON_UNESCAPED_UNICODE);
    }
    /**
     * Разблокировка заказа 
     * Входные параметры:
     * orderId - ID заказа
     * 
     * 
    */
    public function ordersUnlock(Request $request){
        $orderId = (int)($request->input('orderId') ?? 0);
        if($orderId < 0){
            if( \Auth::guard('admin')->user()->can('order_unlock') )
                $this->orderUnlock($orderId);
            else
                return response()->json(['success' => false], JSON_UNESCAPED_UNICODE);
        }else{
            $locks = OrderLocks::find($orderId);
            if($locks){
                $user = $locks->user()->first();
                if($user->id == \Auth::guard('admin')->user()->id) // если этот пользователь заблокировал
                    $this->orderUnlock($orderId);
                else
                    return response()->json(['success' => false], JSON_UNESCAPED_UNICODE);
            }
        }
        
        return response()->json(['success' => true], JSON_UNESCAPED_UNICODE);
    }
}
