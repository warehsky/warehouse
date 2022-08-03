<?php

use Illuminate\Database\Seeder;
use App\Model\TradePointScheduleTypes;

class TradePointScheduleTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TradePointScheduleTypes::create([
                "title" => "Доставка",
        ]);
        TradePointScheduleTypes::create([
                "title" => "Работа",
        ]);
    }
}
