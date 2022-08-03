<?php

use Illuminate\Database\Seeder;
use App\Model\Items;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Items::create([
            "trade_mark_id" => 6,
            "unit_id" => 1,
            "item" => "Group Tovar1",
            "guid" => "guid-Group-Tovar1",
            "guid_parent" => "",
            "is_group" => 1,

        ]);
        Items::create([
            "trade_mark_id" => 6,
            "unit_id" => 1,
            "item" => "GrTovar1",
            "guid" => "guid-Tovar1",
            "guid_parent" => "guid-Group-Tovar1",
            "is_group" => 1,

        ]);
        Items::create([
            "trade_mark_id" => 6,
            "unit_id" => 1,
            "item" => "Group Tovar2",
            "guid" => "guid-Group-Tovar2",
            "guid_parent" => "",
            "is_group" => 1,

        ]);
        Items::create([
            "trade_mark_id" => 6,
            "unit_id" => 1,
            "item" => "GrTovar2",
            "guid" => "guid-Tovar2",
            "guid_parent" => "guid-Group-Tovar2",
            "is_group" => 1,

        ]);
        Items::create([
            "trade_mark_id" => 6,
            "unit_id" => 1,
            "item" => "Tovar-1",
            "guid" => "guid-Tovar-1",
            "guid_parent" => "guid-Group-Tovar1",
            "is_group" => 0,

        ]);
        Items::create([
            "trade_mark_id" => 7,
            "unit_id" => 1,
            "item" => "Tovar-2",
            "guid" => "guid-Tovar-2",
            "guid_parent" => "guid-Tovar2",
            "is_group" => 0,

        ]);
        Items::create([
            "trade_mark_id" => 6,
            "unit_id" => 1,
            "item" => "Tovar-1-1",
            "guid" => "guid-Tovar-1-1",
            "guid_parent" => "guid-Group-Tovar1",
            "is_group" => 0,

        ]);
        Items::create([
            "trade_mark_id" => 7,
            "unit_id" => 1,
            "item" => "Tovar-2-2",
            "guid" => "guid-Tovar-2-2",
            "guid_parent" => "guid-Tovar2",
            "is_group" => 0,

        ]);
    }
}
