<?php

use Illuminate\Database\Seeder;
use App\Model\ActiveItemGroups;

class ActiveItemGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ActiveItemGroups::create([
            "tradeDirectionId" => 1,
            "groupId" => 1
        ]);
        ActiveItemGroups::create([
            "tradeDirectionId" => 2,
            "groupId" => 3
        ]);
    }
}
