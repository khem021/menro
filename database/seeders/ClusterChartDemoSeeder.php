<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Fills the gap between the last demo waste entry and today so the
 * "Waste Collection by Cluster" chart has data for every period
 * (daily / weekly / monthly / annual). Safe to re-run; remove with:
 *   DB::table('waste_entries')->where('remarks', self::TAG)->delete();
 */
class ClusterChartDemoSeeder extends Seeder
{
    private const TAG = 'Demo: cluster chart';

    public function run(): void
    {
        mt_srand(2026);

        DB::table('waste_entries')->where('remarks', self::TAG)->delete();

        $encoderId = DB::table('users')->orderBy('user_id')->value('user_id');

        $generators = DB::table('waste_generators as wg')
            ->join('barangays as b', 'wg.barangay_id', '=', 'b.barangay_id')
            ->whereNotNull('b.cluster')
            ->where('wg.status', 'active')
            ->get(['wg.generator_id', 'wg.estimated_daily_waste_kg', 'b.cluster']);

        $categoryIds = DB::table('waste_categories')->pluck('category_id')->all();

        // Cluster-specific volume so the bars differ visibly.
        $clusterFactor = [1 => 1.0, 2 => 0.6, 3 => 0.85];

        $today = Carbon::today();
        $start = Carbon::create($today->year, 5, 1);
        $rows  = [];

        for ($day = $start->copy(); $day->lte($today); $day->addDay()) {
            // Always log the current week so Daily/Weekly aren't empty.
            $guaranteed = $day->gte($today->copy()->startOfWeek());

            foreach ($generators as $g) {
                if (! $guaranteed && mt_rand(1, 100) > 30) {
                    continue;
                }

                $base = max(10, (float) $g->estimated_daily_waste_kg);
                $qty  = $base * ($clusterFactor[$g->cluster] ?? 0.8) * (mt_rand(60, 130) / 100);

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

        $this->command?->info(count($rows) . ' demo waste entries inserted.');
    }
}
