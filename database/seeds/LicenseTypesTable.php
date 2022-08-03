<?php

use Illuminate\Database\Seeder;
use App\Model\LicenseTypes;

class LicenseTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        LicenseTypes::create([
                "title" => "Алкоголь", "value" => 1
        ]);
        LicenseTypes::create([
                "title" => "Табак", "value" => 2
        ]);
    }
}
