<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{
    protected $table = 'orderAddress';
    public $timestamps = true;
    protected $fillable = [
        'orderId',
        'lat',
        'lng',
        'deliveryZone',
        'deliveryZoneIn',
        'unknown',
        'country',
        'region',
        'province',
        'area',
        'locality',
        'district',
        'street',
        'house',
        'entrance',
        'route',
        'station',
        'metro_station',
        'railway_station',
        'vegetation',
        'hydro',
        'airport',
        'other',
        'houseReal',
        'entranceReal',
        'floor',
        'flat'
    ];
    protected $primaryKey = 'orderId';
    /**
     * Сохраняет адрес заказа
     * Входные параметры:
     * orderId - ID заказа
     * address - массив параметров адреса
     */
    public static function setAddress($orderId, $address){
        if(!$orderId)
            return null;
        $addr = OrderAddress::find($orderId);
        if(!$address)
            return $addr;
        if($addr){
            $addr->update($address);
        }else{
            $address["orderId"] = $orderId;
            $addr = OrderAddress::create($address);
        }
        return $addr;
    }
    /**
     * Возвращает строку адреса
    */
    public function getStrAddress(){
        $strAddr = "";
        if($this->locality && strlen($this->locality))
            $strAddr .= $this->locality;
        if($this->district && strlen($this->district))
            $strAddr .= ", " . $this->district;
        if($this->street && strlen($this->street))
            $strAddr .= ", " . $this->street;
        if($this->house && strlen($this->house))
            $strAddr .= ", " . $this->house;
        if($this->entrance && strlen($this->entrance))
            $strAddr .= ", " . $this->entrance;
        if($this->station && strlen($this->station))
            $strAddr .= ", " . $this->station;
        if($this->railway_station && strlen($this->railway_station))
            $strAddr .= ", " . $this->railway_station;
        if($this->vegetation && strlen($this->vegetation))
            $strAddr .= ", " . $this->vegetation;
        if($this->hydro && strlen($this->hydro))
            $strAddr .= ", " . $this->hydro;
        if($this->airport && strlen($this->airport))
            $strAddr .= ", " . $this->airport;
        if($this->other && strlen($this->other))
            $strAddr .= ", " . $this->other;    
        return $strAddr;
    }
}
