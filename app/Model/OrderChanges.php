<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OrderChanges extends Model
{
    protected $table = 'orderChanges';
    public $timestamps = true;
    
    protected $fillable = [
        'orderId', 'itemId', 'quantity', 'initiatorId', 'moderatorId', 'initiatorPlace', 'changeId', 'closed', 'price', 'priceType', 'headId'
    ];
    /**
     * Возвращает корректирующие документы заказа
     * Входные параметры:
     * orderId - ID заказа
     * closed - документ закрыт 1, не закрыт 0
     * in - кто создал документ: склад или оператор (см. BaseController)
     */
    public static function getOrderChanges($orderId, $closed=0, $in=0){
        if(!$orderId) return [];
        $changes = OrderChanges::select('orderChanges.*', 'itemsLink.title')
        ->join('itemsLink', 'itemsLink.id', 'orderChanges.itemId')
        ->where('orderId', $orderId)
        ->where('closed', $closed);
        if($in > 0)
            $changes = $changes->where('initiatorPlace', $in);
        $changes = $changes->get();
        foreach($changes as $i){
            $item = Items::find($i->itemId);
            if($item){
                $i->barCode = $item->barCode ?? '';
                $i->supplierArticle = $item->supplierArticle ?? '';
                $i->article = $item->article ?? '';
            }
            else{
                $i->barCode = "";
                $i->supplierArticle = "";
            }
            $props = ItemPropertys::where('itemId', $i->itemd)
            ->where(function ($query){
                $query->where('title', 'Бренд')
                 ->orWhere('title', 'Производитель');
            })
            ->orderBy('title')
            ->get();
            $supplier = '';
            foreach($props as $p){
                if($p->title == 'Бренд')
                    $supplier = $p->value ?? '';
                if($p->title == 'Производитель'){
                    $supplier .= " / ".$p->value ?? '';
                    break;
                }
            }
            $i->supplier = $supplier;
            $i->image = "https://mt.delivery/img/img/items/small/{$i->itemId}.png";
            $warehouseItem = WarehouseItems::where('itemId', $i->itemId)
            ->where('warehouseId', 39)
            ->first();
            if($warehouseItem)
                $i->remind = $warehouseItem->quantity;
            else
                $i->remind = 0;
            $i->newQuantity = $i->quantity;
        }
        return $changes;
    }
}
