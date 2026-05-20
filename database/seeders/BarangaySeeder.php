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
        // IDs must match DemoDataSeeder references exactly
        $barangays = [
            ['barangay_id' => 1,  'barangay_name' => 'Alegria',           'cluster' => null],
            ['barangay_id' => 2,  'barangay_name' => 'Anahawan',          'cluster' => null],
            ['barangay_id' => 3,  'barangay_name' => 'Bgy. 1 Poblacion',  'cluster' => null],
            ['barangay_id' => 4,  'barangay_name' => 'Bgy. 2 Poblacion',  'cluster' => null],
            ['barangay_id' => 5,  'barangay_name' => 'Bgy. 3 Poblacion',  'cluster' => null],
            ['barangay_id' => 6,  'barangay_name' => 'Bgy. 4 Poblacion',  'cluster' => null],
            ['barangay_id' => 7,  'barangay_name' => 'Bgy. 5 Poblacion',  'cluster' => null],
            ['barangay_id' => 8,  'barangay_name' => 'Bgy. 6 Poblacion',  'cluster' => null],
            ['barangay_id' => 9,  'barangay_name' => 'Bgy. 7 Poblacion',  'cluster' => null],
            ['barangay_id' => 10, 'barangay_name' => 'Hinagdanan',         'cluster' => null],
            ['barangay_id' => 11, 'barangay_name' => 'Libertad',           'cluster' => null],
            ['barangay_id' => 12, 'barangay_name' => 'Lourdes',            'cluster' => null],
            ['barangay_id' => 13, 'barangay_name' => 'Mabini',             'cluster' => null],
            ['barangay_id' => 14, 'barangay_name' => 'Magsaysay',          'cluster' => null],
            ['barangay_id' => 15, 'barangay_name' => 'Mahayahay',          'cluster' => null],
            ['barangay_id' => 16, 'barangay_name' => 'Malixi',             'cluster' => null],
            ['barangay_id' => 17, 'barangay_name' => 'Managok',            'cluster' => null],
            ['barangay_id' => 18, 'barangay_name' => 'Manoligao',          'cluster' => null],
            ['barangay_id' => 19, 'barangay_name' => 'Marga',              'cluster' => null],
            ['barangay_id' => 20, 'barangay_name' => 'New Visayas',        'cluster' => null],
            ['barangay_id' => 21, 'barangay_name' => 'Pangi',              'cluster' => null],
            ['barangay_id' => 22, 'barangay_name' => 'Punta',              'cluster' => null],
            ['barangay_id' => 23, 'barangay_name' => 'San Isidro',         'cluster' => null],
            ['barangay_id' => 24, 'barangay_name' => 'Tagbayagan',         'cluster' => null],
        ];

        foreach ($barangays as $data) {
            DB::table('barangays')->insertOrIgnore(array_merge($data, [
                'municipality' => 'Madrid',
                'province'     => 'Surigao del Sur',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]));
        }

        // Reset PostgreSQL sequence so new inserts get IDs after 24
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('barangays', 'barangay_id'), 24)");
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
