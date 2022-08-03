<?php

use Illuminate\Database\Seeder;
use App\Model\Warehouses;

class WarehousesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Warehouses::create([
                "warehouse" => "warehouse1",
                "is_new" => 0,
                "guid" => "guid-warehouse1"
        ]);
        Warehouses::create([
                "warehouse" => "warehouse2",
                "is_new" => 1,
                "guid" => "guid-warehouse2"
        ]);
    }
}
