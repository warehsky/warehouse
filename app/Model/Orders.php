<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Orders extends Model
{
    protected $table = 'orders';
    public $timestamps = false;
    

    protected $fillable = [
        'sum_total', 
        'lat',
        'lng',
        'name',
        'phone',
        'phoneConsignee',
        'note',
        'addr',
        'deliveryCost',
        'deliveryZone',
        'deliveryZoneIn',
        'deliveryFrom',
        'deliveryTo',
        'number',
        'userId',
        'guid',
        'status',
        'pickupStatus',
        'deviceType',
        'deviceInfo',
        'payment',
        'pension',
        'remindSms',
        'updated_at',
        'bonus',
        'bonus_pay',
        'waveId',
        'deliveryDate',
        'url_pay',
        'sum_pay',
        'orderNumber',
        'gift',
        'webUserId',
        'discountId',
        'discount_proc',
        'discount_sum',
        'entrance',
        'floor',
        'flat',
        'course',
        'nopacks'
        
    ];
    
    
    /** */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'orderId');
    }
    public function getOrderItems()
    {
        $items = OrderItem::select('orderItem.*', 'itemsLink.title', 'itemGroups.title as group', 'itemsLink.pickup')
        ->join('itemsLink', 'orderItem.itemId', '=', 'itemsLink.id')
        ->join('itemGroups', 'itemGroups.id', '=', 'itemsLink.parentId')
        ->where('orderItem.orderId', $this->id)
        ->orderBy('itemsLink.title')
        ->get();
        foreach($items as $ind=>$item){
            if(\Storage::disk('public')->exists('img/img/items/small/'.$item->itemId.'.png'))
                $item->image = "/img/img/items/small/" . $item->itemId . ".png";
            else{
                
                if(\Storage::disk('public')->exists('img/img/catalog/shadow/'.$item->parentId.'.png'))
                    $item->image = 'img/img/catalog/shadow/'.$item->parentId.'.png';
                else{
                    $parentId = $item->parentId;
                    $item->image = "/img/item_default.png";
                    for(;$parentId>0;){
                        $group = ItemGroups::find($parentId);
                        if($group){
                            $parentId = $group->parentId;
                            
                            if(\Storage::disk('public')->exists('img/img/catalog/shadow/'.$parentId.'.png')){
                                $item->image = 'img/img/catalog/shadow/'.$parentId.'.png';
                                break;
                            }
                        }else
                            $parentId = 0;// выход из цыкла
                    }
                }
            }
        }
        return $items;
    }
    /**
     * Расчет суммы к оплате 
    */
    public function getSumPay(){
        return round($this->sum_total+$this->deliveryCost-$this->bonus_pay-($this->sum_total*$this->discount_proc/100)-$this->discount_sum);
    }
    /**
     * Расчет суммы в гривнах 
    */
    public static function getSumTotalGrn($orderId){
        $order = Orders::find($orderId);
        $sum = 0;
        if($order && $order->course > 0){ // если заказ в гривне
            $items = OrderItem::where('orderId', $orderId)->get();
            foreach($items as $i){
                $sum += round($i->quantity*(ceil($i->price/$order->course)), 2);
            }
        }
        return $sum;//round($sum+ceil($order->deliveryCost/$order->course)-ceil($order->bonus_pay/$order->course)-($sum*$order->discount_proc/100)-$order->discount_sum/$order->course);
    }
}
