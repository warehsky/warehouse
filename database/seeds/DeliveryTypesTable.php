<?php

use Illuminate\Database\Seeder;
use App\Model\DeliveryTypes;

class DeliveryTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DeliveryTypes::create([
                "title" => "Самовывоз", "guid" => "guid-sam"
        ]);
        DeliveryTypes::create([
                "title" => "Доставка", "guid" => "guid-delivery"
        ]);
    }
}
