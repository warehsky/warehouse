<?php

use Illuminate\Database\Seeder;
use App\Model\WarehouseByDirection;

class WarehouseByDirectionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WarehouseByDirection::create([
                "warehouseId" => 1,
                "tradeDirectionId" => 1,
        ]);
        WarehouseByDirection::create([
                "warehouseId" => 2,
                "tradeDirectionId" => 2,
        ]);
    }
}
