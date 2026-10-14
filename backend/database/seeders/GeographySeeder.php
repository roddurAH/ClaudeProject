<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Division;
use Illuminate\Database\Seeder;

class GeographySeeder extends Seeder
{
    public function run(): void
    {
        foreach (require __DIR__.'/data/geography.php' as [$divisionEn, $divisionBn, $districts]) {
            $division = Division::updateOrCreate(['name_en' => $divisionEn], ['name_bn' => $divisionBn]);

            foreach ($districts as [$en, $bn, $lat, $lon]) {
                District::updateOrCreate(
                    ['name_en' => $en],
                    ['division_id' => $division->id, 'name_bn' => $bn, 'latitude' => $lat, 'longitude' => $lon],
                );
            }
        }
    }
}
