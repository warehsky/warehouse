<?php

use Illuminate\Database\Seeder;
use App\Model\RequestMoney;

class RequestMoneyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RequestMoney::create([
            "number" => "M001",
            "status" => 2,
            "trade_point_id" => 1,
            "user_id" => 1,
            "date_time_created"  => strtotime(date('d-m-Y H:i:s')),
            "coord_created_lat" => 1,
            "coord_created_lng" => 1,
            "sum" => 100,
            "note" => strtotime(date('d-m-Y H:i:s')),
            "guid" => "money-order1",
            "is_new" => 0,
            "orderGuid" => "orders-order1",
        ]);
        RequestMoney::create([
            "number" => "Ф002",
            "status" => 0,
            "trade_point_id" => 3,
            "user_id" => 2,
            "date_time_created"  => strtotime(date('d-m-Y H:i:s')),
            "coord_created_lat" => 1,
            "coord_created_lng" => 1,
            "sum" => 200,
            "note" => strtotime(date('d-m-Y H:i:s')),
            "guid" => "money-order2",
            "is_new" => 0,
            "orderGuid" => "orders-order2",
        ]);
    }
}
