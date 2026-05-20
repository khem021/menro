<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BarangaySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $barangays = [
            'Bagsac', 'Bayogo', 'Linibonan', 'Magsaysay', 'Manga',
            'Panayogon', 'Patong Patong', 'Quirino', 'San Antonio', 'San Juan',
            'San Roque', 'San Vicente', 'Songkit', 'Union',
        ];

        foreach ($barangays as $name) {
            DB::table('barangays')->insertOrIgnore([
                'barangay_name' => $name,
                'municipality'  => 'Madrid',
                'province'      => 'Surigao del Sur',
                'cluster'       => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // Create 6 sectors per barangay
        $brgyIds = DB::table('barangays')->pluck('barangay_id');
        foreach ($brgyIds as $brgyId) {
            for ($i = 1; $i <= 6; $i++) {
                DB::table('barangay_sectors')->insertOrIgnore([
                    'barangay_id'   => $brgyId,
                    'sector_number' => $i,
                    'sector_name'   => 'Sector ' . $i,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }
    }
}
