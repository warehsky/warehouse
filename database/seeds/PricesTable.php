<?php

use Illuminate\Database\Seeder;
use App\Model\Prices;

class PricesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Цены опт
        Prices::create([
                "itemId" => 5,
                "areaId" => 1,
                "priceType" => 1,
                "value" => 50
        ]);
        Prices::create([
                "itemId" => 6,
                "areaId" => 1,
                "priceType" => 1,
                "value" => 150
        ]);
        Prices::create([
            "itemId" => 7,
            "areaId" => 1,
            "priceType" => 1,
            "value" => 51
        ]);
        Prices::create([
                "itemId" => 8,
                "areaId" => 1,
                "priceType" => 1,
                "value" => 151
        ]);
        // Цены розница
        Prices::create([
            "itemId" => 5,
            "areaId" => 1,
            "priceType" => 2,
            "value" => 50
        ]);
        Prices::create([
                "itemId" => 6,
                "areaId" => 1,
                "priceType" => 2,
                "value" => 150
        ]);
        Prices::create([
            "itemId" => 7,
            "areaId" => 1,
            "priceType" => 2,
            "value" => 51
        ]);
        Prices::create([
                "itemId" => 8,
                "areaId" => 1,
                "priceType" => 2,
                "value" => 151
        ]);
    }
}
