<?php

use Illuminate\Database\Seeder;
use App\Model\RouteEvent;

class RouteEventsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RouteEvent::create([
                "coord_created_lat" => "warehouse1",
                "coord_created_lng" => 0,
                "user_id" => 1,
                "tradePointId" => 1,
                "title" => 1,
                "guid" => "guid-routeevents1",
                "is_new" => 1,
        ]);
    }
}
