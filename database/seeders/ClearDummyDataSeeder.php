<?php

namespace Database\Seeders;

use App\Models\Barangay;
use App\Models\BarangaySector;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClearDummyDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // ── Clear all transactional / operational data ──────────────────────
        DB::table('audit_logs')->truncate();
        DB::table('notifications')->truncate();
        DB::table('reports')->truncate();
        DB::table('violations')->truncate();
        DB::table('inspections')->truncate();
        DB::table('incidents')->truncate();
        DB::table('collection_records')->truncate();
        DB::table('collection_schedules')->truncate();
        DB::table('waste_entries')->truncate();
        DB::table('waste_generators')->truncate();

        // ── Keep only 1 admin user (user_id = 1), remove test accounts ──────
        DB::table('users')->where('user_id', '>', 1)->delete();

        // ── Replace barangays with the real 14 + 6 sectors each ─────────────
        DB::table('barangay_sectors')->truncate();
        DB::table('barangays')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Re-seed real barangays
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
            $brgy = Barangay::create([
                'barangay_name' => $name,
                'municipality'  => 'Madrid',
                'province'      => 'Surigao del Sur',
                'cluster'       => null,
            ]);

            for ($i = 1; $i <= 6; $i++) {
                BarangaySector::create([
                    'barangay_id'   => $brgy->barangay_id,
                    'sector_number' => $i,
                    'sector_name'   => 'Sector ' . $i,
                ]);
            }
        }

        $this->command->info('✓ All dummy data cleared.');
        $this->command->info('✓ 14 barangays + 6 sectors each re-seeded.');
        $this->command->info('✓ Admin user (ID 1) preserved.');
    }
}
