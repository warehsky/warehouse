<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WebUsersDiscount extends Model
{
    protected $table = 'webUsersDiscount';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'id',
        'title',
        'type',
        'discount',
        'expiration',
        'orderId',
        'webUserId'
    ];
    /*
    * Проверка и получение промокода
    * Входные параметры:
    * promocode - промокод (хеш)
    * phone - телефон клиента (не обязательно поле)
    */
    public static function getPromoCode($promocode, $phone=0, $orderId=0, $created){
        $promo = WebUsersDiscount::where('title', $promocode)->first();
        if(!$promo) // код не найден
            return 0;
        if($promo->orderId>0 && $promo->orderId != $orderId) // промо код использован
            return 0;
        $now = Carbon::now();
        $start = Carbon::createFromTimeString($promo->startValidity)->startOfDay();
        $end = Carbon::createFromTimeString($promo->expiration)->endOfDay();
        if (!$now->between($start, $end)) // код просрочен или еще не вступил в действие
            return 0;
        if($phone==0 && $promo->webUserId>0){ // код привязан к телефону
            if($phone==0) // нет телефона
                return 0;
            $tel = WebUsers::find($phone);
            if(!$tel) // телефона нет в базе
                return 0;
            if($tel->id != $promo->webUserId) // код привязан к другому телефону
                return 0;
        }
        if($promo->friendId > 0){
            $orders = Orders::where('phone', $phone)->where('created_at', '<=', $created)->get();
            if($orders && count($orders) > 1) // если это не первый заказ
                return 0;
        }
        return $promo;
    }
}
