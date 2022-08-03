<?php

use Illuminate\Database\Seeder;
use App\Model\TradePointDebts;

class TradePointDebtsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TradePointDebts::create([
                "tpId" => 1,
                "userId" => 1,
                "value" => "1000",
                "expiredValue" => "500"
        ]);
    }
}
