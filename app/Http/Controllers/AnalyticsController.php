<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $since = now()->subMonths(12)->toDateString();

        $wasteByCategory = Cache::remember('analytics:waste_by_category', 300, fn() => DB::select("
            SELECT
                wc.category_name,
                SUM(we.quantity) AS total
            FROM waste_entries we
            JOIN waste_categories wc ON we.category_id = wc.category_id
            WHERE we.entry_date >= ?
            GROUP BY wc.category_name
            ORDER BY total DESC
        ", [$since]));

        $wasteByGeneratorType = Cache::remember('analytics:waste_by_generator_type', 300, fn() => DB::select("
            SELECT
                gt.type_name,
                SUM(we.quantity) AS total
            FROM waste_entries we
            JOIN waste_generators wg ON we.generator_id = wg.generator_id
            JOIN generator_types gt ON wg.generator_type_id = gt.generator_type_id
            WHERE we.entry_date >= ?
            GROUP BY gt.type_name
            ORDER BY total DESC
        ", [$since]));

        $wasteByCluster = Cache::remember('analytics:waste_by_cluster', 300, fn() => DB::select("
            SELECT
                b.cluster,
                SUM(we.quantity) AS total
            FROM waste_entries we
            JOIN waste_generators wg ON we.generator_id = wg.generator_id
            JOIN barangays b ON wg.barangay_id = b.barangay_id
            WHERE we.entry_date >= ?
              AND b.cluster IS NOT NULL
            GROUP BY b.cluster
            ORDER BY total DESC
        ", [$since]));

        $topGenerators = Cache::remember('analytics:top_generators', 300, fn() => DB::select("
            SELECT
                wg.generator_name,
                wg.compliance_status,
                b.barangay_name,
                SUM(we.quantity) AS total
            FROM waste_entries we
            JOIN waste_generators wg ON we.generator_id = wg.generator_id
            LEFT JOIN barangays b ON wg.barangay_id = b.barangay_id
            WHERE we.entry_date >= ?
            GROUP BY wg.generator_id, wg.generator_name, wg.compliance_status, b.barangay_name
            ORDER BY total DESC
            LIMIT 10
        ", [$since]));

        return view('analytics.index', compact(
            'wasteByCategory',
            'wasteByGeneratorType',
            'wasteByCluster',
            'topGenerators'
        ));
    }
}
