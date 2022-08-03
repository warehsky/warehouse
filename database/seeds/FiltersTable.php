<?php

use Illuminate\Database\Seeder;
use App\Model\Filters;

class FiltersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Filters::create([
                "filter" => "Фильтр 1",
                "guid" => "guid-f1",
                "is_new" => 1
        ]);
        Filters::create([
                "filter" => "Фильтр 2",
                "guid" => "guid-f2",
                "is_new" => 0
        ]);
    }
}
