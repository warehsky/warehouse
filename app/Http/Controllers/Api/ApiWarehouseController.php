<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use PhpParser\Node\Expr\Cast\Array_;
use App\Http\Controllers\BaseController;
use App\Model\Admin;
use App\Model\Orders;
use App\Model\OrderChanges;
use App\Model\OrderChangesHead;
use App\Model\OrderItem;
use App\Model\ItemPropertys;
use App\Model\Items;
use App\Model\ItemsLink;
use App\Model\WarehouseItems;
use App\Model\PickupControll;
use App\Model\DeliveryAdd;
use App\Model\ItemGroups;
use App\Model\TimeWaves;
use App\Model\DeliverySots;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;
use Exception;


class ApiWarehouseController extends BaseController
{
    private $user=null;
    /**
    *  Получает входные параметры и преобразует их в объект
    */
    private function getData(Request $request){
        $rules = [
            'data' => 'required',
        ];
        $validation = \Validator::make($request->all(), $rules);

        if($validation->fails()){
            return ['data' => null, 'success'=> false, 'error' => ['code' => 7, 'msg' => $validation->messages()]];
        }
        @$data = json_decode($request->input('data'));
        
        if(!$data)
            return ['data' => null, 'success'=> false,  'error' => ['code' => 7, 'msg' => 'не верный формат json']];
            
        return ['data' => $data, 'error' => []];
    }
    /**
     * Логин на устройстве (для склада)
     *
     * 
     */
    public function warehouseLogin(Request $request)
    {
        $data = $this->getData($request);

        if(count($data['error'])){
            return ['data' => null, 'success'=> false, 'error' => [ 'code' => 7, 'msg' => 'ошибка данных']];
        }
        $data = $data['data'];
        try{
            
            if(!$data || !property_exists($data, 'login') || !property_exists($data, 'password') || !property_exists($data, 'tm') || 
               !property_exists($data, 'client'))
                throw new Exception('нет обязательных полей');
            $user = Admin::where('login', $data->login)->whereNull('deleted_at')->first();
            if(!$user)
                return response()->json(['success'=> false, 'data' => null, 'error' => ['code' => 5, 'msg' => 'пользователь не найден']], JSON_UNESCAPED_UNICODE);
            if(abs(intval($data->tm)-time())>(int)config('shop.LOGIN_TIMEMARK_DISTANCE'))
                return response()->json(['success'=> false, 'data' => null, 'error' => ['code' => 3, 'msg' => 'время на устройстве не верное']], JSON_UNESCAPED_UNICODE);
            if($data->password!=hash("sha256", $data->login . $user->warehousep . $data->tm))
                return response()->json(['success'=> false, 'data' => null, 'error' => ['code' => 1, 'msg' => 'не верно имя пользователя или пароль']], JSON_UNESCAPED_UNICODE);
            // проверка минимальной допустимой версии программы
            if($this->cmpVersion($this->getOption('MIN_VERSION'), $data->client)<0)
                return response()->json(['success'=> false, 'data' => null, 'error' => ['code' => 6, 'msg' => 'версия программы устарела']], JSON_UNESCAPED_UNICODE);
            $client =     property_exists($data, 'client') ? $data->client : "";
            $deviceId =   property_exists($data, 'deviceId') ? $data->deviceId : "";
            $deviceName = property_exists($data, 'deviceName') ? $data->deviceName : "";
            $session = hash("sha256", uniqid() . $user->id . $deviceId);
            $login_time = time();
            $session_end_time = time()+config('shop.SESSION_LEASE_TIME');
                
            $sql = "INSERT INTO sessions (`session`, userId, deviceId, deviceName, login_time, session_end_time, client) ".
                "VALUES ('{$session}', {$user->id}, '{$deviceId}', '{$deviceName}', {$login_time}, {$session_end_time}, '{$client}') ".
                "ON DUPLICATE KEY UPDATE ". 
                " session = '{$session}'," .
                " deviceName='{$deviceName}', login_time={$login_time}, session_end_time={$session_end_time}, client='{$client}';";
            \DB::connection()->select($sql);
            return response()->json(['success'=> true, 'data' => ['session' => $session]], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            return response()->json(['success'=> false, 'data' => null, 'error' => ['code' => 7, 'msg' => $e->getMessage()]], JSON_UNESCAPED_UNICODE);
        }

        
    }
    /**
     * проверка минимальной допустимой версии программы
     */
    private function cmpVersion($a, $b)
    {
        $a = explode(".", $a);
        $b = explode(".", $b);
        $len = max(count($a), count($b));
        for($i=0; $i<$len; $i++)
        {
            $ai = $i<count($a)?(int)$a[$i]:0;
            $bi = $i<count($b)?(int)$b[$i]:0;
            if($ai<$bi)
                return 1;
            if($ai>$bi)
                return -1;
        }
        return 0;
    }
    /**
     * Функция загрузки заказов назначенных для сборки рабочему
     * Входные параметры:
     * data = {"date":"2022-05-02","waves":"16:00-20:00","l":123,"tm":1643883793,"p":"98ae9f9c3d8b957a415e62682e490563c7f5feadb9251a6ede08f1b99ada5a3a"}
    */
    public function getPickupOrders(Request $request){
        $now = Carbon::now();

        $start = Carbon::createFromTimeString('20:00');
        $end = Carbon::createFromTimeString('08:00')->addDay();

        if ($now->between($start, $end)) { // не рабочее время у оператора
            $statuses = [1];
        }else
            $statuses = [2,5,6,7,8];
        $data = $this->getData($request);

        if(count($data['error']))
            return ['data' => null, 'success'=> false, 'error' => ['code' => 7, 'msg' => 'ошибка данных']];
        
        $data = $data['data'];
        $user = $this->ValidateLoginDev($data);
        if (!$user)  // проверка Аутентификации
            return ['data' => null, 'success'=> false, 'error' => ['code' => 7, 'msg' => 'ошибка Аутентификации']];
        $_sots = DeliverySots::all();
        $sots = [];
        foreach($_sots as $s) 
            $sots[$s->id] = $s->description;
        $this->user = $user;
        $wstart = Carbon::now()->timezone('Europe/Moscow')->subDays(4)->startOfDay()->format('Y-m-d H:i:s');
        $wend = Carbon::now()->timezone('Europe/Moscow')->endOfDay()->format('Y-m-d H:i:s');
        if(property_exists($data, 'date') && $data->date){
            $wstart = Carbon::parse($data->date)->timezone('Europe/Moscow')->startOfDay()->format('Y-m-d H:i:s');
            $wend = Carbon::parse($data->date)->timezone('Europe/Moscow')->endOfDay()->format('Y-m-d H:i:s');
        }
        $orders = Orders::select('orders.id', 'orders.deliveryZoneIn as cellId', 'orders.webUserId',
                                 'timeWaves.timeFrom', 'timeWaves.timeTo', 'orders.deliveryDate', 'orders.status', 'statuses.title as stitle', 'orders.pickupStatus', 'pickupStatuses.title as ptitle', 'pickupStatuses.color')
        ->join('timeWaves', 'timeWaves.id', 'orders.waveId')
        ->join('pickupStatuses', 'pickupStatuses.id', 'orders.pickupStatus')
        ->join('statuses', 'statuses.id', 'orders.status');
        if(property_exists($data, 'orderId') && $data->orderId){
            $orders = $orders->where('orders.id', $data->orderId);
        }
        else{
            $orders = $orders->whereBetween('orders.created_at', [$wstart, $wend])
            ->whereIn('orders.status',  $statuses);
            if(property_exists($data, 'waves') && $data->waves){
                $ww = explode('-', $data->waves);
                $waves = TimeWaves::select('id')->where('timeFrom', '>=', $ww[0])->where('timeTo', '<=', $ww[1])->pluck('id')->toArray();
                if(count($waves))
                    $orders = $orders->whereIn('orders.waveId',  $waves);
            }
        }
        $orders = $orders->orderBy('orders.deliveryDate', 'asc')
        ->orderBy('timeWaves.timeFrom', 'asc')
        ->get();
        foreach($orders as $o){
            $o->items = $this->getItems($o, 0);
            $o->cell = @$sots[$o->cellId];
            $o->timeWave = "{$o->deliveryDate} с {$o->timeFrom} по {$o->timeTo}";
            $o->status = ['id' => $o->status, 'title' => $o->stitle];
            $col = explode(',', $o->color);
            $o->pickupStatus = ['id' => $o->pickupStatus, 'title' => $o->ptitle, 'color' => $col];
            $o->unclosedCor = 0;
            if($o->pickupStatus['id'] == 3){
                $unclosedCor = OrderChanges::where('orderId', $o->id)
                ->where('initiatorPlace', $this->initiatorPlace_operator)
                ->where('closed', 0)
                ->count();
                if($unclosedCor > 0)
                    $o->unclosedCor = $this->initiatorPlace_operator;
                else{
                    $unclosedCor = OrderChanges::where('orderId', $o->id)
                    ->where('initiatorPlace', $this->initiatorPlace_warehouse)
                    ->where('closed', 0)
                    ->count();
                    if($unclosedCor > 0)
                        $o->unclosedCor = $this->initiatorPlace_warehouse;
                }
            }
            unset($o->stitle);
            unset($o->ptitle);
            unset($o->deliveryDate);
            unset($o->timeFrom);
            unset($o->timeTo);
            unset($o->color);
        }
        return response()->json(['success'=> true, 'data' => ['orders' => $orders]], JSON_UNESCAPED_UNICODE);
    }
    /**
     * Функция загрузки документов коррекции для заказа
     * Входные параметры:
     * orderId - ID - заказа
     */
    public function getPickupChanges(Request $request){
        $data = $this->getData($request);
        
        if(count($data['error'])){
            return response()->json($data['error'], JSON_UNESCAPED_UNICODE);
        }
        $data = $data['data'];
        $orderId = (int)$data->orderId;
        $changes = OrderChanges::where('orderId', $orderId)
        ->where('initiatorPlace', $this->initiatorPlace_operator)
        ->where('closed', 0)
        ->get();
        return response()->json(['success'=> true, 'data' => $changes], JSON_UNESCAPED_UNICODE);
    }
    /**
     * Функция начала сборки
     * Входные параметры:
     * data = {"orderId":1,"l":123,"tm":1643883793,"p":"98ae9f9c3d8b957a415e62682e490563c7f5feadb9251a6ede08f1b99ada5a3a"}
     */
    public function startPickup(Request $request){
        $data = $this->getData($request);

        if(count($data['error']))
            return ['data' => null, 'success'=> false, 'error' => ['code' => 7, 'msg' => 'ошибка данных']];
        
        $data = $data['data'];
        $user = $this->ValidateLoginDev($data);
        if (!$user)  // проверка Аутентификации
            return ['data' => null, 'success'=> false, 'error' => ['code' => 7, 'msg' => 'ошибка Аутентификации']];
        $this->user = $user;
        $orderId = (int)$data->orderId;
        if(!$orderId) // нет ID заказа
            return response()->json(['success'=> false, 'data' => null, 'error' => ['code' => 7, 'msg' => 'нет ID заказа (orderId)']], JSON_UNESCAPED_UNICODE);
        $order = Orders::find($orderId);
        if(!$order) // заказ не найден
            return response()->json(['success'=> false, 'data' => null, 'error' => ['code' => 7, 'msg' => 'заказ не найден']], JSON_UNESCAPED_UNICODE);
        $pickupControll = PickupControll::where('orderId', $orderId)->first();
        if( $pickupControll ){
            if($pickupControll && $pickupControll->pickupId != $user->id)
                return response()->json(['success'=> false, 'data' => null, 'error' => ['code' => 7, 'msg' => 'заказ заблокирован id пользователя '.$pickupControll->pickupId]], JSON_UNESCAPED_UNICODE);
        }else
            PickupControll::create(['orderId' => $orderId, 'pickupId' => $user->id, 'moderatorId' => 1]);
        
        $changes = [];
        if($order->pickupStatus==1){
            $this->applayCorrects($order->id, $this->initiatorPlace_operator, $user); // заказ еще не собирался, применяем корркектировки сразу
            Orders::where('id', $orderId)->update(["pickupStatus" => 2]); // изменяем статус заказа по сборке
        }else{
            $changes = OrderChanges::getOrderChanges($orderId);
        }
        $items = $this->getItems($order, 0);
        return response()->json(['success'=> true, 'data' => ['items' => $items, 'changes' => $changes]], JSON_UNESCAPED_UNICODE);
    }
    /**
     * Функция сохранения заказа на сборку
     * 
     *      *    */
    public function finishPickup(Request $request){
        $data = $this->getData($request);
        if(count($data['error']))
            return ['data' => null, 'success'=> false, 'error' => ['code' => 7, 'msg' => 'ошибка данных']];
        
        $data = $data['data'];
        $user = $this->ValidateLoginDev($data);
        if (!$user)  // проверка Аутентификации
            return ['data' => null, 'success'=> false, 'error' => ['code' => 7, 'msg' => 'ошибка Аутентификации']];
        $this->user = $user;
        $orderId = (int)$data->orderId;
        if(!$orderId)
            return response()->json(['success'=> false, 'data' => null, 'error' => ['code' => 7, 'msg' => 'нет ID заказа (orderId)']], JSON_UNESCAPED_UNICODE);
        if(!is_array($data->items))
            return response()->json(['success'=> false, 'data' => null, 'error' => ['code' => 7, 'msg' => 'не верный формат items']], JSON_UNESCAPED_UNICODE);
        $orderItems = OrderItem::where('orderId', $orderId)->get();
        $items = [];
        foreach($orderItems as $i)
            $items[$i->itemId] = $i;
        
        $dataitem = [];
        $status = 4;
        $head = 0;
        \DB::beginTransaction(); // начало транзакции
        foreach($data->items as $item){
            if($item->deliveryAdd>0){ // довозы
                $deliveryAdd = DeliveryAdd::find($item->deliveryAdd);
                if($deliveryAdd)
                    $deliveryAdd->update(["pickTm" => Carbon::parse($item->pickTm)->timezone('Europe/Moscow')->format('Y-m-d H:i:s'), "pickupQuantity" => $item->quantity,
                                          "closeId" => $user->id, "addedId" => $orderId, "status" => 3]);
                continue;
            }
            $dataitem[$item->id] = $item;
            $correct = OrderChanges::where('orderId', $orderId)
            ->where('itemId', $item->id)
            ->where('closed', 0)
            ->where('id', \DB::raw("(select max(oc.id) from orderChanges as oc where oc.orderId={$orderId} and oc.itemId=orderChanges.itemId and oc.closed=0 and oc.initiatorPlace={$this->initiatorPlace_operator})"))
            ->first();
            if( key_exists($item->id, $items)){ 
                
                if ($correct && ($item->idChangeOrder==$correct->id || $item->quantity==0) && $correct->quantity!=$item->quantity){ //Если корректировка обработана складом и сделан на нее вычерк
                    $correct->update(["closed" => 1]); //Закрываем обработаный документ и создаем новый уже для оператора
                    if(!is_object($head))
                        $head = OrderChangesHead::create(['orderId' => $orderId]);
                    OrderChanges::create([ // документ для оператора
                        'headId'   => $head->id,
                        'orderId'  => $orderId,
                        'itemId'   => $item->id,
                        'quantity' => $item->quantity,
                        'initiatorId' => $user->id,
                        'initiatorPlace' => $this->initiatorPlace_warehouse,
                        'changeId' => 0
                    ]);
                }

                if($correct && $item->idChangeOrder!=$correct->id){ // если корректировка еще не обработана складом 
                    $status = 3;
                    continue;
                }
                
                OrderItem::where('orderId', $orderId)
                ->where('itemId', $item->id)
                // ->where('quantity_warehouse', 0)  //только штучный товар
                ->update(["pickTm" => Carbon::parse($item->pickTm)->timezone('Europe/Moscow')->format('Y-m-d H:i:s')]);
                
                if($item->quantity!=$items[$item->id]->quantity and (!$correct || $correct->quantity != $item->quantity)){ // сравниваем кол-ва
                // создаем корректирующие документы
                    
                    
                    $changes = OrderChanges::where('orderId', $orderId)
                    ->where('itemId', $item->id)
                    ->where('closed', 0)
                    ->where('id', \DB::raw("(select max(oc.id) from orderChanges as oc where oc.orderId={$orderId} and oc.itemId=orderChanges.itemId and oc.closed=0 and oc.initiatorPlace={$this->initiatorPlace_warehouse})"))
                    ->first();
                    if(!is_object($head))
                        $head = OrderChangesHead::create(['orderId' => $orderId]);
                    if(!$changes)
                        OrderChanges::create([ // документ для оператора
                            'headId'   => $head->id,
                            'orderId'  => $orderId,
                            'itemId'   => $item->id,
                            'quantity' => $item->quantity,
                            'initiatorId' => $user->id,
                            'initiatorPlace' => $this->initiatorPlace_warehouse,
                            'changeId' => 0
                        ]);
                    else{
                        if($items[$item->id]->quantity != $changes->quantity && ($item->quantity != $changes->quantity || $item->idChangeOrder==0)){
                            $changes->update(["closed" => 1,"moderatorId" => $user->id]);
                            $changes->save();
                            if(!is_object($head))
                                $head = OrderChangesHead::create(['orderId' => $orderId]);
                            OrderChanges::create([ // документ для оператора
                                'headId'   => $head->id,
                                'orderId'  => $orderId,
                                'itemId'   => $item->id,
                                'quantity' => $item->quantity,
                                'initiatorId' => $user->id,
                                'initiatorPlace' => $this->initiatorPlace_warehouse,
                                'changeId' => 0
                            ]);
                        }
                    }
                }
            }else{
                $price = ItemsLink::getItemPrice($item->id, $item->quantity);
                if($item->quantity==$correct->quantity){
                    if(!is_object($head))
                        $head = OrderChangesHead::create(['orderId' => $orderId]);
                    OrderItem::create([
                        'headId'   => $head->id,
                        'orderId'  => $orderId,
                        'itemId'   => $item->id,
                        'quantity' => $item->quantity,
                        'quantity_base' => $item->quantity,
                        'price' => $price['price'],
                        'priceType' => $price['priceType'],
                        'pickTm' => Carbon::parse($item->pickTm)->timezone('Europe/Moscow')->format('Y-m-d H:i:s') 
                    ]);
                }
                else{
                    $correct->update(["closed" => 1]);
                    if(!is_object($head))
                        $head = OrderChangesHead::create(['orderId' => $orderId]);
                    OrderChanges::create([ // документ для оператора
                        'headId'   => $head->id,
                        'orderId'  => $orderId,
                        'itemId'   => $item->id,
                        'quantity' => $item->quantity,
                        'initiatorId' => $user->id,
                        'initiatorPlace' => $this->initiatorPlace_warehouse,
                        'changeId' => 0
                    ]);
                }
            }
        }
        \DB::commit(); // конец транзакции
        $corrects = OrderChanges::where('orderId', $orderId)
        ->where('closed', 0)
        ->where('id', \DB::raw("(select max(oc.id) from orderChanges as oc where oc.orderId={$orderId} and oc.itemId=orderChanges.itemId and oc.closed=0 and oc.initiatorPlace={$this->initiatorPlace_operator})"))
        ->get();
        
        foreach($corrects as $c)
            if(!key_exists($c->itemId, $dataitem)){ // если оператор добавил новую позицию
                $status = 3;
                break;
            }
        if($status==4)
            $this->applayCorrects($orderId, $this->initiatorPlace_operator, $user);
        $changes = OrderChanges::getOrderChanges($orderId);
        
        PickupControll::where('orderId', $orderId)->delete(); // удаляем блокировку
        if(count($changes)>0){ // если есть изменения выходим
            $status = 3;
            Orders::where('id', $orderId)->update(["pickupStatus" => $status]);
            return response()->json(['success'=> true, 'data' => ['msg' => 'есть не обработанные изменения', 'changes' => $changes]], JSON_UNESCAPED_UNICODE);
        }
        Orders::where('id', $orderId)->update(["pickupStatus" => $status]);
        
        return response()->json(['success'=> true, 'data' => ['msg' => 'специально для Коли - Успех!!!']], JSON_UNESCAPED_UNICODE);
    }
    /**
     * получить товары
    */
    private function getItems($order, $cor=0){
        if($cor)
            $this->applayCorrects($order->id, $this->initiatorPlace_operator, $this->user);
        $items = OrderItem::select('itemsLink.id', 'itemsLink.parentId', 'itemsLink.longTitle as title', 'orderItem.quantity', 'two.wsort', 'one.wsort as wsort2', \DB::raw('UNIX_TIMESTAMP(pickTm) as pickTm'),  \DB::raw('0 as deliveryAdd'))
        ->join('itemsLink', "itemsLink.id", "orderItem.itemId")
        ->join('itemGroups as one', 'one.id', 'itemsLink.parentId')
        ->join('itemGroups as two', 'two.id', 'one.parentId')
        // ->join('itemPropertys as i', function ($join) {
        //     $join->on('i.itemId', '=', 'itemsLink.id');
        //     $join->on('i.title', '=', 'Бренд');
        // })
        ->where('orderId', $order->id)
        // ->orderBy('two.wsort')
        // ->orderBy('i.value')
        ->get();
        $items = $this->addDeliveryAdds($items, $order->webUserId, $order->id);
        $order->freez = 0;
        $order->fenix = 0;
        $freez = ItemGroups::select('id')->where('parentId', 134)->pluck('id')->toArray();
        foreach($items as $i){
            $item = Items::find($i->id);
            if($item){
                $i->barCode = $item->barCode;
                $i->supplierArticle = $item->supplierArticle ?? "";
                $i->article = $item->article ?? "";
            }
            else{
                $i->barCode = "";
                $i->supplierArticle = "";
            }
            switch($i->parentId){
                case in_array($i->parentId, $freez):
                    $order->freez = 1;
                    break;
                case 116:
                    $order->fenix = 1;
                    break;
            }
            $props = ItemPropertys::where('itemId', $i->id)
            ->where(function ($query){
                $query->where('title', 'Бренд')
                 ->orWhere('title', 'Производитель');
            })
            ->orderBy('title')
            ->get();
            $supplier = '';
            foreach($props as $p){
                if($p->title == 'Бренд')
                    $supplier = $p->value;
                if($p->title == 'Производитель'){
                    $supplier .= " / ".$p->value;
                    break;
                }
            }
            $i->supplier = $supplier;
            $i->image = "https://mt.delivery/img/img/items/small/{$i->id}.png";
            $warehouseItem = WarehouseItems::where('itemId', $i->id)
            ->where('warehouseId', 39)
            ->first();
            if($warehouseItem)
                $i->remind = $warehouseItem->quantity;
            else
                $i->remind = 0;
            $i->newQuantity = $i->quantity;
            if($i->deliveryAdd==0)
                $i->pickedQuantity = $i->pickTm == null || $i->pickTm == 0 ? 0 : $i->quantity;
            else
                $i->pickedQuantity = $i->pickupQuantity;
        }
        $items = $items->sortBy(function($row) {
            $sort = $row->wsort<10 ? "00{$row->wsort}" : ($row->wsort<100 ? "0{$row->wsort}" : $row->wsort);
            $sort .= $row->wsort2<10 ? "00{$row->wsort2}" : ($row->wsort2<100 ? "0{$row->wsort2}" : $row->wsort2);
            return sprintf('%-12s%s', $sort, $row->supplier);
        });
        return $items->values()->all();
    }
    /**
     * добавляет довоз к заказу если есть
    */
    private function addDeliveryAdds($items, $webUserId, $orderId){
        $adds = DeliveryAdd::select('itemsLink.id', 'itemsLink.parentId', 'itemsLink.longTitle as title', 'deliveryAdd.pickupQuantity as pickupQuantity', 'deliveryAdd.confirmQuantity as quantity', 'two.wsort', 'one.wsort as wsort2', \DB::raw('UNIX_TIMESTAMP(pickTm) as pickTm'),  'deliveryAdd.id as deliveryAdd')
        ->join('itemsLink', "itemsLink.id", "deliveryAdd.itemId")
        ->join('itemGroups as one', 'one.id', 'itemsLink.parentId')
        ->join('itemGroups as two', 'two.id', 'one.parentId')
        ->join('orders as o', 'o.id', 'deliveryAdd.orderId')
        ->where('o.webUserId', $webUserId)
        ->where('deliveryAdd.status', 2)
        ->where('deliveryAdd.addedId', 0)
        ->get();
        if($adds && count($adds)>0)
        $items = $items->merge($adds);
        $adds = DeliveryAdd::select('itemsLink.id', 'itemsLink.parentId', 'itemsLink.longTitle as title', 'deliveryAdd.pickupQuantity as pickupQuantity', 'deliveryAdd.confirmQuantity as quantity', 'two.wsort', 'one.wsort as wsort2', \DB::raw('UNIX_TIMESTAMP(pickTm) as pickTm'),  'deliveryAdd.id as deliveryAdd')
        ->join('itemsLink', "itemsLink.id", "deliveryAdd.itemId")
        ->join('itemGroups as one', 'one.id', 'itemsLink.parentId')
        ->join('itemGroups as two', 'two.id', 'one.parentId')
        ->join('orders as o', 'o.id', 'deliveryAdd.orderId')
        ->where('o.webUserId', $webUserId)
        ->where('deliveryAdd.addedId', $orderId)
        ->get();
        if(!$adds || count($adds)==0) 
            return $items;
        else
            return $items->merge($adds);
    }
    /**
     * Подтверждает (склад) документ довоза
     * Входные параметры:
     * data = {"items":[{"id":ID документа довоза, "confirmQuantity": подтверждаемые кол-ва}],"l":123,"tm":1643883793,"p":"98ae9f9c3d8b957a415e62682e490563c7f5feadb9251a6ede08f1b99ada5a3a"}
     * 
     */
    public function confirmDeliveryAdd(Request $request){
        $data = $this->getData($request);
        if(count($data['error']))
            return ['success'=> false, 'error' => ['code' => 7, 'msg' => 'ошибка данных']];
        
        $data = $data['data'];
        $user = $this->ValidateLoginDev($data);
        if (!$user)  // проверка Аутентификации
            return ['success'=> false, 'error' => ['code' => 7, 'msg' => 'ошибка Аутентификации']];
        if(!is_array(@$data->items))
            return ['success'=> false, 'error' => ['code' => 7, 'msg' => 'ошибка данных items должен быть массив']];
        foreach($data->items as $i){
            $id = $i->id ?? 0;
            $confirmQuantity = $i->confirmQuantity ?? 0;
            if(!$id) continue;
            $deliveryadd = DeliveryAdd::find($id);
            if(!$deliveryadd) continue;
            $now = Carbon::now()->timezone('Europe/Moscow')->format("Y-m-d H:i:s");
            if($confirmQuantity && $confirmQuantity==$deliveryadd->quantity)
                $status = 2;
            else
                $status = 1;
            $deliveryadd->update(['confirmQuantity' => $confirmQuantity, 'confirmId' => $user->id, 'status' => $status, 'pickTm' => $now]);
        }
        return response()->json(['success'=> true, 'data' => ['msg' => 'документы довоза подтверждены']], JSON_UNESCAPED_UNICODE);
    }
    /**
     * Возвращает не подтвержденные документы довоза
     * Входные параметры:
     * data = {"l":123,"tm":1643883793,"p":"98ae9f9c3d8b957a415e62682e490563c7f5feadb9251a6ede08f1b99ada5a3a"}
     * 
     */
    public function getDeliveryAdds(Request $request){
        $data = $this->getData($request);
        if(count($data['error']))
            return ['success'=> false, 'data' => [], 'error' => ['code' => 7, 'msg' => 'ошибка данных']];
        
        $data = $data['data'];
        $user = $this->ValidateLoginDev($data);
        if (!$user)  // проверка Аутентификации
            return ['success'=> false, 'data' => [], 'error' => ['code' => 7, 'msg' => 'ошибка Аутентификации']];
        $deliveryAdds = DeliveryAdd::getNoConfirmDeliveryAdds();
        return ['success'=> true, 'data' => ['deliveryAdds' => $deliveryAdds]];
    }
}
