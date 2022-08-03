<?php

use Illuminate\Database\Seeder;
use App\Model\Admin;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Admin::create([
                "login" => "admin", 
                "password" => "$2y$10$/4HtqV0vImzUSGBmS56Js.kMPEFD.eTXqcrjlJs3tr/JTW12BSwsO",
                "name" => "admin",
                "email" => "admin@mail.com",
                "role" => 10
        ]);
    }
}
