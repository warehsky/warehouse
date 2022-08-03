<?php

use Illuminate\Database\Seeder;
use App\Model\Orders;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Orders::create([
            "number" => "Ф001",
            "contractType" => "Ф1",
            "trade_point_id" => 1,
            "user_id" => 1,
            "date_time_created"  => strtotime(date('d-m-Y H:i:s')),
            "date_time_shipment" => strtotime(date('d-m-Y H:i:s')),
            "deliveryType" => 1,
            "coord_created_lat" => 1,
            "coord_created_lng" => 1,
            "sum_total" => 100,
            "note" => strtotime(date('d-m-Y H:i:s')),
            "guid" => "orders-order1",
            "is_new" => 0,
            "attributes" => "value1;value2",
        ]);
        Orders::create([
            "number" => "Ф002",
            "contractType" => "Ф2",
            "trade_point_id" => 3,
            "user_id" => 2,
            "date_time_created"  => strtotime(date('d-m-Y H:i:s')),
            "date_time_shipment" => strtotime(date('d-m-Y H:i:s')),
            "deliveryType" => 1,
            "coord_created_lat" => 1,
            "coord_created_lng" => 1,
            "sum_total" => 200,
            "note" => strtotime(date('d-m-Y H:i:s')),
            "guid" => "orders-order2",
            "is_new" => 0,
            "attributes" => "value2",
        ]);
    }
}
