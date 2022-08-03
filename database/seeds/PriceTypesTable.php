<?php

use Illuminate\Database\Seeder;
use App\Model\PriceTypes;

class PriceTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PriceTypes::create([
                "title" => "Оптовые", "value" => 1
        ]);
        PriceTypes::create([
                "title" => "Розничные", "value" => 2
        ]);
        PriceTypes::create([
                "title" => "Дилер", "value" => 4
        ]);
        PriceTypes::create([
                "title" => "Сетка", "value" => 8
        ]);
    }
}
