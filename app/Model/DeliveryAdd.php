<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DeliveryAdd extends Model
{
    protected $table = 'deliveryAdd';
    public $timestamps = true;
    protected $fillable = [
        'orderId', // документ в котором был не довоз
        'itemId',   // ID товара
        'confirmId', // кто на складе подтвердил
        'confirmQuantity', // подтвержденное количество
        'pickupQuantity', // взятое кол-во (склад)
        'quantity', // количество
        'status', // статус документа
        'createId', // кто создал документ
        'closeId', // кто закрыл документ
        'addedId', // ID заказа с которым уехал довоз (0 тогда ухал без заказа)
        'waveId', // ID волны
        'deliveryDate', // дата довоза
        'pickTm' // время сборки товара
    ];
    /**
     * Возвращает не подтвержденные довозы
    */
    public static function getNoConfirmDeliveryAdds(){
        $deliveryAdds = DeliveryAdd::select('orderId')
        ->where('status', 1)->groupBy('orderId')->get();
        foreach($deliveryAdds as $d){
            $items = DeliveryAdd::select('deliveryAdd.id', 'itemsLink.id as itemId', 'itemsLink.longTitle as title', 'deliveryAdd.confirmId', 
            'deliveryAdd.confirmQuantity', 'deliveryAdd.quantity', 'deliveryAdd.status', 'deliveryAdd.createId', 'deliveryAdd.closeId', 'deliveryAdd.addedId')
            ->join('itemsLink', "itemsLink.id", "deliveryAdd.itemId")
            // ->where('deliveryAdd.confirmId', 0)
            ->where('deliveryAdd.status', 1)
            ->where('orderId', $d->orderId)
            ->get();
            foreach($items as $i){
                $item = Items::find($i->itemId);
                if($item){
                    $i->barCode = $item->barCode;
                    $i->supplierArticle = $item->supplierArticle ?? "";
                    $i->article = $item->article ?? "";
                }
                else{
                    $i->barCode = "";
                    $i->supplierArticle = "";
                }
                $props = ItemPropertys::where('itemId', $i->itemId)
                ->where(function ($query){
                    $query->where('title', 'Бренд')
                     ->orWhere('title', 'Производитель');
                })
                ->orderBy('title')
                ->get();
                $supplier = '';
                foreach($props as $p){
                    if($p->title == 'Бренд')
                        $supplier = $p->value;
                    if($p->title == 'Производитель'){
                        $supplier .= " / ".$p->value;
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
                
            }
            $d->items = $items;
        }
        return $deliveryAdds;
    }
    
}
