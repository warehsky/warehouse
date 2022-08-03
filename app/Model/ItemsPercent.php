<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ItemsPercent extends Model
{
    protected $table = 'itemsPercent';
    public $timestamps = false;

    /**
     * Возвращает процент иерархически
     * Входные параметры:
     * id - ID - товара
     */
    public static function getPercent($id){
        $percent = 0;
        if(!$id) // не верный ID
            return $percent;
        $pers = ItemsPercent::where('itemId', $id)->first();
        
        if($pers && $pers->percent != 0) // % установлен для данного товара
            return $pers->percent;
        $item = Items::find($id);
        //dd($item);
        if(!$item) // нет такого ID товара
            return $percent;
        $parent_guid = $item->guid_parent;
        
        while($item = Items::where('guid', $parent_guid)->first()){
            $parent_guid = $item->guid_parent;
            $pers = ItemsPercent::where('itemId',$item->id)->first();
            if($pers && $pers->percent != 0) // % установлен для данного товара
                return $pers->percent;
        }
        return $percent;
    }
}
