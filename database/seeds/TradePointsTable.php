<?php

use Illuminate\Database\Seeder;
use App\Model\TradePoints;

class TradePointsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TradePoints::create([
                "client_id" => 1,
                "areaId" => 1,
                "trade_point" => "Магаз 1",
                "address" => "Магаз 1 address",
                "phone" => "12345678",
                "trade_point_type_id" => 1,
                "is_new" => 0,
                "is_locked" => 0,
                "location_id" => 1,
                "trade_area_id" => 1,
                "guid" => "guid-shop1"
        ]);
        TradePoints::create([
            "client_id" => 1,
            "areaId" => 1,
            "trade_point" => "Магаз 2",
            "address" => "Магаз 2 address",
            "phone" => "12345678",
            "trade_point_type_id" => 2,
            "is_new" => 0,
            "is_locked" => 0,
            "location_id" => 1,
            "trade_area_id" => 1,
            "guid" => "guid-shop2"
        ]);
        TradePoints::create([
            "client_id" => 2,
            "areaId" => 1,
            "trade_point" => "Магаз 3",
            "address" => "Магаз 3 address",
            "phone" => "12345678",
            "trade_point_type_id" => 1,
            "is_new" => 0,
            "is_locked" => 0,
            "location_id" => 1,
            "trade_area_id" => 1,
            "guid" => "guid-shop3"
        ]);
        TradePoints::create([
            "client_id" => 2,
            "areaId" => 1,
            "trade_point" => "Магаз 4",
            "address" => "Магаз 4 address",
            "phone" => "12345678",
            "trade_point_type_id" => 2,
            "is_new" => 0,
            "is_locked" => 0,
            "location_id" => 1,
            "trade_area_id" => 1,
            "guid" => "guid-shop4"
        ]);
    }
}
