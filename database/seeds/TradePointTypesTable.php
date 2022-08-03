<?php

use Illuminate\Database\Seeder;
use App\Model\TradePointTypes;

class TradePointTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TradePointTypes::create([
                "type" => "Магазин",
                "is_new" => 0,
                "guid" => "guid-shop"
        ]);
        TradePointTypes::create([
                "type" => "Автосалон",
                "is_new" => 1,
                "guid" => "guid-auto"
        ]);
    }
}
