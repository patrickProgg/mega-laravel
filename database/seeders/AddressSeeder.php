<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bohol Province with municipalities and barangays
        $provinces = [
            ['name' => 'Bohol', 'cities' => [
                ['name' => 'Tagbilaran City', 'zip_code' => '6310', 'barangays' => [
                    ['name' => 'Barangay Cogon', 'puroks' => ['Purok 1', 'Purok 2', 'Purok 3']],
                    ['name' => 'Barangay Dao', 'puroks' => ['Purok A', 'Purok B', 'Purok C']],
                    ['name' => 'Barangay Canlumia', 'puroks' => ['Purok I', 'Purok II']],
                ]],
                ['name' => 'Guindulman', 'zip_code' => '6311', 'barangays' => [
                    ['name' => 'Barangay Guindulman', 'puroks' => ['Purok Central', 'Purok East', 'Purok West']],
                    ['name' => 'Barangay Canlumia', 'puroks' => ['Purok 1', 'Purok 2', 'Purok 3']],
                    ['name' => 'Barangay Tabuan', 'puroks' => ['Purok A', 'Purok B']],
                ]],
                ['name' => 'Cebu City', 'zip_code' => '6000', 'barangays' => [
                    ['name' => 'Barangay Caballero', 'puroks' => ['Purok 1', 'Purok 2']],
                    ['name' => 'Barangay Maribojoc', 'puroks' => ['Purok A', 'Purok B']],
                ]],
                ['name' => 'Baclayon', 'zip_code' => '6312', 'barangays' => [
                    ['name' => 'Barangay Baclayon', 'puroks' => ['Purok Central', 'Purok East', 'Purok West']],
                ]],
                ['name' => 'Panglao', 'zip_code' => '6340', 'barangays' => [
                    ['name' => 'Barangay Tangnan', 'puroks' => ['Purok 1', 'Purok 2', 'Purok 3']],
                    ['name' => 'Barangay Lourdes', 'puroks' => ['Purok A', 'Purok B']],
                ]],
                ['name' => 'Anda', 'zip_code' => '6342', 'barangays' => [
                    ['name' => 'Barangay Anda', 'puroks' => ['Purok 1', 'Purok 2']],
                ]],
                ['name' => 'Corella', 'zip_code' => '6319', 'barangays' => [
                    ['name' => 'Barangay Corella', 'puroks' => ['Purok Central', 'Purok Norte', 'Purok Sur']],
                ]],
            ]],
        ];

        foreach ($provinces as $provinceData) {
            $province = DB::table('address_provinces')->insertGetId([
                'name' => $provinceData['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($provinceData['cities'] as $cityData) {
                $city = DB::table('address_cities')->insertGetId([
                    'province_id' => $province,
                    'name' => $cityData['name'],
                    'zip_code' => $cityData['zip_code'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($cityData['barangays'] as $barangayData) {
                    $barangay = DB::table('address_barangays')->insertGetId([
                        'city_id' => $city,
                        'name' => $barangayData['name'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    foreach ($barangayData['puroks'] as $purokName) {
                        DB::table('address_puroks')->insert([
                            'barangay_id' => $barangay,
                            'name' => $purokName,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}

