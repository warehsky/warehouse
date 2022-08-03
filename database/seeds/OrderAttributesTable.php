<?php

use Illuminate\Database\Seeder;
use App\Model\OrderAttributes;

class OrderAttributesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OrderAttributes::create([
            "title" => "Отсрочка",
            "tradeDirection" => 1,
            "value" => "value1",
            "guid" => "guid-otsrochka"
        ]);
        OrderAttributes::create([
            "title" => "Рассрочка",
            "tradeDirection" => 2,
            "value" => "value2",
            "guid" => "guid-rasrochka"
        ]);
    }
}
