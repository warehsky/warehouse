<?php

namespace App\Http\Controllers;

use App\Model\Admin;
use Illuminate\Http\Request;
use App\Model\Items;
use App\Model\ItemsLink;
use App\Model\ItemsPercent;
use App\Model\Options;
use App\Model\WebUsersDiscount;
use App\Model\WebUsers;
use App\Model\Orders;
use PhpOption\Option;
use App\Model\DeliveryZones;
use App\Model\TimeWaves;
use App\Model\TimeWaveDisable;
use App\Model\OrderLocks;
use App\Model\Sessions;
use App\Model\OrderChanges;
use Carbon\Carbon;

class BaseController extends Controller
{
    public $initiatorPlace_warehouse = 1;
    public $initiatorPlace_operator = 2;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }
    /**
     * Проверка параметров защищенного входа пользователей приложения
     * Входные параметры:
     * l - логин (номер телефона)
     * p - Аутентификационный код, или контрольная сумма, полученная из набора параметров
     * tm - метка времени (timestamp нужна для получения контрольнай суммы)
    */
    public function ValidateLoginDev($data) {
        $p = property_exists($data, 'p') ? $data->p : 0;
        $tm = property_exists($data, 'tm') ? $data->tm : 0;
        $l = property_exists($data, 'l') ? $data->l : 0;
        if ($p && $l && $tm) {
            $user = Admin::where('login', $l)->first();
            if(!$user)
                return 0;
            if(env('WAREHOUSE_TEST', false)) // для тестирования
                return $user;
            $userMobile = Sessions::where('userId', $user->id)->where('session', '<>', '')->get();
            if ($userMobile) {
                foreach ($userMobile as $m) {
                    $token = hash("sha256", $l . $m->session . $tm);
                    if ($p == $token)
                        return $user;
                }
            }
        }
        return 0;
    }
    public function getItemPrice($id){
        $areaId = config('areaId', 1); // регион для цены
        $priceType =config('priceType', 2); // тип цены
        $good = ItemsLink::find($id);
        if(!$good)
            return 0;
        $pers = ItemsPercent::getPercent($id);
        $price = Prices::where('areaId', $areaId)->where('priceType', $priceType)->where('itemId', $good->id)->first();
        
        if(!$price)
            return 0;
        return ($price->value + $price->value * $pers);
    }
    /**
     * запрос к url
     */
    public function get_curl($url, $data_string, $metod="GET"){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $metod);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
        /*curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );*/
    
        $result = curl_exec($ch);
        if($result === false){
            $result =  'Ошибка curl: ' . curl_error($ch);
        }
        curl_close ($ch);
        return $result;
    }
    /**
     * запрос к API мобильного агента
     */
    public function mLogin($user){
        /** Login */
        $tm = time();
        $p = hash("sha256", $user->login.$user->password.$tm);
        $data = array('login' => $user->login, 'password' => $p, 'timemark' => $tm, 'client' => '0.100.2');
        
        $data_string = json_encode($data);

        $url = $this->url() . "Login";

        $result = $this->get_curl($url, array('login' => $data_string));
        
        if($result === false){
            $session = null;
        }else{
            $res = json_decode($result);
            
            $session = $res->data->session;
        }
        return $session;
    }
    /**
     * 
     */
    public function url(){
        
        $url_base = env("MOBILE_URL", "http://localhost/ServerAgent/www/sites/mobileagent/")."index.php/Api/";
        // $url_base = "http://api.intertorg.org/index.php/Api/";
        return $url_base;
    }
    /**
     * Возвращает опцию сайта, если нет параметра возвращает все
     */
    public function getOption($par=''){
        if($par=='')
          $option = Options::all();
        else
          $option = Options::getOption($par);   
        
        return $option;
    }

   

    /**
     * Возвращает json из файла
     */
    public function getJson($path){
        if (!\File::exists($path)) {
            throw new Exception("Invalid File");
        }

        $file = \File::get($path); // string
        $json = json_decode($file);
        return $json;
    }
    /**
     * возвращает таблицу цен по зонам доставки
     */
    public function get_DeliveryZones($id=-1, $zoneDisable=0){
        $sel = ['id', 'cost', 'limit_min', 'limit', 'limit_lgot', 'description', 'balloon', 'fill as fillColor', 'fillOpacity', 'stroke as strokeColor', 'strokeOpacity', 'zonePoligon as geometry', 'deleted', 'schedule'];
        if($id>=0){
            $costs = DeliveryZones::select($sel)
            ->where('id', $id)
            ->first();
            if(!$costs)
                $costs = [];
        }else{
            $costs = DeliveryZones::select($sel);
            if($zoneDisable > 0)
                $costs = $costs->where('deleted', 0);
            $costs = $costs->get();
        }
        return $costs;
    }
    /**
     * возвращает стоимомть доставки
     */
    public function getDeliveryCost($sum, $deliveryZone, $lgota=0) {
        $cost = $this->get_DeliveryZones( $deliveryZone);
        $limit = $cost->limit;
        if($lgota)
            $limit = $cost->limit_lgot;
        if ($sum < $limit)
            $deliveryCost = $cost->cost;
        else 
            $deliveryCost = 0;
        
        return $deliveryCost;
    }
    /**
     * исправляет формат телефона
     */
    public function getPhone($phone){
        if(!$phone) 
            return '';
        $phone = str_replace('(', '', $phone);
        $phone = str_replace(')', '', $phone);
        $phone = str_replace('-', '', $phone);
        $phone = str_replace(' ', '', $phone);
        return $phone;
    }
    /**
     * форматирует телефон
     */
    public function getPhoneMask($phone){
        $l = strlen($phone);
        $phone = substr_replace($phone, '-', ($l-2), 0);
        $phone = substr_replace($phone, '-', ($l-4), 0);
        $phone = substr_replace($phone, ') ', ($l-7), 0);
        $phone = substr_replace($phone, '(', ($l-10), 0);
        return $phone;
    }  
    /**
     * Проверяет, залогинен пользователь или нет
     * Входные параметры:
     * login - телефон пользователя
    */
    public function isLogin($login){
        if(!$login) return false;
        $phone = session('phone', 0);
        $login = $this->getPhone($login);
        if(!$phone || $phone!=$login) return false;
        $logintime = session('logintime', 0);
        if(!$logintime) return false;
        $now = Carbon::now()->timezone('Europe/Moscow');
        $last = Carbon::parse(($logintime))->timezone('Europe/Moscow');
        $diff = $now->diffInSeconds($last)/60;
        $waite = (int)$this->getOption('logintimeLife'); // времмя жизни сессии логина
        
        if($diff < $waite)
            return true;
        else
            return false;
    }
    /**
     * Вычисляет количество заказов на указанный номер за период с по текущий день
     * Входные параметры:
     * $phone - номер телефона
     * $from - период в днях (сколько дней вычесть из текущей даты, 0 - весь период)
    */
    public function checkCountPrevOrders($phone, $from=0){
        if(!$phone)
            return 0;
        if($from)
            $from = Carbon::now()->timezone('Europe/Moscow')->subDays($from)->endOfDay()->format("Y-m-d H:i:s");
        $phone = $this->getPhone($phone);
        if(strlen($phone)<10)
            return 0;
        $ordersCount = Orders::where('phone', $phone);
        if($from)
            $ordersCount = $ordersCount->where('created_at', '>', $from);
        $ordersCount = $ordersCount->count();
        
        return $ordersCount;
    }
    /**
     * отправка СМС
     */
    public function sendSms($phone, $msg){
        $data = array("phone" => $this->getPhone($phone), "msg" => $msg );
        $url = env("CODE_SMS_PATH", "172.20.20.16/sms-1") . "/sendSms";
        $result = $this->get_curl($url, $data);
        return true;
    }
    /**
     * возвращает таблицу цен по зонам доставки
     * Входные параметры:
     * id - ID волны (вернет одну запись)
     * zoneId - ID зоны (вернет волны для этой зоны)
     * t - если 1 то вернет волны доступные в это время суток
     */
    public function get_TimeWaves($id=0, $zoneId=0, $t=0){
        if((int)$id>0){
            $wave = TimeWaves::find($id);
        if(!$wave)
            $wave = [];
        }else{
            $now = Carbon::now()->timezone('Europe/Moscow')->format("H:i");
            $day = Carbon::now()->timezone('Europe/Moscow')->format("Y-m-d");
            $daytwo = Carbon::now()->timezone('Europe/Moscow')->addDay()->format("Y-m-d");
            if($zoneId)
                $wave = TimeWaves::where('zoneId', $zoneId);
            else
                $wave = TimeWaves::select('*');
            $day1 = $day;
            if($t){
                $disabled = TimeWaveDisable::whereDate('wdate', $day)
                ->where(function($q){
                    $q->whereNull('dateStart')
                    ->orWhere(\DB::raw('UNIX_TIMESTAMP(dateStart)'), '<', Carbon::now()->timezone('Europe/Moscow')->timestamp);
                })
                ->get();
                // foreach($disabled as $d)
                //     $wave = $wave->where('id', '<>', $d->waveId);
                $wave = $wave->where(\DB::raw("TIME_TO_SEC(`timeFrom`)"), '>', \DB::raw("delay*60+TIME_TO_SEC('{$now}')"))
                ->whereRAW("IF( (@countorderall := 
                IFNULL((
                    SELECT tw.countOrder FROM timeWavesOrderLimit as tw 
                    WHERE tw.waveId=`timeWaves`.id AND tw.date='{$day1}'),`timeWaves`.orderLimit))!=0,
                @countorderall>(
                    SELECT count(o.id) FROM orders as o 
                    WHERE `timeWaves`.timeFrom = DATE_FORMAT(o.deliveryFrom, '%H:%i:%s') 
                    and `timeWaves`.timeTo = DATE_FORMAT(o.deliveryTo, '%H:%i:%s') 
                    and o.deliveryDate='{$day1}' and o.status!=3 and o.status!=7),1) ");
            }else{
                $day1 = $daytwo;
                $disabled = TimeWaveDisable::whereDate('wdate', $daytwo)
                ->where(function($q){
                    $q->whereNull('dateStart')
                    ->orWhere(\DB::raw('UNIX_TIMESTAMP(dateStart)'), '<', Carbon::now()->timezone('Europe/Moscow')->timestamp);
                })
                ->get();
            }
            $wave = $wave->where('deleted', 0)
            ->OrderBy('timeFrom')
            ->get();

            if(!$wave)
                $wave = [];
            else{
                foreach($wave as $w){
                    $dis = 0;
                    foreach($disabled as $d)
                        if($d->waveId == $w->id)
                            $dis = 1;
                    $w->disabled = $dis;
                }
            }
        }
            
        return $wave;
    }

    /**
     * Создает промокод/дисконтную карту
     * Входящие параметры 
     *  id - массив кодов покупателей (если 0 то создается промокод, иначе дисконтная карта)
     *  count - количество создаваемых промокодов/диск. карт
     *  discount - размер скидки 
     *  type - тип промокода/дисконтной карты
     *  dateStart - дата начала действия 
     *  dateEnd - дата окончания действия 
     *  title - цифро-буквенный код для создания промокода
     *  friend - указывает что этот промокод является из акции "Приведи друга" (true - принадлежит)
     */
    public function generateDiscountCart($id, $count, $discount, $type, $dateStart, $dateEnd, $title='',$friend)
    {
        if (($dateEnd<Carbon::now()->timezone('Europe/Moscow')->startOfDay()->format('Y-m-d')) || ($dateEnd<$dateStart))
            return ['status'=>false, 'code'=>500];

        if (!$discount)
            return ['status'=>false, 'code'=>501];
        
        //Проверка количества промокодов, если не было введено ничего, создаем один промокод
        if (!isset($count) || $count==0)
            if (count($id)>1)
                $count=count($id);
            else 
                $count=1;
        $titleArray=[];
        $alphabet="ABCDEFGHIJKLMNOPQRSTUVWXYZ"; 
        $allPromocode=WebUsersDiscount::select('title')->get()->toArray();
        $allPromocode=array_column($allPromocode,'title');

        if (!in_array($title, $allPromocode))
        {
            if (strlen($title)<10)
            {
                for ($i = 0; $i < $count; $i++) 
                {
                    $flag=true;
                    while($flag)
                    {
                        if ($title)
                            $promo=$title;
                        else 
                            $promo='';
                        
                        if ($promo=='')
                            for($j = 0; $j < 5; $j++)
                                $promo.=$alphabet[rand(0,25)];

                        while (strlen($promo) < 10) 
                            $promo.=rand(0,9);
                        
                        if (!in_array($promo, $allPromocode) && !in_array($promo,$titleArray))
                            $flag=false;
                    }
                    $titleArray[]=$promo;       
                }
                
            }
            $time=Carbon::now()->timezone('Europe/Moscow')->startOfDay();
            if ($dateStart)
                $time=$dateStart;
            for ($i = 0; $i < $count; $i++) 
            {
                $newPromocode = new WebUsersDiscount;
                $newPromocode->title=$titleArray[$i];

                if (count($id)>1)
                    $code=$id[$i];
                else 
                    if (!empty($id))  
                        $code=$id[0];
                    else 
                        $code=0;
                if ($friend)
                    $newPromocode->friendId=$code;
                else 
                    $newPromocode->webUserId=$code;    
                
                    
                $newPromocode->type=$type;
                $newPromocode->startValidity=$time;
                $newPromocode->status=1;
                $newPromocode->expiration=$dateEnd;
                $newPromocode->discount=$discount;
                $newPromocode->save();        
            }
            return ['code'=>200]; 
        }
    }

        /**
     * Массовая расслыка сообщения покупателям 
     * При нахождении определенной подстроки в msg подставляет:
     *  #user - имя покупателя
     *  #promocode - цифро-буквенный код промокода
     *  #promostart - дата начала действия промокода
     *  #promoend - дата окончания действия промокода
     *  #promodiscount - процент скидки промокода
     * 
     * Входящие параметры
     *  ids - массив кодов пользователей
     *  msg - строка сообщения
     */
    //='Уважаемый #USER Вам доступен следующий промокод #promocode начинающий свое действие с #promoStart до #promoEnd'
    public function sendMassSms($ids,$msg,$friend=0)
    {
        $phones = WebUsers::select('phone','id')->whereIn('id',$ids)->get()->toArray();
        // $phones = array_column($phone, 'phone');
        
        foreach ($phones as $value)
        {
            $messages = $msg;
            if(stristr($msg, '#user') == true)
            {
                $val = $value['phone'];
                $userName = WebUsers::select('userName')->where('phone','LIKE',"%$val%")->get()->toArray();
                if (!empty($userName) && $userName[0]['userName']!='')
                    $name = $userName[0]['userName'];
                else 
                    $name = 'клиент';
                
                $messages = str_ireplace("#user", $name, $messages);
            }
            if ($friend)
            {
                $promocode = WebUsersDiscount::select('title','startValidity','expiration','discount')
                    ->where('status', 1)
                    ->where('friendId',$value['id'])
                    ->orderBy('id', 'DESC')
                    ->First();
            }
            else
            {
                $val = $value['phone'];
                $promocode = WebUsersDiscount::select('webUsersDiscount.title','webUsersDiscount.startValidity','webUsersDiscount.expiration','webUsersDiscount.discount')
                    ->join('webUsers', 'webUsers.id', '=', 'webUsersDiscount.webUserId')
                    ->where('webUsers.phone','LIKE',"%$val%")
                    ->where('webUsersDiscount.status', 1)
                    ->where('webUsersDiscount.orderId', 0)
                    ->orderBy('webUsersDiscount.id', 'DESC')
                    ->First();
            }
            if ($promocode)
            {
                $messages = str_ireplace("#promocode", $promocode['title'], $messages);
                $messages = str_ireplace("#promodiscount", $promocode['discount'], $messages);
                $messages = str_ireplace("#promostart", date('d.m.Y', strtotime($promocode['startValidity'])), $messages);
                $messages = str_ireplace("#promoend", date('d.m.Y', strtotime($promocode['expiration'])), $messages);
            }
            else
                break;

            // dd($value,$messages);
            $this->sendSms($value['phone'],$messages);
        }
        
    }
    /**
     * Разблокировка заказов
     */
    public function orderUnlock($orderId){
        if($orderId > 0)
            OrderLocks::where('orderId', $orderId)->delete();
        else
            OrderLocks::query()->truncate();
        return true;
    }
    /**
     * Применяет корректировки 
    */
    public function applayCorrects($orderId, $initiator, $user){
        $corrects = OrderChanges::select('*')
        ->where('orderChanges.orderId', $orderId)
        ->where('initiatorPlace', $initiator)
        ->where('closed', 0)
        ->count();
        if($corrects==0)
            return;
        \DB::beginTransaction();
        try{
            $sql = "UPDATE orderItem AS i INNER JOIN orderChanges AS c ON i.itemId=c.itemId SET i.quantity=c.quantity ";
            $sql .= "WHERE i.orderId={$orderId} AND c.initiatorPlace={$initiator} AND c.closed=0 AND ";
            $sql .= "c.id=(select max(oc.id) from orderChanges as oc where oc.orderId={$orderId} and oc.itemId=c.itemId and oc.closed=0 and oc.initiatorPlace={$initiator})";
            \DB::connection()->select( $sql );
            $sql = "INSERT INTO orderItem (orderId, itemId, quantity, quantity_base, price, priceType) SELECT {$orderId}, itemId, quantity, quantity as one, price, priceType ";
            $sql .= "FROM orderChanges as c WHERE c.initiatorPlace={$initiator} AND c.orderId={$orderId} and c.closed=0 and  !EXISTS(select id from orderItem as o where o.orderId={$orderId} and o.itemId=c.itemId)";
            \DB::connection()->select( $sql );
            $sql = "DELETE i FROM orderItem AS i INNER JOIN orderChanges AS c ON i.itemId=c.itemId ";
            $sql .= "WHERE c.orderId={$orderId} AND c.initiatorPlace={$initiator} AND c.closed=0 AND i.quantity=0;";
            \DB::connection()->select( $sql );

            $sql = "UPDATE orderChanges SET closed=1, moderatorId={$user->id} WHERE orderId={$orderId} AND initiatorPlace={$initiator} AND closed=0;";
            \DB::connection()->select( $sql );
        } catch(\Exception $e)
        {
            \DB::rollback();
            throw $e;
        }
        \DB::commit();
    }
}
