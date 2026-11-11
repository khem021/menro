<?php

namespace Tests\Feature;

use Database\Seeders\BarangaySeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * start.sh runs the core seeders on every boot, so each one has to be a no-op
 * on an already-populated database.
 *
 * BarangaySeeder was not: it used insertOrIgnore against a column with no
 * unique index, so every deploy inserted another copy of all 14 barangays. The
 * deployed site reached 98 of them.
 */
class SeederIdempotencyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_running_the_barangay_seeder_again_adds_nothing(): void
    {
        $before = DB::table('barangays')->count();

        $this->seed(BarangaySeeder::class);
        $this->seed(BarangaySeeder::class);

        $this->assertSame(
            $before,
            DB::table('barangays')->count(),
            'BarangaySeeder must be a no-op on an already-seeded database.'
        );
    }

    public function test_no_barangay_name_appears_twice(): void
    {
        $duplicated = DB::table('barangays')
            ->select('barangay_name')
            ->groupBy('barangay_name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('barangay_name');

        $this->assertEmpty(
            $duplicated,
            'Duplicate barangays: ' . $duplicated->implode(', ')
        );
    }

    public function test_sectors_do_not_multiply_either(): void
    {
        $before = DB::table('barangay_sectors')->count();

        $this->seed(BarangaySeeder::class);

        $this->assertSame($before, DB::table('barangay_sectors')->count());
    }
}
