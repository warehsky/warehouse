<?php

use Illuminate\Database\Seeder;
use App\Model\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
                "login" => "777", 
                "password" => 666,
                "areaId" => 1,
                "priceTypes" => 7,
                "tradeDirection" => 1,
                "session" => "",
                "client" => "0.0",
                "login_time" => 0,
                "session_end_time" => 0,
                "fio" => "Samopal",
                "is_locked" => 0,
                "guid" => "1",
                "week" => 1111111,
                "daySchedule" => 8001900,
                "is_new" => 0,
                "gen" => 0,
                "updateTm" => 0,
        ]);
        User::create([
            "login" => "user", 
            "password" => 666,
            "areaId" => 2,
            "priceTypes" => 7,
            "tradeDirection" => 2,
            "session" => "",
            "client" => "0.0",
            "login_time" => 0,
            "session_end_time" => 0,
            "fio" => "Samopal&K",
            "is_locked" => 0,
            "guid" => "1",
            "week" => 1111111,
            "daySchedule" => 8001900,
            "is_new" => 0,
            "gen" => 0,
            "updateTm" => 0,
    ]); 
    }
}
