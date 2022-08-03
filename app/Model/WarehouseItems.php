<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WarehouseItems extends Model
{
    protected $table = 'warehouseItems';
    public $timestamps = false;
    protected $connection = 'mtagent';

    /* Получение Даты и время последнего обновления складов*/
    public static function getWarehouseRefreshTime(){
        $result = WarehouseItems::select(\DB::raw('max(UNIX_TIMESTAMP(updateTm)) as updateTm'))->first();
        return $result;
    }

}
