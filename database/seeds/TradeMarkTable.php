<?php

use Illuminate\Database\Seeder;
use App\Model\TradeMark;

class TradeMarkTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TradeMark::create([
                "supplier" => "Mark1 level0",
                "is_new" => 0,
                "guid" => "guid-Mark1",
                "guid_parent" => "",
                "is_group" => 1
        ]);
        TradeMark::create([
                "supplier" => "Mark2 level0",
                "is_new" => 1,
                "guid" => "guid-Mark2",
                "guid_parent" => "",
                "is_group" => 1
        ]);
        TradeMark::create([
            "supplier" => "Mark3 level1",
            "is_new" => 0,
            "guid" => "guid-Mark3",
            "guid_parent" => "guid-Mark1",
            "is_group" => 1
        ]);
        TradeMark::create([
            "supplier" => "Mark4 level1",
            "is_new" => 0,
            "guid" => "guid-Mark4",
            "guid_parent" => "guid-Mark1",
            "is_group" => 1
        ]);
        TradeMark::create([
            "supplier" => "Mark5 level1",
            "is_new" => 0,
            "guid" => "guid-Mark5",
            "guid_parent" => "guid-Mark3",
            "is_group" => 1
        ]);
        TradeMark::create([
            "supplier" => "Mark6 level2",
            "is_new" => 0,
            "guid" => "guid-Mark6",
            "guid_parent" => "guid-Mark3",
            "is_group" => 0
        ]);
        TradeMark::create([
            "supplier" => "Mark7 level2",
            "is_new" => 0,
            "guid" => "guid-Mark7",
            "guid_parent" => "guid-Mark3",
            "is_group" => 0
        ]);
    }
}
