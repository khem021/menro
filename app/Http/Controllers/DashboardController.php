<?php

namespace App\Http\Controllers;

use App\Models\CollectionSchedule;
use App\Models\Incident;
use App\Models\Inspection;
use App\Models\Violation;
use App\Models\WasteEntry;
use App\Models\WasteGenerator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $kpis = Cache::remember('dashboard:kpis', 60, function () {
            $thisMonthStart = now()->startOfMonth()->toDateString();
            $lastMonthStart = now()->subMonth()->startOfMonth()->toDateString();
            $lastMonthEnd   = now()->subMonth()->endOfMonth()->toDateString();

            $gen = WasteGenerator::selectRaw("
                COUNT(*) AS total,
                SUM(status = 'active') AS active,
                SUM(compliance_status = 'compliant') AS compliant,
                SUM(compliance_status = 'non_compliant') AS non_compliant,
                SUM(compliance_status = 'for_inspection') AS for_inspection
            ")->first();

            $waste = WasteEntry::selectRaw("
                SUM(CASE WHEN entry_date >= ? THEN quantity ELSE 0 END) AS this_month,
                SUM(CASE WHEN entry_date BETWEEN ? AND ? THEN quantity ELSE 0 END) AS last_month
            ", [$thisMonthStart, $lastMonthStart, $lastMonthEnd])->first();

            $viol = Violation::selectRaw("
                SUM(resolution_status = 'open') AS open_,
                SUM(severity = 'critical' AND resolution_status = 'open') AS critical_
            ")->first();

            $openIncidents      = Incident::whereIn('status', ['reported', 'for_validation', 'under_investigation'])->count();
            $pendingCollections = CollectionSchedule::where('status', 'pending')->count();

            return [
                'total_generators'     => (int) $gen->total,
                'active_generators'    => (int) $gen->active,
                'compliant'            => (int) $gen->compliant,
                'non_compliant'        => (int) $gen->non_compliant,
                'for_inspection'       => (int) $gen->for_inspection,
                'this_month_waste'     => (float) $waste->this_month,
                'last_month_waste'     => (float) $waste->last_month,
                'open_violations'      => (int) $viol->open_,
                'critical_violations'  => (int) $viol->critical_,
                'open_incidents'       => (int) $openIncidents,
                'pending_collections'  => (int) $pendingCollections,
            ];
        });

        $totalGenerators     = $kpis['total_generators'];
        $activeGenerators    = $kpis['active_generators'];
        $compliantCount      = $kpis['compliant'];
        $nonCompliantCount   = $kpis['non_compliant'];
        $forInspectionCount  = $kpis['for_inspection'];
        $thisMonthWaste      = $kpis['this_month_waste'];
        $lastMonthWaste      = $kpis['last_month_waste'];
        $openViolations      = $kpis['open_violations'];
        $criticalViolations  = $kpis['critical_violations'];
        $openIncidents       = $kpis['open_incidents'];
        $pendingCollections  = $kpis['pending_collections'];

        $wasteTrendPct = $lastMonthWaste > 0
            ? round((($thisMonthWaste - $lastMonthWaste) / $lastMonthWaste) * 100, 1)
            : ($thisMonthWaste > 0 ? 100 : 0);

        $recentEntries = Cache::remember('dashboard:recent_entries', 60, fn() =>
            WasteEntry::with([
                'wasteGenerator:generator_id,generator_name',
                'wasteCategory:category_id,category_name',
            ])
            ->latest('entry_date')
            ->limit(5)
            ->get()
        );

        $recentIncidents = Cache::remember('dashboard:recent_incidents', 60, fn() =>
            Incident::with('barangay:barangay_id,barangay_name')
                ->latest('date_reported')
                ->limit(5)
                ->get()
        );

        $charts = Cache::remember('dashboard:charts', 300, function () {
            $monthly = DB::select("
                SELECT
                    YEAR(entry_date) AS yr,
                    MONTH(entry_date) AS mo,
                    SUM(quantity) AS total
                FROM waste_entries
                WHERE entry_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY YEAR(entry_date), MONTH(entry_date)
                ORDER BY yr ASC, mo ASC
            ");

            $cluster = DB::select("
                SELECT b.cluster, SUM(we.quantity) AS total
                FROM waste_entries we
                JOIN waste_generators wg ON we.generator_id = wg.generator_id
                JOIN barangays b ON wg.barangay_id = b.barangay_id
                WHERE we.entry_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                  AND b.cluster IS NOT NULL
                GROUP BY b.cluster
                ORDER BY b.cluster ASC
            ");

            $category = DB::select("
                SELECT wc.category_name, SUM(we.quantity) AS total
                FROM waste_entries we
                JOIN waste_categories wc ON we.category_id = wc.category_id
                WHERE we.entry_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY wc.category_id, wc.category_name
                ORDER BY total DESC
            ");

            return compact('monthly', 'cluster', 'category');
        });
        $monthlyWasteData = $charts['monthly'];
        $clusterWaste     = $charts['cluster'];
        $categoryWaste    = $charts['category'];

        $upcomingByCluster = Cache::remember('dashboard:upcoming_collections', 120, fn() => DB::select("
            SELECT
                b.cluster,
                b.barangay_name,
                b.barangay_id,
                cs.collection_date,
                cs.waste_type,
                cs.assigned_team,
                cs.assigned_vehicle,
                cs.status
            FROM collection_schedules cs
            JOIN barangays b ON cs.barangay_id = b.barangay_id
            INNER JOIN (
                SELECT barangay_id, MIN(collection_date) AS next_date
                FROM collection_schedules
                WHERE collection_date >= CURDATE()
                  AND status IN ('pending', 'confirmed')
                GROUP BY barangay_id
            ) nxt ON cs.barangay_id = nxt.barangay_id AND cs.collection_date = nxt.next_date
            ORDER BY b.cluster ASC, cs.collection_date ASC, b.barangay_name ASC
        "));

        $collectionsByCluster = collect($upcomingByCluster)->groupBy('cluster');

        $alerts = Cache::remember('dashboard:alerts', 60, function () {
            return [
                'overdue_followups'   => Inspection::where('compliance_status', 'for_follow_up')
                    ->whereNotNull('next_follow_up')
                    ->where('next_follow_up', '<', Carbon::today()->toDateString())
                    ->count(),
                'tomorrow_collections' => CollectionSchedule::whereIn('status', ['pending', 'confirmed'])
                    ->where('collection_date', Carbon::tomorrow()->toDateString())
                    ->count(),
                'for_inspection'      => WasteGenerator::where('compliance_status', 'for_inspection')
                    ->where('status', 'active')
                    ->count(),
                'critical_violations' => Violation::where('resolution_status', 'open')
                    ->whereIn('severity', ['high', 'critical'])
                    ->count(),
            ];
        });

        return view('dashboard', compact(
            'totalGenerators',
            'activeGenerators',
            'compliantCount',
            'nonCompliantCount',
            'forInspectionCount',
            'thisMonthWaste',
            'lastMonthWaste',
            'wasteTrendPct',
            'openViolations',
            'criticalViolations',
            'openIncidents',
            'pendingCollections',
            'recentEntries',
            'recentIncidents',
            'monthlyWasteData',
            'clusterWaste',
            'categoryWaste',
            'collectionsByCluster',
            'alerts'
        ));
    }
}
