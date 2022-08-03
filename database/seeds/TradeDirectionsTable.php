<?php

use Illuminate\Database\Seeder;
use App\Model\TradeDirections;

class TradeDirectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TradeDirections::create([
                "title" => "ubique",
                "parent" => 0,
                "guid" => "guid-ubique"
        ]);
        TradeDirections::create([
                "title" => "probe",
                "parent" => 1,
                "guid" => "guid-probe"
        ]);
    }
}
