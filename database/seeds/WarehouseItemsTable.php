<?php

use Illuminate\Database\Seeder;
use App\Model\WarehouseItems;

class WarehouseItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WarehouseItems::create([
                "warehouseId" => 1,
                "itemId" => 5,
                "quantity" => 100
        ]);
        WarehouseItems::create([
                "warehouseId" => 2,
                "itemId" => 6,
                "quantity" => 200
        ]);
        WarehouseItems::create([
            "warehouseId" => 1,
            "itemId" => 7,
            "quantity" => 100
        ]);
        WarehouseItems::create([
                "warehouseId" => 2,
                "itemId" => 8,
                "quantity" => 200
        ]);
        WarehouseItems::create([
            "warehouseId" => 1,
            "itemId" => 8,
            "quantity" => 100
        ]);
        WarehouseItems::create([
                "warehouseId" => 2,
                "itemId" => 7,
                "quantity" => 200
        ]);
    }
}
