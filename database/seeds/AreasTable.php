<?php

use Illuminate\Database\Seeder;
use App\Model\Areas;

class AreasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Areas::create([
                "title" => "ДНР"
        ]);
        Areas::create([
                "title" => "ЛНР"
        ]);
    }
}
