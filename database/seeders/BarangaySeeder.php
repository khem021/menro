<?php

namespace Database\Seeders;

use App\Models\Barangay;
use App\Models\BarangaySector;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BarangaySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $barangays = [
            'Bagsac',
            'Bayogo',
            'Linibonan',
            'Magsaysay',
            'Manga',
            'Panayogon',
            'Patong Patong',
            'Quirino',
            'San Antonio',
            'San Juan',
            'San Roque',
            'San Vicente',
            'Songkit',
            'Union',
        ];

        foreach ($barangays as $name) {
            $brgy = Barangay::firstOrCreate(
                ['barangay_name' => $name],
                [
                    'municipality' => 'Madrid',
                    'province'     => 'Surigao del Sur',
                    'cluster'      => null,
                ]
            );

            // Create 6 sectors per barangay if they don't exist yet
            for ($i = 1; $i <= 6; $i++) {
                BarangaySector::firstOrCreate(
                    ['barangay_id' => $brgy->barangay_id, 'sector_number' => $i],
                    ['sector_name' => 'Sector ' . $i]
                );
            }
        }
    }
}
