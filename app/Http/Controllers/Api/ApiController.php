<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Admins;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
             $this->middleware('adminauth.access-token');
            //  $user = Admin::where('id', 1)->first();
            //  \Auth::guard('admin')->login($user);
    }
    /**
     * Возвращает кол-во не обработанных заказов и кол-во не прочитанных сообщений чата
     * Входные параметры:
     * isusers - признак возвращать или нет пользователей чата, по умолчанию не возвращать
    */
    public function getAlerts(Request $request){
        $data=[];
        return json_encode( $data, JSON_UNESCAPED_UNICODE );
    }
    
    public function orderClone($order){
        if(!$order || ($order->status !=2 && $order->status !=6))
            return;
        $id = $order->id;
        \DB::beginTransaction();
        try{
            
            $sql = "INSERT INTO `ordersShadow`(`id`, `number`, `name`, `phone`, `phoneConsignee`, `addr`, `deliveryCost`, `status`, `deliveryZone`, `deliveryZoneIn`, `userId`, 
                   `note`, `sum_total`, `sum_last`, `sms`, `lat`, `lng`, `guid`, `deliveryFrom`, `deliveryTo`, `deviceType`, `deviceInfo`, `pension`, `payment`, `order_number`, 
                   `bonus`, `bonus_pay`, `action`, `orderNumber`, `sum_pay`, `visiteTime`, `remindSms`, `created_at`, `updated_at`) SELECT `id`, `number`, `name`, `phone`, `phoneConsignee`, 
                   `addr`, `deliveryCost`, `status`, `deliveryZone`, `deliveryZoneIn`, `userId`, `note`, `sum_total`, `sum_last`, `sms`, `lat`, `lng`, `guid`, `deliveryFrom`, 
                   `deliveryTo`, `deviceType`, `deviceInfo`, `pension`, `payment`, `order_number`, `bonus`, `bonus_pay`, `action`, `orderNumber`, `sum_pay`, `visiteTime`, `remindSms`, `created_at`, 
                   `updated_at` FROM `orders` WHERE orders.id={$id} ON DUPLICATE KEY UPDATE `number`=VALUES(number), `name`=VALUES(name), `phone`=VALUES(phone), `phoneConsignee`=VALUES(phoneConsignee), 
                   `addr`=VALUES(addr), `deliveryCost`=VALUES(deliveryCost), `status`=VALUES(status), `deliveryZone`=VALUES(deliveryZone), `deliveryZoneIn`=VALUES(deliveryZoneIn), 
                   `userId`=VALUES(userId), `note`=VALUES(note), `sum_total`=VALUES(sum_total), `sum_last`=VALUES(sum_last), `sms`=VALUES(sms), `lat`=VALUES(lat), `lng`=VALUES(lng), `guid`=VALUES(guid), 
                   `deliveryFrom`=VALUES(deliveryFrom), `deliveryTo`=VALUES(deliveryTo), `deviceType`=VALUES(deviceType), `deviceInfo`=VALUES(deviceInfo), 
                   `pension`=VALUES(pension), `payment`=VALUES(payment), `order_number`=VALUES(order_number), `bonus`=VALUES(bonus), `bonus_pay`=VALUES(bonus_pay), `action`=VALUES(action), 
                   `orderNumber`=VALUES(orderNumber), `sum_pay`=VALUES(sum_pay), `visiteTime`=VALUES(visiteTime), `remindSms`=VALUES(remindSms), `created_at`=VALUES(created_at), 
                   `updated_at`=VALUES(updated_at)";
            
            \DB::connection()->select( $sql );
            $sql = "INSERT INTO `orderItemShadow`(`id`, `orderId`, `itemId`, `warehouse_id`, `quantity`, `quantity_base`, `quantity_warehouse`, `price`, `priceType`, `percent`, `workerId`, `manually`) 
                   SELECT `id`, `orderId`, `itemId`, `warehouse_id`, `quantity`, `quantity_base`, `quantity_warehouse`, `price`, `priceType`, `percent`, `workerId`, `manually` FROM `orderItem` WHERE orderItem.orderId={$id} 
                   ON DUPLICATE KEY UPDATE `warehouse_id`=VALUES(warehouse_id), `quantity`=VALUES(quantity), `quantity_base`=VALUES(quantity_base), `quantity_warehouse`=VALUES(quantity_warehouse), `price`=VALUES(price), `priceType`=VALUES(priceType), `percent`=VALUES(percent), `workerId`=VALUES(workerId), `manually`=VALUES(manually)";
            \DB::connection()->select( $sql );

        }catch(\Exception $e){
            \DB::rollback();
            throw $e;
        }
        \DB::commit();
    }
    
}
