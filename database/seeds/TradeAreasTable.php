<?php

use Illuminate\Database\Seeder;
use App\Model\TradeAreas;

class TradeAreasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TradeAreas::create([
                "name" => "TradeArea1",
                "is_new" => 0,
                "guid" => "guid-TradeArea1"
        ]);
        TradeAreas::create([
                "name" => "TradeArea2",
                "is_new" => 1,
                "guid" => "guid-TradeArea2"
        ]);
    }
}
