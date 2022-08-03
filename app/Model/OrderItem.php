<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'orderItem';
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'orderId', 'itemId', 'warehouse_id', 'quantity', 'quantity_warehouse', 'price', 'priceType', 'percent', 'pickTm'
    ];
    /**
     * Сохранение позиций товаров в заказе
     * Входные параметры:
     * $items - массив товаров заказа
     * $order_id - ID заказа
     */
    public static function saveOrderItems($items, $order_id)
	{
		foreach($items as $item)
		{
			$model_oi = [
			'orderId' => $order_id,
			'itemId' => $item['itemId'],
			'warehouse_id' => $item['warehouse_id'],
			'quantity' => $item['quantity'],
			'price' => $item['price'],
            'priceType' => $item['priceType'],
            'percent' => $item['percent'],
            'workerId' => $item['workerId'],
            'manually' => $item['manually'],
            ];
            
			OrderItem::create( $model_oi );
		}
	}
	/** */
	public function orders()
    {
        return $this->belongsTo(Orders::class, 'order_id', 'id');
    }
    /** */
	public function items()
    {
        return $this->belongsTo(Items::class, 'item_id', 'id');
    }
    /** */
	public function warehouse()
    {
        return $this->belongsTo(Warehouses::class, 'warehouse_id', 'id');
    }
    /** */
	public function priceTypes()
    {
        return $this->belongsTo(PriceTypes::class, 'priceType', 'value');
    }
    /**
     * Возвращает список товаров в заказе 
    */
    public static function getOrderItems($orderId){
        if(!$orderId)
            return $items = [];
        $areaId = config('shop.areaId', 1); // регион для цены
        $priceType =config('shop.priceType', 16); // тип цены обычная
        $priceType2 =config('shop.priceType2', 32); // тип цены акция
        $priceType3 =config('shop.priceType3', 64); // тип цены скидка за кол-во
        $sql = "select i.itemId, i.quantity, i.quantity_base, i.quantity_warehouse, i.price  as courier, l.title, l.discountBound, p.value as price, l.prepayment, l.mult, l.parentId, l.weightId, i.workerId, i.manually, 0 as addStatus,
            (select case when EXISTS(select id from mtShop.warehouseGroups where groupId=l.parentId and warehouseId=0) then 100 else sum(quantity) end from mtagent.warehouseItems as w INNER JOIN mtShop.warehouseGroups as wg on w.`warehouseId`=wg.warehouseId where w.itemId = l.id and wg.groupId=l.parentId) as quantityAll,
            (select sum(value) from mtagent.prices as pt where pt.priceType={$priceType2} and pt.areaId = {$areaId} and pt.itemId=i.itemId) as discountPrice, 
            (select sum(value) from mtagent.prices as pt where pt.priceType={$priceType3} and pt.areaId = {$areaId} and pt.itemId=i.itemId) as stockPrice 
                from mtShop.orderItem as i INNER JOIN mtShop.itemsLink as l ON i.itemId=l.id 
                right join mtagent.prices as p on p.itemId=l.id and p.areaId = {$areaId} and p.priceType={$priceType} 
                where i.orderId={$orderId} order by i.id";
                
        $items = \DB::connection()->select( $sql );
        if(!$items)
            $items = [];
        else{
            $adds = DeliveryAdd::where('orderId', $orderId)->get(); // проверяем наличие довозов
            foreach($adds as $a)
                foreach($items as $i)
                    if($i->itemId==$a->itemId){
                        $i->addStatus = $a->status;
                        break;
                    }
        }
        return $items;
    }
    /**
     * Возвращает список товаров в заказе для печати на складе
    */
    public static function getOrderItemsWeight($orderId){
        $items = [];
        if(!$orderId)
            return $items;
        $items['weight'] = OrderItem::select('orderItem.*', 'itemsLink.title', 'itemGroups.title as group', 'itemsLink.weightId')
        ->join('itemsLink', 'orderItem.itemId', '=', 'itemsLink.id')
        ->join('itemGroups', 'itemGroups.id', '=', 'itemsLink.parentId')
        ->where('orderItem.orderId', $orderId)
        ->whereNotNull('itemsLink.weightId')
        ->orderBy('itemsLink.title')
        ->get();
        $items['notweight'] = OrderItem::select('orderItem.*', 'itemsLink.title', 'itemGroups.title as group', 'itemsLink.weightId')
        ->join('itemsLink', 'orderItem.itemId', '=', 'itemsLink.id')
        ->join('itemGroups', 'itemGroups.id', '=', 'itemsLink.parentId')
        ->where('orderItem.orderId', $orderId)
        ->whereNull('itemsLink.weightId')
        ->orderBy('itemsLink.title')
        ->get();
        return $items;
    }
    /**
     * Сохранение позиций товаров в заказе
     * Входные параметры:
     * $items - массив товаров заказа
     * $orderId - ID заказа
     */
    public static function save_OrderItems($items, $orderId, &$sum, &$ids)
	{
		$workerId = \Auth::guard('admin')->user()->id;
        foreach($items as $item){
            // @$itm = json_decode($item);
            // if(!$itm) continue;
            $price = ItemsLink::getItemPrice($item['itemId'], $item['quantity']);
            $sum += round($price['price'] * $item['quantity'], 2);
            
            $sql = "INSERT INTO mtShop.orderItem (`orderId`, `itemId`, `warehouse_id`, `price`, `priceType`, `quantity`, `quantity_base`) " .
            "VALUES ({$orderId}, {$item['itemId']}, 0, {$price['price']}, {$price['priceType']}, {$item['quantity']}, {$item['quantity']}) ".
            "ON DUPLICATE KEY UPDATE `price`=VALUES(`price`), `priceType`=VALUES(`priceType`), `quantity`=VALUES(`quantity`)";
            
            $result = \DB::connection()->select( $sql );
            $ids[] = $item['itemId'];
        }
	}
    /**
     * Сохранение позиций товаров в заказе весовой товар на складе
     * Входные параметры:
     * $items - массив товаров заказа
     * $orderId - ID заказа
     */
    public static function save_OrderWarehouseItems($items, $orderId, &$sum, &$ids)
	{
		$workerId = \Auth::guard('admin')->user()->id;
        foreach($items as $item){
            // @$itm = json_decode($item);
            if(!$item['scaned']) continue;
            $price = ItemsLink::getItemPrice($item['itemId'], $item['quantity']);
            $sum += $price['price'] * $item['quantity'];

            $sql = "UPDATE mtShop.orderItem SET `quantity_warehouse`={$item['quantity_warehouse']}, `workerId`={$workerId}, `manually`={$item['manually']} " .
            "where `orderId`={$orderId} and `itemId`={$item['itemId']}";
            
            $result = \DB::connection()->select( $sql );
            $ids[] = $item['itemId'];
        }
	}
    /**
     * Сохранение позиций пакетов в заказе
     * Входные параметры:
     * $items - массив товаров заказа
     * $orderId - ID заказа
     */
    public static function save_OrderPacks($items, $orderId, &$sum, &$ids)
	{
		$workerId = \Auth::guard('admin')->user()->id;
        foreach($items as $item){
            $itm = ItemsLink::find($item['itemId']);
            if(!$itm || $itm->parentId != 179) continue; // если не пакеты, пропускаем
            $price = ItemsLink::getItemPrice($item['itemId'], $item['quantity']);
            $sum += $price['price'] * $item['quantity'];
            if($item['scaned']){
                $sql = "INSERT INTO mtShop.orderItem (`orderId`, `itemId`, `warehouse_id`, `price`, `priceType`, `quantity`,`quantity_warehouse`, `workerId`, `manually`) " .
                "VALUES ({$orderId}, {$item['itemId']}, 0, {$price['price']}, {$price['priceType']}, {$item['quantity_warehouse']}, {$item['quantity_warehouse']}, {$workerId}, {$item['manually']}) ".
                "ON DUPLICATE KEY UPDATE `price`=VALUES(`price`), `priceType`=VALUES(`priceType`), `quantity`=VALUES(`quantity`),`quantity_warehouse`=VALUES(`quantity`), `workerId`=VALUES(`workerId`), `manually`=VALUES(`manually`)";
            }else{
                $sql = "INSERT INTO mtShop.orderItem (`orderId`, `itemId`, `warehouse_id`, `price`, `priceType`, `quantity`, `quantity_base`) " .
                "VALUES ({$orderId}, {$item['itemId']}, 0, {$price['price']}, {$price['priceType']}, {$item['quantity']}, {$item['quantity']}) ".
                "ON DUPLICATE KEY UPDATE `price`=VALUES(`price`), `priceType`=VALUES(`priceType`), `quantity`=VALUES(`quantity`)";
            }
            $result = \DB::connection()->select( $sql );
            $ids[] = $item['itemId'];
        }
	}
}
