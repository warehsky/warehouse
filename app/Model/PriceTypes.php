<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PriceTypes extends Model
{
    protected $table = 'priceTypes';
    public $timestamps = false;
    protected $connection = 'mtagent';
    /**
     * Возвращает цены по битовой маске
     */
    public static function getPriceTypesByType($type){
        $result = PriceTypes::select( 'id', 'value', 'title')->get();
        $data=[];
        foreach($result as $price){
            if( $price->value & $type){
                $data[]= $price->title;
            }  
        }
        return implode(", ", $data);
    }
}
