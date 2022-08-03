<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Prices extends Model
{
    protected $table = 'prices';
    public $timestamps = false;
    protected $connection = 'mtagent';
    /*
        Возвращает изменившиеся цены с учетом доступных цен в профиле.
        args: timestamp, user
        return: [itemId:Int, pType:Int, value:Double, updateTm:Long(timestamp)}]}]
    */
    public static function getPrices($user, $itemId = 0){
        $sql = "select CAST(itemId as CHAR) as itemId, CAST(priceType as CHAR) as pType, CAST(value as CHAR) as value, CAST(UNIX_TIMESTAMP(updateTm) as CHAR) as updateTm " .
        "from prices ";
        $sqlWhere = " where areaId=" . $user->areaId . " and (priceType&" . $user->priceTypes . ")>0";
        if($itemId){
            $sqlWhere = " where areaId=" . $user->areaId . " and (priceType&" . $user->priceTypes . ")>0 and itemId=" . $itemId;
        }
        $result = \DB::connection('mtagent')->select( $sql.$sqlWhere );
        if($itemId)
            if($result)
                $result = $result[0]->value;
            else
                $result = 0;
        return $result;
    }
}
