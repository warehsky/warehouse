<?php

use Illuminate\Database\Seeder;
use App\Model\ContractTypes;

class ContractTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ContractTypes::create([
                "title" => "Ф1 безнал", "value" => 1
        ]);
        ContractTypes::create([
                "title" => "Ф2", "value" => 2
        ]);
        ContractTypes::create([
                "title" => "Ф1 нал", "value" => 4
        ]);
    }
}
