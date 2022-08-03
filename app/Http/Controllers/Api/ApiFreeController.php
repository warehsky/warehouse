<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Admin\TimeWavesController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use App\Model\Items;
use App\Model\TradeMark;
use App\Model\Tagent;
use App\Model\TradePoints;
use App\Model\Promocode;
use App\Model\Orders;
use App\Model\ItemGroups;
use App\Model\ItemsLink;
use App\Model\ItemsPercent;
use App\Model\WebUsersDiscount;
use App\Model\ItemPropertys;
use App\Model\GroupSuggests;
use App\Model\WebUsers;
use App\Http\Controllers\BaseController;
use App\Model\Comments;
use App\Model\OrderItem;
use App\Model\Vacancy;
use App\Model\PageStock;
use App\Model\TimeWaves;
use App\Model\DeliverySots;
use App\Model\PhonePrefixes;
use Carbon\Carbon;
use phpseclib\Crypt\Random;

class ApiFreeController extends BaseController
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
     * возвращает web группы товара в формате json
     */
    public function getMenuItems(Request $request)
    {
        $group = $request->input('groupId') ?? 0 ;
        $itemgroups = ItemGroups::getGroupsMap($group);
        
        return json_encode( $itemgroups, JSON_UNESCAPED_UNICODE );
    }
    /**
     * возвращает хлебные крошки в формате json
     */
    public function getCrumbs(Request $request)
    {
        $group = $request->input('groupId') ?? 0 ;
        if($group>0)
            $path = $this->getPath($group);
        else
            $path = [];
        
        return json_encode( $path, JSON_UNESCAPED_UNICODE );
    }
    /** 
     * Возвращает товарные позиции для выбранной группы товаров web каталога
     * входные параметры:
     * group код группы.
     * text строка для поиска по наименованию
     * tags массив тегов для поиска по тегам (например [31,32,33])
     * page номер страницы, по умолчанию 1
     * sort сортировка товаров, по умолчанию по популярности, допустимые значения - 'popular', 'priceup', 'pricedown'
     * price_from фильтр по цене начало диапазона
     * price_to фильтр по цене конец диапазона
     * 
     * 
    */
    public function getGroupData(Request $request)
    {
        $group = $request->input('group') ?? 0 ;
        $text = $request->input('text') ?? '' ;
        $tags = $request->input('tags') ?? [] ;
        $page = $request->input('page') ?? 0 ;
        $sort = $request->input('sort') ?? 'popular' ;
        $price_from = $request->input('price_from') ?? 0 ;
        $price_to = $request->input('price_to') ?? 2000000 ;
        $carusel = $request->input('carusel') ?? 0 ;
        if($tags && !empty($tags)){
            $tags = ltrim($tags, '[');
            $tags = rtrim($tags, ']');
            $tags = explode(',' , $tags);
        }
        $items = ItemsLink::getItems($group, $text, $tags, $page, $sort, $price_from, $price_to, [], $carusel);
        $groups = ItemGroups::getGroupsMap($group);
        $path = $this->getPath($group);
        
        return json_encode(['groups'=>$groups, 'items'=>$items['items'], 'path' => $path, 'inpage' => $items['inpage'],
                            'taggroups' => $items['taggroups'], 'minprice' => $items['minprice'], 'maxprice' => $items['maxprice']], 
                            JSON_UNESCAPED_UNICODE);
    }
    /**
     * Возвращает список групп 2-го уровня в виде json
     */
    public function getSearchGroups(Request $request){
        $groups = ItemGroups::select('id', 'title')
        ->where('parentId', '>', 0)
        ->orderBy('title', 'asc')
        ->get();

        return json_encode($groups);
    }
    /**
     * Возвращает результат рекурсивного поиска в товарах и подгуппах введенной строки для поиска
     * Входные параметры:
     * text - строка для поиска
     * 
     */
    public function getSearch(Request $request){
        if( !$request->input('text') )  // нет строки для поиска возвращаем пустой результат
            return json_encode(['groups'=>[], 'items'=>[]]);
        $group = $request->input('groupId') ?? 0 ;
        $items = ItemsLink::getItems($group, $request->input('text'));
        
        return json_encode(['items'=>$items]);
    }
    /** 
     * отправляет заказ 
     * входные параметры:
     * 
     * lat координата заказчика,
     * lng координата заказчика,
     * note - примечание,
     * name - имя заказчика, 
     * phone - телефон заказчика, 
     * addr - адрес заказчика,
     * deliveryZone => зона доставки,
     * deliveryZoneIn => сота доставки,
     * deliveryFrom => время доставки с,
     * deliveryTo => время доставки по,
     * items: [{ "id":номер, "quantity": количество}]
     */
    public function sendOrder(Request $request)
    {  
        if(!$request->input('items'))
            return json_encode(['message'=>'заказ не отправлен, нет товаров', 'orderId' => 0], JSON_UNESCAPED_UNICODE);
        
        $tpId = 45255;
        if($tpId==null || $tpId==0){
            $tp = TradePoints::where('client_id', \Auth::user()->clientId)->first();
            if($tp)
                $tpId = $tp->id;
        }else{
            $tp = TradePoints::find($tpId);
        }
        $contractType = $request->input('contractType');
        if(!$contractType)
            $contractType = "Ф1";
        if(!$request->input('deliveryTime'))
            $date_time_shipment = Carbon::now()->timezone('Europe/Moscow')->timestamp;
        
        if(!$request->input('phone'))
            return json_encode(['message'=>'заказ не отправлен, нет контактного телефона', 'orderId' => 0], JSON_UNESCAPED_UNICODE);
        if(!$request->input('addr'))
            return json_encode(['message'=>'заказ не отправлен нет адреса доставки', 'orderId' => 0], JSON_UNESCAPED_UNICODE);
         
        if($request->input('deliveryType'))
            $deliveryType = $request->input('deliveryType');
        else
            $deliveryType = 1;
        if($request->input('attributes'))
            $attributes = $request->input('attributes');
        else
            $attributes = '';
        if($request->input('coords')){
            $coord_lat = $request->input('coords')[0];
            $coord_lng = $request->input('coords')[1];
        }else{
            $coord_lat = 0;
            $coord_lng = 0;
        }
        $items = [];
        // $_items = $this->getItemsCart( $request->input('items') );
        $_items = json_decode( $request->input('items') );

        //$uId = \Auth::user()->tagentId;
        $uId = 1309; // !!!!!!!!!!!!!!! тестовый пользователь
        $user = Tagent::find($uId);
        $sum_total = 0;
        
        foreach($_items as $t){
            $itemLink = ItemsLink::find($t->id);
            if(!$itemLink)
                continue;
            $itemId = $itemLink->id;  
            // $price  = Prices::getPrices($user, $itemId);
            $price = ItemsLink::getItemPrice($itemId, $t->quantity);
            $percent = ItemsPercent::getPercent($t->id);
            $items[] = [
                'warehouse_id' => 0,
                'itemId' => $itemId,
                'price' => $price['price'],
                'quantity' => $t->quantity,
                'percent' => $percent,
                'priceType' => $price['priceType']
            ];
            $sum_total += $price['price']*$t->quantity;
        }
        $deliveryCost = $this->getDeliveryCost($sum_total, $request->input('deliveryZone'));
       
        $order_data = [
            'guid' => strtolower( (string)\Str::uuid() ),
            'sum_total' => $sum_total,
            'lat' => $coord_lat,
            'lng' => $coord_lng,
            'note' => $request->input('note'),
            'name' =>$request->input('name'), 
            'phone' => $request->input('phone'), 
            'addr' => $request->input('addr'),
            'deliveryCost' => $deliveryCost,
            "deliveryZone" => $request->input('deliveryZone'),
            "deliveryZoneIn" => $request->input('deliveryZoneIn'),
            "deliveryFrom" => $request->input('deliveryFrom'),
            "deliveryTo" => $request->input('deliveryTo'),
            'status' => 1,
            'items' => $items,
            'deviceType' => $request->input('deviceType')?$request->input('deviceType'):0,
            'deviceInfo' => $request->input('deviceInfo')?$request->input('deviceInfo'):''
        ];
        
        // $session = $this->mLogin($user);
        // $data = array("create_order" => json_encode(array("session" => $session, "orders" => $orders)) );

        // $url = $this->url() . "CreateOrder";
        // $result = $this->get_curl($url, $data);
        
        // $code = Orders::where('guid', $orders[0]['guid'])->first();
        $order = Orders::create($order_data);
        OrderItem::saveOrderItems($items , $order->id);
        if($order){
            $wuser = WebUsers::where('phone', $order->phone)->where('orderId', 0)->first();
            if($wuser)
                $wuser->update(['orderId' => $order->id]);
            else
                WebUsers::create(['phone' => $order->phone, 'code' => 0, 'orderId' => 0]);
            $msg = $this->getOption('smsCode') . $order->id . $this->getOption('smsCodeView') ;
            $this->sendSms($this->getPhone($order->phone), $msg);
            return json_encode(['msg'=>'заказ отправлен', 'code' => $order->id], JSON_UNESCAPED_UNICODE);
        }
        else
            return json_encode(['msg'=>'заказ не отправлен', 'code' => 0], JSON_UNESCAPED_UNICODE);
    }
    /**
     * проверка кода подверждения из СМС
     */
    private function checkCode($phone, $code){
        // if(!$phone || $code)
        //     return false;
        return true;
    }
    /**
     * Генерирует и отправляет код подтверждения по СМС
     */
    public function sendCodeSms(Request $request){
        if(!$request->input('phone'))
            return json_encode(['msg'=>'код подтверждения не отправлен, не указан номер телефона', 'code' => 0], JSON_UNESCAPED_UNICODE);
        $phone = $request->input('phone');
        $phone = $this->getPhone($phone);
        $code = rand(100000, 999999);
        $wuser = WebUsers::where('phone', $phone)->where('orderId', 0)->first();
        
        if(!$wuser){
            WebUsers::create(['phone' => $phone, 'code' => $code, 'orderId' => 0]);
            $send = $this->sendSms($phone, $code);
            if($send)
                return json_encode(['msg'=>'код подтверждения отправлен', 'code' => 1], JSON_UNESCAPED_UNICODE);
            else
                return json_encode(['msg'=>'код подтверждения не отправлен, ошибка шлюза смс', 'code' => 0], JSON_UNESCAPED_UNICODE);
        }
        else{
            $now = Carbon::now()->timezone('Europe/Moscow');
            $last = Carbon::parse(($wuser->createTm))->timezone('Europe/Moscow');
            $diff = $now->diffInSeconds($last)/60;
            $waite = (int)$this->getOption('codeWaite'); // частота посыла смс с одного номера
            if($diff < $waite)
                return json_encode(['msg'=>'код подтверждения не отправлен, прошло менее '.$waite.' мин', 'code' => 0], JSON_UNESCAPED_UNICODE);
            else{
                $wuser->update(['code' => $code]);
                $send = $this->sendSms($phone, $code);
                if($send)
                    return json_encode(['msg'=>'код подтверждения отправлен', 'code' => 1, 'code_life' => $this->getOption('codeLife')], JSON_UNESCAPED_UNICODE);
                else
                    return json_encode(['msg'=>'код подтверждения не отправлен, ошибка шлюза смс', 'code' => 0], JSON_UNESCAPED_UNICODE);
            }
        }
    }
    
    /**
     * проверка введенного кода из СМС
     * входные параметры:
     * phone - номер телефона
     * code - код СМС
     */
    public function checkSmsCode(Request $request){
        $phone = $request->input('phone');
        $code = $request->input('code');
        if(!$phone && !$code)
        return json_encode(['msg'=>'код подтверждения не верный, не указан номер телефона или код', 'code' => 0], JSON_UNESCAPED_UNICODE);
        
        $phone = str_replace('(', '', $phone);
        $phone = str_replace(')', '', $phone);
        $phone = str_replace('-', '', $phone);
        $phone = str_replace(' ', '', $phone);
        $wuser = WebUsers::where('phone', $phone)->where('code', $code)->first();
        if(!$wuser)
            return json_encode(['msg'=>'код подтверждения не верный1', 'code' => 0], JSON_UNESCAPED_UNICODE);
        else{
            $now = Carbon::now()->timezone('Europe/Moscow');
            $last = Carbon::parse(($wuser->createTm))->timezone('Europe/Moscow');
            $diff = $now->diffInSeconds($last)/60;
            if($diff > $this->getOption('codeLife'))
                return json_encode(['msg'=>'код подтверждения не верный='.$diff, 'code' => 0], JSON_UNESCAPED_UNICODE);
            else{
                return json_encode(['msg'=>'код подтверждения верный', 'code' => 1], JSON_UNESCAPED_UNICODE);
            }
        }
        return json_encode(['msg'=>'код подтверждения не верный', 'code' => 0], JSON_UNESCAPED_UNICODE);
    }
    /** 
     * Возвращает подробную информацию для указанных товаров 
     * входные параметры:
     * items - массив из ID товаров
     * 
     * 
    */
    public function getItems(Request $request)
    {
        $ids = $request->input('items') ?? [] ;
        if($ids && !empty($ids)){
            $ids = ltrim($ids, '[');
            $ids = rtrim($ids, ']');
            $ids = explode(',' , $ids);
            $oitems = ItemsLink::getItems(0, '', [], 0, 'popular', 0, 2000000, $ids);
            $items = $oitems['items'];
            
            if($items && count($items)>0){
                $path = $this->getPath($items[0]->parentId);
                foreach($items as $item){
                    $propertys = ItemPropertys::select('id', 'title', 'value')
                    ->where('itemId', $items[0]->id)
                    ->where(\DB::raw('LENGTH(TRIM(title))'), '>', 0)
                    ->where(\DB::raw('LENGTH(TRIM(value))'), '>', 0)
                    ->orderby('title')
                    ->get();
                    if($propertys)
                        foreach($propertys as $property)
                            if($property->title == 'Срок годности')
                                $property->value = $this->expiryFormat($property->value);
                    $item->propertys = $propertys;
                }
            }
            else{
                $path = [];
            }
        }else{
            $items = [];
            $path = [];
        }
        
        return json_encode(compact('items', 'path'));
    }
    /**
     * возвращает отформатированную строку
     * Входные параметры:
     * $days - срок годности в днях
     */
    private function expiryFormat($days){
        $days = str_replace(array(" ", chr(0xC2).chr(0xA0)), '', $days);
        
        if(!is_numeric($days))
            return $days;
        $years = round($days/365);
        $month = round(($days%365)/30);
        $dn = ($days%365)%30;
        if($month>=11){ // если 11 месяцев, скорее всего это год
            $years++;
            $month = 0;
            $dn = 0;
        }
        $ret = "";
        if($years>0){
            $dn = 0; // при сроке годности в годах дни не показываем
            $ms = "";
            if($years==1)
               $ms = "год";
            else
                if($years<5)
                    $ms = "года";
                else
                    $ms = "лет";
            $ret .= $years . " " . $ms . " ";
        }
        if($month>0){
            $ms = "";
            if($month==1)
               $ms = "месяц";
            else
                if($month<5)
                    $ms = "месяца";
                else
                    $ms = "месяцев";
            $ret .= $month . " " . $ms . " ";
        }
        if($dn>0 && $years<=0){
            $ms = "";
            if($dn==1)
               $ms = "день";
            else
                if($dn<5)
                    $ms = "дня";
                else
                    $ms = "дней";
            $ret .= $dn . " " . $ms;
        }
        return $ret;
    }
    /**
     * возвращает путь к каталогу
     */
    private function getPath($groupId){
        if($groupId > 0){
            $gr = ItemGroups::find($groupId);
            if($gr){
                $parentId = $gr->parentId;
                $path[] = ['id' => $gr->id, 'title' => $gr->title];
            }
            else
                $parentId = 0;
            for(;$parentId>0;){
                $gr = ItemGroups::find($parentId);
                if($gr){
                    $parentId = $gr->parentId;
                    $path[] = ['id' => $gr->id, 'title' => $gr->title];
                }
                else
                    $parentId = 0;
            }
            $path = array_reverse($path);
        }else
            $path = [];
        return $path;
    }
    /**
     * возвращает сопутствующие товары для указанного товара в формате json
     */
    public function getSuggestItems(Request $request)
    {
        if(!$request->input('itemId')) // нет товара
            return json_encode( [], JSON_UNESCAPED_UNICODE );
        $item = ItemsLink::find($request->input('itemId'));
        $groups = GroupSuggests::getSuggests($item->parentId);
        $items = [];
         
        foreach($groups as $group){
            $suggests = ItemsLink::getItems($group->id);
            $ar = [];
            foreach($suggests["items"] as $suggest)
                if($suggest->quantityAll && (int)$suggest->quantityAll>0)
                    $ar[] = $suggest;
            
            $count = count($ar);
            if($count > 0){
                if($count <= 2)
                    $items = array_merge($items, $ar);
                else{
                    $rand_keys = array_rand($ar, 2);
                    foreach($rand_keys as $i)
                        $items[] = $ar[$i];
                }
            }
        }
        shuffle($items);
        return json_encode( ['items' => $items], JSON_UNESCAPED_UNICODE );
    }
    /**
     * возвращает отзывы на товар для указанного товара в формате json
     * Входные параметры:
     * itemId - ID товара
     */
    public function getComments(Request $request)
    {
        if(!$request->input('itemId')) // нет товара
            return json_encode( [], JSON_UNESCAPED_UNICODE );
        $com = Comments::getComments(null, null, $request->input('itemId'), 1);
        $comments = $com->orderBy('created_at', 'desc')->get();
        $average_estimate = $com->select(\DB::raw('sum(estimate) as estimate'), \DB::raw('count(estimate) as count'))->where('estimate', '>', 0)->first();
        // $sqlset = "SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));";
        // \DB::connection()->select( $sqlset );
        $com = Comments::getComments(null, null, $request->input('itemId'), 1);
        $average_estimates = $com->select('estimate', \DB::raw('sum(estimate) as sumestimate'), \DB::raw('count(estimate) as count'))
        ->where('estimate', '>', 0)->where('status', '>', 0)
        ->groupBy('estimate')
        ->get();
        return json_encode( [
                                'comments' => $comments, 
                                'voices' => $average_estimate->count, 
                                'average_estimate' => $average_estimate->count>0?round($average_estimate->estimate/$average_estimate->count, 2):0, 
                                'average_estimates' => $average_estimates,
                            ], JSON_UNESCAPED_UNICODE );
    }
    /**
     * добавляет отзыв на товар
     * Входные параметры:
     * itemId - ID товара
     * comment - отзыв
     * estimate - оценка товара
     */
    public function addComment(Request $request)
    {
        if(!$request->input('itemId')) // нет товара
            return json_encode( ['msg'=>'отзыв не добавлен, нет товара', 'code' => 400], JSON_UNESCAPED_UNICODE );
        if(!$request->input('comment') && !$request->input('estimate')) // нет ни отзыва ни оценки
            return json_encode( ['msg'=>'отзыв не добавлен, нет ни отзыва ни оценки', 'code' => 400], JSON_UNESCAPED_UNICODE );
        Comments::create([
                            'userName' => $request->input('name'),
                            'comment' => $request->input('comment'), 
                            'estimate' => $request->input('estimate'),
                            'itemId' => $request->input('itemId')
                        ]);
        
        return json_encode( ['msg' => 'отзыв добавлен', 'code' => 200], JSON_UNESCAPED_UNICODE );
    }

    /**
     * Возвращает массив акций
     * Входные параметры:
     * status - параметр определяющий какие акции возвращать, активные или не активные (если не указано возвращаются активные акции)
    */
    public function getPageStock(Request $request)
    {
        if(!isset($request->status))
            $result=PageStock::where('status',1)->get()->toArray();
        else 
            $result=PageStock::where('status',$request->status)->get()->toArray();
        return json_encode($result);
    }

    /**
     * Возвращает массив требуемых вакансий
     * 
     * Входные параметры: 
     * vacancyRequired - параметр (0 или 1)  какие вакансии возвращать, требуемые или не требуемые в данный момент, по умолчанию возвращать все вакансии
     */
    public function getVacancy(Request $request)
    {
        if(isset($request->vacancyRequired))
            $allIds=Vacancy::select('id')->where('vacancyRequired',$request->vacancyRequired)->pluck('id')->toArray();
        else 
            $allIds=Vacancy::select('id')->pluck('id')->toArray();
        $result = array();
        foreach($allIds as $key)
        {
           $result[]=Vacancy::getItem($key);
        }
        return json_encode($result);
    }
    /**
     * возвращает таблицу цен по зонам доставки и полигоны сот
     * Входные параметры:
     * disabled - доступность соты (1 или 0)
     * zoneDisable - доступность зоны (1 или 0)
     * id - ID зоны
     * sotId - ID соты
     */
    public function getPoligons(Request $request)
    {
        $disabled = (int)($request->disabled ?? -1);
        $zoneDisable = (int)($request->zoneDisable ?? -1);
        $costs = $this->get_DeliveryZones(isset($request->id) ? ($request->id) : -1, $zoneDisable);
        $data = ['zones' => $costs];
        if(isset($request->sotId)){
            $sot = DeliverySots::select("id", "sotPoligon as geometry", "description", "deleted");
            if((int)$request->sotId > 0)
                $sot = $sot->where('id', (int)$request->sotId);

            if($disabled >= 0)
                $sot = $sot->where('deleted', $disabled);
            $sot = $sot->get();
            
            $data['sots'] = $sot;
        }
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    /**
     * возвращает таблицу цен по зонам доставки
     */
    public function getDeliveryZones(Request $request){
        $costs = $this->get_DeliveryZones(isset($request->id) ? ($request->id) : -1);
        return json_encode( $costs, JSON_UNESCAPED_UNICODE );
    }
    /**
     * возвращает таблицу волн доставки по зонам
     */
    public function getTimeWaves(Request $request){
        $waves = $this->get_TimeWaves((isset($request->id) ? ((int)$request->id + 1) : 0), ($request->zoneId ? $request->zoneId : 0), ($request->t ? $request->t : 0));
        return response()->json( $waves, JSON_UNESCAPED_UNICODE );
    }
    /**
     * возвращает таблицу волн доставки с группировкой по временным периодам
     */
    public function getTimeWavesGroup(Request $request){
        $waves = TimeWaves::select(\DB::raw('GROUP_CONCAT(id) as wId'),'timeFrom', 'timeTo')
        ->where('deleted', 0)
        ->groupBy(\DB::raw('timeFrom+timeTo'))
        ->get();
        return response()->json( $waves, JSON_UNESCAPED_UNICODE );
    }
    /**
     * Возвращает поля бонуса пользователя
     * Входные параметры:
     * phone -    телефон (он же ID)
     * 
     */
    public function getUserBonus(Request $request)
    {
        $rules = [
            'phone' => 'required'
        ];
        $validation = \Validator::make($request->all(), $rules);

        if($validation->fails()){
            return $validation->messages();
        } else{
            $phone = $this->getPhone($request->input('phone'));
            $webuser = WebUsers::where('phone', 'like', $phone)->first();
            
            if(!$webuser)
                return json_encode( ['msg' => 'профаил не найден', 'code' => 400, 'profile' =>[]], JSON_UNESCAPED_UNICODE );
        }
        
        return json_encode( ['msg' => 'профаил найден', 'code' => 200, 'bonus' => $webuser->bonus, 'proc' => $this->getOption('procOrderBonus')], JSON_UNESCAPED_UNICODE );
    }

    /** 
     * Изменяет поле autologin в таблице webUsers, опеределяющее нужен ли код подтверждения для покупателя
     * 
     * Входные параметры:
     * id - номер телефона
    */
    public function WebUsersChangeAutologin(Request $request)
    {
        $id=$request->id;
        if (isset($id))
        {   
            $id="+".$this->getPhone($id);
            $fin=WebUsers::where('phone',$id)->first();
            if(isset($fin))
            { 
                $fin->autologin=!$fin->autologin;
                $fin->save();
            }
            return json_encode($fin->autologin);
            //return redirect()->route('getAllWebUsers');
        }
    }

    /**
     * Функция добавляет пагинацию на массив полученный с базы
     * Входные параметры:
     * array - массив который нужно разбить
     * request - входящие переменные через адресную строку 
     * path - путь для кнопок пагинации
     */
    public function arrayPaginatorForManyPath($array, $request)
    {
        $page = $request->input('page') ?? 1;
        $perPage = 15;
        $offset = ($page * $perPage) - $perPage;
        return new  \Illuminate\Pagination\LengthAwarePaginator(array_slice($array, $offset, $perPage, true), count($array), $perPage, $page,
            ['path' => $request->path, 'query' => $request->query()]);
    }

    /**
     * Возвращает записи для таблицы "Покупатели"
     */

    public function getAllWebUsers(Request $request)
    {
       
        $phone=$request->id;
        $name=$request->name;
        $deliveryAdd = $request->delivAdd;

        $dateStart='2018-01-01 00:00:00';
        if ($request->dateStart)
            $dateStart = $request->dateStart;
        
        $dateEnd=Carbon::now()->timezone('Europe/Moscow')->endofDay();
        if ($request->dateEnd)
            $dateEnd = $request->dateEnd;
 
        $strWhere='';
        if ($phone) 
            $strWhere="WHERE i.phone LIKE '%{$this->getPhone($phone)}%'";

        if ($request->selectedIds)
            if ($strWhere=='')
                $strWhere="WHERE i.id NOT IN ($request->selectedIds)";
            else 
                $strWhere.="AND i.id NOT IN ($request->selectedIds)";
        if ($name)
            if ($strWhere=='')
                $strWhere="WHERE i.userName LIKE '%{$name}%'";
            else
                $strWhere.="AND i.userName LIKE '%{$name}%'";
        
        if ($deliveryAdd)
        {
            if ($strWhere=='')
                $strWhere=" WHERE ";
            else
                $strWhere.=" AND ";
            
            $strWhere.="(SELECT COUNT(da.id) FROM mtShop.deliveryAdd as da JOIN mtShop.orders as o ON o.id=da.orderId WHERE da.orderId = o.id AND da.status!=3 AND o.webUserId=i.id)>0 ";
        }


        if ($request->sortingField)
            $sort = $request->sortingField;
        else 
            $sort = 'i.createTm';

        $sql="SELECT i.*, COUNT(j.id) as countOrder, (
                SELECT count(d.id) FROM mtShop.deliveryAdd as d 
                JOIN mtShop.orders as o ON o.id=d.orderId 
                WHERE d.status!=3 AND o.webUserId=i.id) as countDeliveryAdd 
            FROM mtShop.webUsers AS i  
        LEFT JOIN mtShop.orders AS j ON i.id=j.webUserId AND j.status = 4 AND j.created_at BETWEEN '{$dateStart}' AND '{$dateEnd}' 
        $strWhere 
        GROUP BY i.id  
        ORDER BY $sort  $request->sortingMethod";
        $allUsers = \DB::select($sql);
        $last=[];
        $items = $this->arrayPaginatorForManyPath($allUsers, $request);
        if ($allUsers)
        {
            $sqlItems=implode(",", array_column($items->items(),'id'));
            
            $sql="SELECT i.webUserId as id,i.phone,i.sum_last,i.updated_at from mtShop.orders as i 
                    where i.webUserId in ($sqlItems) AND i.status=4
                    AND i.updated_at = (
                    SELECT MAX(updated_at) FROM mtShop.orders
                    WHERE webUserId =i.webUserId AND status=4 )";
            $lastOrder = \DB::select($sql);

            foreach($lastOrder as $key)
            {
                $last[$key->id]=array("sum_last"=>$key->sum_last, "updated_at"=>$key->updated_at, "lastOrder"=>$this->checkCountPrevOrders($key->phone,31)>0);
            }
        }
        return response()->json([array_values($items->items()),$last, 'links' => $items->links()->toHtml()]);
    }

    /**
     * Продливает время действия промокода
     * Входящие параметры 
     * id - id промокода 
     * newTime - новое время
     */
    public function extendPromocode(Request $request)
    {
        if ($request->id && $request->newTime)
        {
            $promo=WebUsersDiscount::find($request->id);
            $promo->expiration=$request->newTime;
            $promo->save();
            return json_encode( ['msg' => 'Дата изменена', 'code' => 200], JSON_UNESCAPED_UNICODE ); 
        }
        return json_encode( ['msg' => 'Нет id или даты ', 'code' => 404], JSON_UNESCAPED_UNICODE ); 
    }
    /**
     * Проверяет введенный промокод, что он существует в базе и не использованный 
     * Входящие параметры 
     * promocode - введенный промокод
     */
    public function checkPromocode(Request $request)
    {
        $endTime=Carbon::now()->timezone('Europe/Moscow')->endOfDay();
        $nowTime=Carbon::now()->timezone('Europe/Moscow')->startOfDay();
        if ($request->promocode)
        {
            if (strlen($request->promocode)==10)
            {
                $promocode=htmlspecialchars(strtoupper($request->promocode));

                $sql = "SELECT discount, friendId FROM webUsersDiscount WHERE title='$promocode' AND orderId=0 
                        AND expiration>='$nowTime' AND '$endTime'>=startValidity AND type!=1 AND status=1";
                $phone = $this->getPhone($request->input('phone'));
                $result = \DB::connection()->select($sql);
                if (count($result)){
                    if($result[0]->friendId > 0){  // если это акция приведи друга
                        if($phone == '' || strlen($phone) < 10)
                            return response()->json(['enabled' => false, 'discount' => 0], JSON_UNESCAPED_UNICODE );
                        else{
                            $sql = "select id, status from orders where phone like '{$phone}';";
                            $orders = \DB::connection()->select($sql);
                            if(count($orders) > 1) // не новый клиент
                                return response()->json(['enabled' => false, 'discount' => 0], JSON_UNESCAPED_UNICODE );
                        }
                    }
                    
                    return response()->json(['enabled' => true, 'discount' => $result[0]->discount], JSON_UNESCAPED_UNICODE );
                }
                else 
                    return response()->json(['enabled' => false, 'discount' => 0], JSON_UNESCAPED_UNICODE );
            }
            else 
                return response()->json(['enabled' => false, 'discount' => 0], JSON_UNESCAPED_UNICODE );
        }
        else 
        {
            $sql = "SELECT id FROM webUsersDiscount WHERE webUserId=0 AND orderId=0 
                    AND expiration>='$nowTime' AND '$endTime'>=startValidity AND type!=1 AND status=1";
            $result = \DB::connection()->select($sql);
            if (count($result))
            return response()->json(['enabled' => true, 'discount' => 0], JSON_UNESCAPED_UNICODE );
                else 
            return response()->json(['enabled' => false, 'discount' => 0], JSON_UNESCAPED_UNICODE );
        }
    }
    /**
     * Возвращает все купоны для определенного пользователя
     * Входящие параметры 
     * phone - номер телефона для которого осуществляется поиск
     */
    public function getUserCoupons(Request $request)
    {
        $coupons=[];
        if ($request->phone)
        {
            $phone=htmlspecialchars($this->getPhone($request->phone));
            $nowTime=Carbon::now()->timezone('Europe/Moscow')->startOfDay();
            $endTime=Carbon::now()->timezone('Europe/Moscow')->endOfDay();
            $sql = "SELECT i.id,i.title as promocode,i.type as intType,i.discount,i.expiration, UNIX_TIMESTAMP(i.expiration) as expirationtm,k.title as type FROM webUsersDiscount as i
                    JOIN webUsers as j on i.webUserId=j.id 
                    JOIN webUsersDiscountType as k on k.id=i.type
                    WHERE j.phone like '%$phone' AND i.orderId=0 
                    AND i.expiration>='$nowTime' AND '$endTime'>=i.startValidity AND status!=0 ORDER BY i.type";
            $coupons = \DB::connection()->select($sql);
            
            foreach($coupons as $key=>$item)
            {
                if($item->intType==1)
                    $coupons[$key]->disposable=false;
                else
                    $coupons[$key]->disposable=true;
                    
                //unset($coupons[$key]->intType);
            }
        }
            return response()->json($coupons, JSON_UNESCAPED_UNICODE );
    }
    /**
     * Возвращает используемые префиксы (коды стран) телефонов 
     * 
     */
    public function getPhonePrefixes(Request $request)
    {
        $phonePrefixes = PhonePrefixes::getPhonePrefixes();

        return json_encode(['phonePrefixes'=>$phonePrefixes], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Возвращает id активных волн в указанную дату
     * 
     */
    private function getEnabledTimeWaves($date,$now=0)
    {
        $sql = "SELECT id FROM timeWaves
                WHERE IF( @countorderall := 
                IFNULL((SELECT tw.countOrder FROM timeWavesOrderLimit as tw 
                        WHERE tw.waveId=timeWaves.id AND tw.date='{$date}'),timeWaves.orderLimit)!=0,
                @countorderall>=(SELECT count(o.id) FROM orders as o 
                                    WHERE o.waveId=timeWaves.id and o.deliveryDate='{$date}' 
                                            and o.status!=3 and o.status!=7),1) 
                AND timeWaves.id NOT IN (SELECT d.waveId FROM timeWaveDisable as d 
                                            WHERE d.waveId=timeWaves.id AND d.wdate='{$date}') 
                AND timeWaves.zoneId IN (SELECT tz.id FROM deliveryZones as tz WHERE deleted = 0)";
       
        if ($now)
            $sql .="AND TIME_TO_SEC(timeWaves.timeFrom)>timeWaves.delay*60+TIME_TO_SEC('{$now}') ";

                
            $sql.="ORDER BY timeWaves.timeFrom";
        $wave = \DB::connection()->select($sql);
        $wave = array_column($wave,'id');
        return $wave;
    }

    /**
     * Возвращает волны для показа их операторам, если дата не передана, находится первая доступная волна
     */
    public function getOrdersLimit(Request $request)
    {
        $day = Carbon::now()->timezone('Europe/Moscow')->format("Y-m-d");
        $dayTwo = Carbon::now()->timezone('Europe/Moscow')->addDay()->format("Y-m-d");
        $now = Carbon::now()->timezone('Europe/Moscow')->format("H:i");
        $timeFrom = $request->timeFrom ? $request->timeFrom : '00:00';
        $timeTo = $request->timeTo ? $request->timeTo : '23:59';
        if (!$request->date)
        {
            $enabledWaves = $this->getEnabledTimeWaves($day,$now);
            $date = $day;
            if(empty($enabledWaves))
            {
                $enabledWaves = $this->getEnabledTimeWaves($dayTwo);
                $date = $dayTwo;
                if (empty($enabledWaves))
                    $date = $day;
            }
        }
        else
        {
            $date=$request->date;
            $time = 0;
            if ($date==$day)
                $time = $now;
            
            $enabledWaves = $this->getEnabledTimeWaves($date,$time);
        }

        $sql="SELECT 
        timeWaves.id,
        DATE_FORMAT(timeFrom, '%H:%i') as timeFrom,
        DATE_FORMAT(timeTo, '%H:%i') as timeTo,
        timeWaves.zoneId,
        dz.description,
        (IFNULL((SELECT tw.countOrder FROM timeWavesOrderLimit as tw 
        WHERE tw.waveId=`timeWaves`.id AND tw.date='$date'),`timeWaves`.orderLimit)) as orderLimit,
        (SELECT count(o.id) FROM orders as o 
        WHERE `timeWaves`.timeFrom = DATE_FORMAT(o.deliveryFrom, '%H:%i:%s') 
            and `timeWaves`.timeTo = DATE_FORMAT(o.deliveryTo, '%H:%i:%s') 
            and o.deliveryDate='$date' 
            and o.status!=3 
            and o.status!=7) as orders 
        FROM timeWaves
        JOIN deliveryZones as dz ON dz.id=timeWaves.zoneId
        WHERE dz.deleted=0 AND timeWaves.timeFrom>='$timeFrom' AND timeWaves.timeTo<='$timeTo'";

        $allTimeWaves = collect(\DB::connection()->select($sql));
        if (!empty($enabledWaves) && !$request->timeFrom && !$request->timeTo)
        {
            $firstTimeWaves = array_values($allTimeWaves->where('id',$enabledWaves[0])->toArray());
            $timeFrom = $firstTimeWaves[0]->timeFrom;
            $timeTo = $firstTimeWaves[0]->timeTo;
        }

        
        
        $allTimeWaves = $allTimeWaves->toArray();
        
        $allTimeWaves = array_values($allTimeWaves);
        
        foreach($allTimeWaves as $key=>$value)
        {
            if(!empty($enabledWaves))
                if(in_array($value->id,$enabledWaves))
                    $allTimeWaves[$key]->status = 1; // Волна активная
                else 
                    $allTimeWaves[$key]->status = 0;
        }
        //dd(['date'=>$date,'timeFrom'=>$timeFrom,'timeTo'=>$timeTo,'value'=>$allTimeWaves]);
        return json_encode(['date'=>$date,'timeFrom'=>$timeFrom,'timeTo'=>$timeTo,'value'=>$allTimeWaves], JSON_UNESCAPED_UNICODE ); 
    }
}