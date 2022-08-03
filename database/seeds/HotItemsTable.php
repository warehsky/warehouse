<?php

use Illuminate\Database\Seeder;
use App\Model\HotItems;

class HotItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        HotItems::create([
            "user_id" => 1,
            "trade_point_id" => 1,
            "items" => "1,2"
        ]);
        HotItems::create([
            "user_id" => 1,
            "trade_point_id" => 2,
            "items" => "3,4"
        ]);
        HotItems::create([
            "user_id" => 2,
            "trade_point_id" => 1,
            "items" => "1,2"
        ]);
        HotItems::create([
            "user_id" => 2,
            "trade_point_id" => 2,
            "items" => "3,4"
        ]);
    }
}
