<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Keeps the demo looking alive.
 *
 * FreshDemoSeeder lays down a year of history once and then skips itself
 * forever, so as real time moves on the newest entry drifts further into the
 * past. Once it falls out of the current month, the dashboard's "Waste This
 * Month" and the cluster chart's Daily/Weekly/Monthly views all read zero and
 * the bar graph renders empty.
 *
 * This tops the data up to today. It runs on every boot and owns only the rows
 * it tags, so re-running replaces its own work rather than piling on.
 */
class DemoRecentActivitySeeder extends Seeder
{
    /** Marks the rows this seeder owns. */
    private const TAG = 'Routine collection (demo)';

    /** Never backfill more than this, so a long-idle database stays cheap. */
    private const MAX_DAYS = 45;

    public function run(): void
    {
        $generators = DB::table('waste_generators as wg')
            ->join('barangays as b', 'wg.barangay_id', '=', 'b.barangay_id')
            ->whereNotNull('b.cluster')
            ->where('wg.status', 'active')
            ->get(['wg.generator_id', 'wg.estimated_daily_waste_kg', 'b.cluster']);

        if ($generators->isEmpty()) {
            $this->command?->info('  — no clustered generators yet, skipping recent demo activity');
            return;
        }

        DB::table('waste_entries')->where('remarks', self::TAG)->delete();

        $today = Carbon::today();

        // Resume from the day after the newest entry that isn't ours.
        $latest = DB::table('waste_entries')->where(function ($q) {
            $q->whereNull('remarks')->orWhere('remarks', '!=', self::TAG);
        })->max('entry_date');

        $start = $latest ? Carbon::parse($latest)->addDay() : $today->copy()->subDays(13);

        if ($start->lessThan($today->copy()->subDays(self::MAX_DAYS))) {
            $start = $today->copy()->subDays(self::MAX_DAYS);
        }

        if ($start->greaterThan($today)) {
            $this->command?->info('  ✓ demo data already reaches today');
            return;
        }

        $categoryIds = DB::table('waste_categories')->pluck('category_id')->all();
        $encoderId   = DB::table('users')->orderBy('user_id')->value('user_id');

        // Keeps the clusters visibly different in height instead of three equal bars.
        $weight = [1 => 1.0, 2 => 0.62, 3 => 0.84];

        $rows = [];
        for ($day = $start->copy(); $day->lte($today); $day->addDay()) {
            foreach ($generators as $g) {
                // Everything lands in the current week so the Daily and Weekly
                // views are never empty; older days are sampled more sparsely.
                $recent = $day->greaterThanOrEqualTo($today->copy()->subDays(7));
                if (! $recent && mt_rand(1, 100) > 35) {
                    continue;
                }

                $base = max(10.0, (float) $g->estimated_daily_waste_kg);
                $qty  = $base * ($weight[$g->cluster] ?? 0.8) * (mt_rand(55, 135) / 100);

                $rows[] = [
                    'generator_id' => $g->generator_id,
                    'category_id'  => $categoryIds[array_rand($categoryIds)],
                    'quantity'     => round($qty, 2),
                    'unit'         => 'kg',
                    'entry_date'   => $day->toDateString(),
                    'remarks'      => self::TAG,
                    'encoded_by'   => $encoderId,
                    'created_at'   => $day->copy()->setTime(9, 0),
                    'updated_at'   => $day->copy()->setTime(9, 0),
                ];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('waste_entries')->insert($chunk);
        }

        $this->command?->info(
            '  ✓ ' . count($rows) . ' recent waste entries topped up ('
            . $start->toDateString() . ' → ' . $today->toDateString() . ')'
        );
    }
}
