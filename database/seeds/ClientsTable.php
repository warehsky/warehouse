<?php

use Illuminate\Database\Seeder;
use App\Model\Clients;

class ClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Clients::create([
                "client" => "Квакин",
                "contractType" => 7,
                "guid" => "guid-kva",
                "is_locked" => 0
        ]);
        Clients::create([
                "client" => "Хрякин & Ko",
                "contractType" => 7,
                "guid" => "guid-hryu",
                "is_locked" => 0
        ]);
    }
}
