<?php

use Illuminate\Database\Seeder;
use App\Model\ClientDeferringTime;

class ClientDeferringTimeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ClientDeferringTime::create([
                "clientId" => 1,
                "directionId" => 1,
                "deferringTime" => 5,
                "roAttr" => 0,
                "defAttr" => 1
        ]);
        
    }
}
