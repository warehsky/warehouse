<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Model\WebUsersDiscount;
use App\Model\WebUsersSendSms;
use App\Model\WebUsers;
use App\Model\Orders;
use App\Http\Controllers\BaseController;

class PromocodeController extends BaseController
{
        /**
     * Проверяет введенный промокод, что он существует в базе и не использованный 
     * Входящие параметры 
     * promocode - введенный промокод
     * phone - проверяемый телефон для акции "Приведи друга" (У этого номера не должно быть заказов)
     */
    public function checkPromocode(Request $request)
    {
        $endTime = Carbon::now()->timezone('Europe/Moscow')->endOfDay();
        $nowTime = Carbon::now()->timezone('Europe/Moscow')->startOfDay();
        if ($request->phone && $this->getOption('bringFriendStatus'))
        {
            $phone=$this->getPhone($request->phone);
            $count=\DB::connection()->select("SELECT count(i.id) as count FROM orders as i 
                                                WHERE i.phone LIKE '%$phone%'")[0];
            if ($count->count>0)
                return response()->json(['enabled' => false, 'discount' => 0], JSON_UNESCAPED_UNICODE);
            else 
                return response()->json(['enabled' => true, 'discount' => 0], JSON_UNESCAPED_UNICODE);
        }
        else 
            if ($request->promocode) {
                if (strlen($request->promocode) == 10) {
                    $promocode = htmlspecialchars(strtoupper($request->promocode));

                    $sql = "SELECT discount FROM webUsersDiscount WHERE title='$promocode' AND orderId=0 
                            AND expiration>='$nowTime' AND '$endTime'>=startValidity AND type!=1 AND status=1";

                    $result = \DB::connection()->select($sql);
                    if (count($result))
                        return response()->json(['enabled' => true, 'discount' => $result[0]->discount], JSON_UNESCAPED_UNICODE);
                    else
                        return response()->json(['enabled' => false, 'discount' => 0], JSON_UNESCAPED_UNICODE);
                } else
                    return response()->json(['enabled' => false, 'discount' => 0], JSON_UNESCAPED_UNICODE);
            } else {
                $sql = "SELECT id FROM webUsersDiscount WHERE webUserId=0 AND orderId=0 
                        AND expiration>='$nowTime' AND '$endTime'>=startValidity AND type!=1 AND friendId=0 AND status=1";
                $result = \DB::connection()->select($sql);
                if (count($result))
                    return response()->json(['enabled' => true, 'discount' => 0], JSON_UNESCAPED_UNICODE);
                else
                    return response()->json(['enabled' => false, 'discount' => 0], JSON_UNESCAPED_UNICODE);
            }
    }

    /**
     * Возвращает все купоны для определенного пользователя
     * Входящие параметры 
     * phone - номер телефона для которого осуществляется поиск
     */
    public function getUserCoupons(Request $request)
    {
        $coupons = [];
        if ($request->phone) {
            $phone = htmlspecialchars($this->getPhone($request->phone));
            $nowTime = Carbon::now()->timezone('Europe/Moscow')->startOfDay();
            $endTime = Carbon::now()->timezone('Europe/Moscow')->endOfDay();
            $sql = "SELECT i.id,i.title as promocode,i.type as intType,i.discount,i.expiration, UNIX_TIMESTAMP(i.expiration) as expirationtm,k.title as type, i.webUserId FROM webUsersDiscount as i
                    JOIN webUsers as j on i.webUserId=j.id 
                    JOIN webUsersDiscountType as k on k.id=i.type
                    WHERE j.phone like '%$phone' AND i.orderId=0 
                    AND i.expiration>='$nowTime' AND '$endTime'>=i.startValidity AND status!=0 ORDER BY i.type";
            $coupons = \DB::connection()->select($sql);

            foreach ($coupons as $key => $item) {
                if ($item->intType == 1)
                    $coupons[$key]->disposable = false;
                else
                    $coupons[$key]->disposable = true;

                //unset($coupons[$key]->intType);
            }
        }
        return response()->json($coupons, JSON_UNESCAPED_UNICODE);
    }

    /**
     *  Создает дисконтную карту 
     *  Входящие параметры: 
     *  id - (int) код пользователя (
     *      Если создается дисконтная карта то записывается в поле webUserId, 
     *      если промокод по акции "Приведи друга то в поле friendId")
     *  discount (int) - процент скидки
     *  startValidity (date) - дата начала действия карты
     *  expiration (date) - дата окончания действия карты 
     *  friend (int) - флаг который показывает какую карту нужно создавать
     *      (0 - обычная дисконтная карта; 1 - акция "Приведи друга")
     *  msgSms (string) - строка которая будет отправлена человеку id которого был указан
     *      (Если передать пустую строку, отправки сообщения не будет) 
     */
    
    public function createDiscountCart($id,$discount,$startValidity,$expiration,$friend)
    {
        if (!$id) return ['code' => 500, 'msg' => 'Нет id'];
        if (!$discount || $discount<=0) return ['code' => 501, 'msg' => 'Нет процента скидки'];
        if (!$expiration || !$startValidity) return ['code' => 502, 'msg' => 'Ошибка даты'];
        $friend = $friend ? $friend : 0;

        $func=$this->generateDiscountCart([$id],'',$discount,2,$startValidity,$expiration,'',$friend);
        if ($func['code']==200)
        {
            return ['code' => 200,'promocode'=>$func['promocode']];
        }
        else 
            if ($func['code']==500)
                return ['code' => 503, 'msg' => 'Ошибка при создании'];
    }

            /**
     * Генерирует промокод/дисконтную карту
     * Входящие параметры 
     *  id - код покупателя
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
        // dd($id, $count, $discount, $type, $dateStart, $dateEnd, $title='',$friend);
        if (($dateEnd<Carbon::now()->timezone('Europe/Moscow')->startOfDay()->format('Y-m-d')) || ($dateEnd<$dateStart))
            return ['status'=>false, 'code'=>500];

        if (!$discount)
            return ['status'=>false, 'code'=>501];
        
        //Проверка количества промокодов, если не было введено ничего, создаем один промокод
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
            return ['code'=>200, 'promocode'=>$titleArray[0]]; 
        }
    }

    /**
     * Проверка наличия промокода "Приведи друга" у клиента 
     * Если он есть, проверка что последние сообщение ему приходило n дней назад
     *      Если прошло n дней, повторная отправка сообщения и вернуть промокод
     *      Иначе вернуть промокод
     * Иначе, если промокод можно сгенерировать (Активный клиент) генерация промокода и отправка сообщения об этом
     * Входные параметры 
     *  id - код покупателя
     */
    
     //friend - указатель какой код генерировать (0 - Обычная дисконтная карта, 1 - Акция "Приведи друга")

    public function checkPromocodeBringFriend($id)
    {
        if ($this->getOption('bringFriendStatus'))
        {
            $friend = 1;
            $now=Carbon::now()->timezone('Europe/Moscow')->startOfDay();

            $user = WebUsers::where('id',$id)->first();
            $stockBringFriend = WebUsersDiscount::where('friendId',$id)->where('expiration','>=',$now)->where('status', '>', 0)->first();
            if ($stockBringFriend)
            {
                // $days = $this->getOption('bringFriendLastSms');
                // $time = Carbon::now()->timezone('Europe/Moscow')->subDays($days)->endOfDay()->format("Y-m-d H:i:s");
                // $lastSms = WebUsersSendSms::whereRAW("((webUserId = $id) OR (phone LIKE '%$user->phone%')) AND created_at>='$time'")->first();
                // if (!$lastSms) //Сообщение отправлялось более чем n дней назад 
                // {
                //     $msgSMS = $this->getOption('bringFriendSMS');
                //     $this->sendMassSms($id, $msgSMS);
                // }
                return $stockBringFriend->title;
            }
            else 
            {
                $prevOrder = $this->checkPrevOrders($user->phone,31);
                if ($prevOrder)
                {
                    $discount = $this->getOption('bringFriendDiscount');
                    $countDay = $this->getOption('bringFriendDays');
                    $msgSMS = $this->getOption('bringFriendSMS');
                    $to = Carbon::now()->timezone('Europe/Moscow')->addDays($countDay)->startOfDay()->format("Y-m-d H:i:s");
                    $promo = $this->createDiscountCart($id, $discount, $now, $to, $friend, $msgSMS);
                    return $promo['promocode'];
                }
            }
        }
    }
}
