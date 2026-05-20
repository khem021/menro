<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * FreshDemoSeeder
 * ─────────────────────────────────────────────────────────
 * Run AFTER ClearDummyDataSeeder (which keeps admin user + 14 real barangays).
 * Populates every module with realistic data for Madrid, Surigao del Sur.
 *
 *   php artisan db:seed --class=FreshDemoSeeder
 */
class FreshDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding fresh demo data…');

        // ── 1. Users ─────────────────────────────────────────────────────────
        $roles = DB::table('roles')->pluck('role_id', 'role_name');

        $newUsers = [
            ['username' => 'menro',     'full_name' => 'Ma. Lourdes Dela Cruz',   'email' => 'menro@madrid.gov.ph',      'role' => 'MENRO Officer'],
            ['username' => 'encoder',   'full_name' => 'Roberto Santillan Jr.',   'email' => 'encoder@madrid.gov.ph',    'role' => 'Data Encoder'],
            ['username' => 'inspector', 'full_name' => 'Felix T. Navarro',         'email' => 'inspector@madrid.gov.ph',  'role' => 'Field Inspector'],
            ['username' => 'brgy_user', 'full_name' => 'Natividad Reyes',          'email' => 'barangay@madrid.gov.ph',   'role' => 'Barangay User'],
            ['username' => 'viewer',    'full_name' => 'Cristina M. Aquino',       'email' => 'viewer@madrid.gov.ph',     'role' => 'Report Viewer'],
        ];

        foreach ($newUsers as $u) {
            if (!DB::table('users')->where('username', $u['username'])->exists()) {
                DB::table('users')->insert([
                    'username'      => $u['username'],
                    'full_name'     => $u['full_name'],
                    'email'         => $u['email'],
                    'password_hash' => bcrypt('admin123'),
                    'role_id'       => $roles[$u['role']],
                    'status'        => 'active',
                    'created_at'    => now()->subDays(rand(90, 180)),
                    'updated_at'    => now(),
                ]);
            }
        }

        $adminId    = DB::table('users')->where('username', 'admin')->value('user_id');
        $menroId    = DB::table('users')->where('username', 'menro')->value('user_id');
        $encoderId  = DB::table('users')->where('username', 'encoder')->value('user_id');
        $inspId     = DB::table('users')->where('username', 'inspector')->value('user_id');

        $this->command->info('  ✓ Users seeded');

        // ── 2. Barangay clusters (assign the 14 to 3 clusters) ───────────────
        $brgys = DB::table('barangays')->pluck('barangay_id', 'barangay_name');

        $clusterMap = [
            1 => ['Bagsac', 'Bayogo', 'Linibonan', 'Magsaysay', 'Manga'],
            2 => ['Panayogon', 'Patong Patong', 'Quirino', 'San Antonio', 'San Juan'],
            3 => ['San Roque', 'San Vicente', 'Songkit', 'Union'],
        ];

        foreach ($clusterMap as $cluster => $names) {
            foreach ($names as $name) {
                if (isset($brgys[$name])) {
                    DB::table('barangays')
                        ->where('barangay_id', $brgys[$name])
                        ->update(['cluster' => $cluster]);
                }
            }
        }

        $this->command->info('  ✓ Barangay clusters assigned');

        // ── 3. Waste Generators ───────────────────────────────────────────────
        // type_id: 1=Residential, 2=Commercial, 3=Industrial, 4=Institutional, 5=Agricultural
        $generators = [
            // Bagsac
            ['name' => 'Bagsac Barangay Hall',             'brgy' => 'Bagsac',      'type' => 4, 'daily_kg' => 12,  'compliance' => 'compliant',     'contact' => 'Capt. Armando Basa'],
            ['name' => 'Bagsac Elementary School',         'brgy' => 'Bagsac',      'type' => 4, 'daily_kg' => 25,  'compliance' => 'compliant',     'contact' => 'Mrs. Felicidad Ong'],
            ['name' => 'Bagsac Multi-Purpose Cooperative', 'brgy' => 'Bagsac',      'type' => 2, 'daily_kg' => 18,  'compliance' => 'for_inspection', 'contact' => 'Bernardo Tan'],
            // Bayogo
            ['name' => 'Bayogo Barangay Hall',             'brgy' => 'Bayogo',      'type' => 4, 'daily_kg' => 10,  'compliance' => 'compliant',     'contact' => 'Capt. Rosario Hilario'],
            ['name' => 'Bayogo Farm Supply Store',         'brgy' => 'Bayogo',      'type' => 2, 'daily_kg' => 22,  'compliance' => 'for_inspection', 'contact' => 'Alejandro Chua'],
            ['name' => 'Bayogo Rice & Corn Farm',          'brgy' => 'Bayogo',      'type' => 5, 'daily_kg' => 95,  'compliance' => 'compliant',     'contact' => 'Pedro Villanueva'],
            // Linibonan
            ['name' => 'Linibonan Day Care Center',        'brgy' => 'Linibonan',   'type' => 4, 'daily_kg' => 8,   'compliance' => 'compliant',     'contact' => 'Teacher Erlinda Solis'],
            ['name' => 'Linibonan Tiangge Area',           'brgy' => 'Linibonan',   'type' => 2, 'daily_kg' => 60,  'compliance' => 'non_compliant', 'contact' => 'Anastacio Regala'],
            ['name' => 'Linibonan Piggery Farm',           'brgy' => 'Linibonan',   'type' => 5, 'daily_kg' => 180, 'compliance' => 'non_compliant', 'contact' => 'Rolando Cagaanan'],
            // Magsaysay
            ['name' => 'Magsaysay Public Market',          'brgy' => 'Magsaysay',   'type' => 2, 'daily_kg' => 280, 'compliance' => 'for_inspection', 'contact' => 'Elena Bautista'],
            ['name' => 'Magsaysay National High School',   'brgy' => 'Magsaysay',   'type' => 4, 'daily_kg' => 75,  'compliance' => 'compliant',     'contact' => 'Principal Rogelio Cruz'],
            ['name' => 'Magsaysay Municipal Hall',         'brgy' => 'Magsaysay',   'type' => 4, 'daily_kg' => 45,  'compliance' => 'compliant',     'contact' => 'HR Head Josefina Ramos'],
            ['name' => 'Magsaysay District Hospital',      'brgy' => 'Magsaysay',   'type' => 4, 'daily_kg' => 90,  'compliance' => 'compliant',     'contact' => 'Dr. Alfredo Mendoza'],
            ['name' => 'Magsaysay Rice Mill',              'brgy' => 'Magsaysay',   'type' => 3, 'daily_kg' => 220, 'compliance' => 'non_compliant', 'contact' => 'Felixberto Abad'],
            ['name' => 'Golden Arch Restaurant & Lodge',   'brgy' => 'Magsaysay',   'type' => 2, 'daily_kg' => 80,  'compliance' => 'for_inspection', 'contact' => 'Wilma Go'],
            // Manga
            ['name' => 'Manga Barangay Hall',              'brgy' => 'Manga',       'type' => 4, 'daily_kg' => 11,  'compliance' => 'compliant',     'contact' => 'Capt. Renato Aguilar'],
            ['name' => 'Manga Fishing Community',          'brgy' => 'Manga',       'type' => 1, 'daily_kg' => 55,  'compliance' => 'for_inspection', 'contact' => 'Domingo Lastimosa'],
            ['name' => 'Manga Banana Plantation',          'brgy' => 'Manga',       'type' => 5, 'daily_kg' => 160, 'compliance' => 'compliant',     'contact' => 'Nestor Padilla'],
            // Panayogon
            ['name' => 'Panayogon Barangay Hall',          'brgy' => 'Panayogon',   'type' => 4, 'daily_kg' => 10,  'compliance' => 'compliant',     'contact' => 'Capt. Virgilio Diaz'],
            ['name' => 'Panayogon Elementary School',      'brgy' => 'Panayogon',   'type' => 4, 'daily_kg' => 22,  'compliance' => 'compliant',     'contact' => 'Mrs. Consuelo Perez'],
            ['name' => 'Panayogon Sari-Sari Cluster',      'brgy' => 'Panayogon',   'type' => 2, 'daily_kg' => 40,  'compliance' => 'for_inspection', 'contact' => 'Ligaya Torres'],
            // Patong Patong
            ['name' => 'Patong Patong Barangay Hall',      'brgy' => 'Patong Patong','type' => 4, 'daily_kg' => 9,  'compliance' => 'compliant',     'contact' => 'Capt. Milagros Flores'],
            ['name' => 'Patong Patong Poultry Farm',       'brgy' => 'Patong Patong','type' => 5, 'daily_kg' => 145, 'compliance' => 'non_compliant', 'contact' => 'Alberto Gomez'],
            // Quirino
            ['name' => 'Quirino Barangay Hall',            'brgy' => 'Quirino',     'type' => 4, 'daily_kg' => 11,  'compliance' => 'compliant',     'contact' => 'Capt. Noel Enriquez'],
            ['name' => 'Quirino Gasoline Station',         'brgy' => 'Quirino',     'type' => 2, 'daily_kg' => 28,  'compliance' => 'compliant',     'contact' => 'Rodrigo Santos'],
            ['name' => 'Quirino Coconut Processing Plant', 'brgy' => 'Quirino',     'type' => 3, 'daily_kg' => 255, 'compliance' => 'for_inspection', 'contact' => 'Loreto Aquino'],
            // San Antonio
            ['name' => 'San Antonio Barangay Hall',        'brgy' => 'San Antonio', 'type' => 4, 'daily_kg' => 12,  'compliance' => 'compliant',     'contact' => 'Capt. Simplicio Dela Torre'],
            ['name' => 'San Antonio Health Center',        'brgy' => 'San Antonio', 'type' => 4, 'daily_kg' => 20,  'compliance' => 'compliant',     'contact' => 'Nurse-in-Charge Perla Lim'],
            ['name' => 'San Antonio Hardware Store',       'brgy' => 'San Antonio', 'type' => 2, 'daily_kg' => 35,  'compliance' => 'compliant',     'contact' => 'Marcelo Vergara'],
            // San Juan
            ['name' => 'San Juan Parish Church',           'brgy' => 'San Juan',    'type' => 4, 'daily_kg' => 22,  'compliance' => 'compliant',     'contact' => 'Fr. Domingo Cabrera'],
            ['name' => 'San Juan Elementary School',       'brgy' => 'San Juan',    'type' => 4, 'daily_kg' => 30,  'compliance' => 'compliant',     'contact' => 'Mrs. Teresita Fontanilla'],
            ['name' => 'San Juan Fishing Port',            'brgy' => 'San Juan',    'type' => 2, 'daily_kg' => 175, 'compliance' => 'non_compliant', 'contact' => 'Maximino Ocampo'],
            // San Roque
            ['name' => 'San Roque Barangay Hall',          'brgy' => 'San Roque',   'type' => 4, 'daily_kg' => 10,  'compliance' => 'compliant',     'contact' => 'Capt. Gloria Ramirez'],
            ['name' => 'San Roque Piggery Operations',     'brgy' => 'San Roque',   'type' => 5, 'daily_kg' => 210, 'compliance' => 'non_compliant', 'contact' => 'Ernesto Valenzuela'],
            // San Vicente
            ['name' => 'San Vicente Barangay Hall',        'brgy' => 'San Vicente', 'type' => 4, 'daily_kg' => 11,  'compliance' => 'compliant',     'contact' => 'Capt. Caridad Morales'],
            ['name' => 'San Vicente Beach Resort',         'brgy' => 'San Vicente', 'type' => 2, 'daily_kg' => 65,  'compliance' => 'compliant',     'contact' => 'Dolores Marquez'],
            ['name' => 'San Vicente Abattoir',             'brgy' => 'San Vicente', 'type' => 3, 'daily_kg' => 125, 'compliance' => 'for_inspection', 'contact' => 'Epifanio Lorenzo'],
            // Songkit
            ['name' => 'Songkit Barangay Hall',            'brgy' => 'Songkit',     'type' => 4, 'daily_kg' => 9,   'compliance' => 'compliant',     'contact' => 'Capt. Benjamin Castillo'],
            ['name' => 'Songkit Elementary School',        'brgy' => 'Songkit',     'type' => 4, 'daily_kg' => 18,  'compliance' => 'compliant',     'contact' => 'Mrs. Purificacion Santos'],
            ['name' => 'Songkit Banana & Cassava Farm',    'brgy' => 'Songkit',     'type' => 5, 'daily_kg' => 130, 'compliance' => 'for_inspection', 'contact' => 'Feliciano Madera'],
            // Union
            ['name' => 'Union Barangay Hall',              'brgy' => 'Union',       'type' => 4, 'daily_kg' => 10,  'compliance' => 'compliant',     'contact' => 'Capt. Leonardo Cabantog'],
            ['name' => 'Union Livestock Farm',             'brgy' => 'Union',       'type' => 5, 'daily_kg' => 190, 'compliance' => 'non_compliant', 'contact' => 'Tranquilino Geronimo'],
            ['name' => 'Union Community Store',            'brgy' => 'Union',       'type' => 2, 'daily_kg' => 32,  'compliance' => 'compliant',     'contact' => 'Mirasol Buenaventura'],
        ];

        $genRows = [];
        foreach ($generators as $g) {
            $bid = $brgys[$g['brgy']] ?? null;
            if (!$bid) continue;
            $genRows[] = [
                'generator_name'            => $g['name'],
                'generator_type_id'         => $g['type'],
                'barangay_id'               => $bid,
                'address'                   => $g['brgy'] . ', Madrid, Surigao del Sur',
                'contact_person'            => $g['contact'],
                'contact_number'            => '09' . rand(100, 999) . rand(1000, 9999),
                'email'                     => null,
                'estimated_daily_waste_kg'  => $g['daily_kg'],
                'compliance_status'         => $g['compliance'],
                'status'                    => 'active',
                'created_by'                => $adminId,
                'created_at'                => now()->subDays(rand(60, 365)),
                'updated_at'                => now(),
            ];
        }
        DB::table('waste_generators')->insert($genRows);

        $genIds = DB::table('waste_generators')->pluck('generator_id')->toArray();
        $this->command->info('  ✓ ' . count($genRows) . ' waste generators seeded');

        // ── 4. Waste Entries (12 months) ──────────────────────────────────────
        // category_id: 1=Biodegradable, 2=Recyclable, 3=Residual, 4=Hazardous, 5=E-Waste, 6=Construction
        $entries   = [];
        $startDate = Carbon::now()->subMonths(12)->startOfMonth();

        // Hospital → category 4 (hazardous) extra entries
        $hospitalId = DB::table('waste_generators')->where('generator_name', 'Magsaysay District Hospital')->value('generator_id');
        // Rice mill → category 6 (construction debris / processing residue) extra entries
        $riceMillId = DB::table('waste_generators')->where('generator_name', 'Magsaysay Rice Mill')->value('generator_id');

        foreach ($genIds as $gid) {
            $perMonth = rand(3, 6);
            for ($m = 0; $m < 12; $m++) {
                $md = $startDate->copy()->addMonths($m);
                for ($e = 0; $e < $perMonth; $e++) {
                    $catId = rand(1, 3); // mostly biodegradable/recyclable/residual
                    if ($gid == $hospitalId) $catId = 4;
                    $entries[] = [
                        'generator_id' => $gid,
                        'category_id'  => $catId,
                        'quantity'     => round(rand(40, 480) + (rand(0, 99) / 100), 2),
                        'unit'         => 'kg',
                        'entry_date'   => $md->copy()->addDays(rand(0, 26))->toDateString(),
                        'remarks'      => null,
                        'encoded_by'   => $encoderId,
                        'created_at'   => $md->copy()->addDays(rand(0, 26))->setHour(rand(8, 17)),
                        'updated_at'   => $md->copy()->addDays(rand(0, 26))->setHour(rand(8, 17)),
                    ];
                }
            }
        }

        // Special processing waste for rice mill (category 6)
        if ($riceMillId) {
            for ($m = 0; $m < 12; $m++) {
                $md = $startDate->copy()->addMonths($m);
                $entries[] = [
                    'generator_id' => $riceMillId,
                    'category_id'  => 6,
                    'quantity'     => round(rand(250, 650) + (rand(0, 99) / 100), 2),
                    'unit'         => 'kg',
                    'entry_date'   => $md->copy()->addDays(rand(1, 26))->toDateString(),
                    'remarks'      => 'Rice hull and processing residue — monthly collection',
                    'encoded_by'   => $encoderId,
                    'created_at'   => $md->copy()->addDays(rand(1, 26))->setHour(9),
                    'updated_at'   => $md->copy()->addDays(rand(1, 26))->setHour(9),
                ];
            }
        }

        foreach (array_chunk($entries, 200) as $chunk) {
            DB::table('waste_entries')->insert($chunk);
        }
        $this->command->info('  ✓ ' . count($entries) . ' waste entries seeded');

        // ── 5. Inspections ────────────────────────────────────────────────────
        $inspectionTargets = [
            // compliant
            ['name' => 'Magsaysay Municipal Hall',         'status' => 'compliant',    'score' => 96, 'days' => 12],
            ['name' => 'Magsaysay National High School',   'status' => 'compliant',    'score' => 92, 'days' => 20],
            ['name' => 'Magsaysay District Hospital',      'status' => 'compliant',    'score' => 98, 'days' => 8],
            ['name' => 'San Antonio Barangay Hall',        'status' => 'compliant',    'score' => 90, 'days' => 30],
            ['name' => 'San Antonio Health Center',        'status' => 'compliant',    'score' => 94, 'days' => 25],
            ['name' => 'San Antonio Hardware Store',       'status' => 'compliant',    'score' => 88, 'days' => 18],
            ['name' => 'San Juan Parish Church',           'status' => 'compliant',    'score' => 93, 'days' => 15],
            ['name' => 'San Juan Elementary School',       'status' => 'compliant',    'score' => 91, 'days' => 22],
            ['name' => 'San Vicente Beach Resort',         'status' => 'compliant',    'score' => 87, 'days' => 35],
            ['name' => 'San Roque Barangay Hall',          'status' => 'compliant',    'score' => 89, 'days' => 40],
            ['name' => 'Bagsac Barangay Hall',             'status' => 'compliant',    'score' => 91, 'days' => 28],
            ['name' => 'Bayogo Rice & Corn Farm',          'status' => 'compliant',    'score' => 85, 'days' => 45],
            // warning / for_follow_up
            ['name' => 'Magsaysay Public Market',          'status' => 'warning',      'score' => 62, 'days' => 10],
            ['name' => 'Golden Arch Restaurant & Lodge',   'status' => 'warning',      'score' => 66, 'days' => 7],
            ['name' => 'Manga Fishing Community',          'status' => 'for_follow_up','score' => 58, 'days' => 14],
            ['name' => 'Panayogon Sari-Sari Cluster',      'status' => 'for_follow_up','score' => 61, 'days' => 9],
            ['name' => 'Quirino Coconut Processing Plant', 'status' => 'warning',      'score' => 64, 'days' => 5],
            ['name' => 'San Vicente Abattoir',             'status' => 'for_follow_up','score' => 57, 'days' => 11],
            ['name' => 'Songkit Banana & Cassava Farm',    'status' => 'for_follow_up','score' => 55, 'days' => 16],
            ['name' => 'Bayogo Farm Supply Store',         'status' => 'warning',      'score' => 68, 'days' => 6],
            // violations
            ['name' => 'Linibonan Tiangge Area',           'status' => 'violation',    'score' => 30, 'days' => 9],
            ['name' => 'Linibonan Piggery Farm',           'status' => 'violation',    'score' => 22, 'days' => 11],
            ['name' => 'Magsaysay Rice Mill',              'status' => 'violation',    'score' => 25, 'days' => 7],
            ['name' => 'Patong Patong Poultry Farm',       'status' => 'violation',    'score' => 27, 'days' => 13],
            ['name' => 'San Juan Fishing Port',            'status' => 'violation',    'score' => 31, 'days' => 4],
            ['name' => 'San Roque Piggery Operations',     'status' => 'violation',    'score' => 20, 'days' => 6],
            ['name' => 'Union Livestock Farm',             'status' => 'violation',    'score' => 24, 'days' => 8],
        ];

        $remarkMap = [
            'compliant'     => 'Waste segregation is properly practised. All bins labelled. Records up to date.',
            'warning'       => 'Minor segregation issues noted. Missing colour-coded bins in some areas. Verbal warning given.',
            'for_follow_up' => 'Partial compliance. Corrective action plan required within 15 days.',
            'violation'     => 'Serious violations found — open dumping and improper storage observed. Formal notice issued.',
        ];
        $recoMap = [
            'compliant'     => 'Maintain current practices. Submit updated waste management plan quarterly.',
            'warning'       => 'Provide segregation bins per waste type. Train staff. Re-inspect in 30 days.',
            'for_follow_up' => 'Submit corrective action plan within 15 days. Re-inspection scheduled.',
            'violation'     => 'Cease illegal disposal immediately. Attend MENRO compliance seminar. Penalty notice filed.',
        ];

        $violationInspectionIds = [];
        $warningInspectionIds   = [];

        foreach ($inspectionTargets as $d) {
            $gid = DB::table('waste_generators')->where('generator_name', $d['name'])->value('generator_id');
            if (!$gid) continue;

            $insDate  = Carbon::now()->subDays($d['days']);
            $followUp = $d['status'] === 'compliant' ? null : $insDate->copy()->addDays(30)->toDateString();

            $insId = DB::table('inspections')->insertGetId([
                'generator_id'      => $gid,
                'inspection_date'   => $insDate->toDateString(),
                'inspector_id'      => $inspId,
                'compliance_status' => $d['status'],
                'segregation_score' => $d['score'],
                'remarks'           => $remarkMap[$d['status']],
                'recommendation'    => $recoMap[$d['status']],
                'next_follow_up'    => $followUp,
                'created_at'        => $insDate,
                'updated_at'        => $insDate,
            ]);

            if ($d['status'] === 'violation') {
                $violationInspectionIds[] = $insId;
            } elseif ($d['status'] === 'warning') {
                $warningInspectionIds[] = $insId;
            }
        }

        $this->command->info('  ✓ ' . count($inspectionTargets) . ' inspections seeded');

        // ── 6. Violations ─────────────────────────────────────────────────────
        $vtypes = [
            ['type' => 'Improper Waste Segregation',        'sev' => 'medium',   'desc' => 'Biodegradable and non-biodegradable waste mixed together in collection bins.'],
            ['type' => 'Open Dumping',                       'sev' => 'high',     'desc' => 'Waste openly dumped in an unauthorised area within the premises.'],
            ['type' => 'Lack of Segregation Bins',           'sev' => 'low',      'desc' => 'No designated bins for each waste category.'],
            ['type' => 'Improper Hazardous Waste Storage',   'sev' => 'critical', 'desc' => 'Hazardous waste stored in open unlabelled containers.'],
            ['type' => 'Open Burning of Waste',              'sev' => 'high',     'desc' => 'Evidence of open burning of solid waste on premises.'],
            ['type' => 'No Waste Management Plan',           'sev' => 'medium',   'desc' => 'No documented waste management and disposal plan on file.'],
        ];

        // Violations from violation-status inspections (open)
        foreach ($violationInspectionIds as $insId) {
            $shuffled = $vtypes;
            shuffle($shuffled);
            $count = rand(2, 3);
            for ($i = 0; $i < $count; $i++) {
                DB::table('violations')->insert([
                    'inspection_id'     => $insId,
                    'violation_type'    => $shuffled[$i]['type'],
                    'description'       => $shuffled[$i]['desc'],
                    'severity'          => $shuffled[$i]['sev'],
                    'penalty_status'    => 'penalty_pending',
                    'resolution_status' => 'open',
                    'resolved_date'     => null,
                    'created_at'        => now()->subDays(rand(2, 14)),
                    'updated_at'        => now(),
                ]);
            }
        }

        // Warnings from warning-status inspections (in_progress)
        foreach ($warningInspectionIds as $insId) {
            DB::table('violations')->insert([
                'inspection_id'     => $insId,
                'violation_type'    => $vtypes[array_rand($vtypes)]['type'],
                'description'       => 'Warning-level compliance issue noted during routine inspection.',
                'severity'          => 'low',
                'penalty_status'    => 'warning_issued',
                'resolution_status' => 'in_progress',
                'resolved_date'     => null,
                'created_at'        => now()->subDays(rand(3, 10)),
                'updated_at'        => now(),
            ]);
        }

        // A few resolved historical violations
        $anyInsId = DB::table('inspections')->value('inspection_id');
        if ($anyInsId) {
            DB::table('violations')->insert([
                'inspection_id'     => $anyInsId,
                'violation_type'    => 'Clogged Drainage from Waste Runoff',
                'description'       => 'Waste disposal caused drainage blockage. Cleaned up and resolved.',
                'severity'          => 'medium',
                'penalty_status'    => 'penalty_applied',
                'resolution_status' => 'resolved',
                'resolved_date'     => Carbon::now()->subDays(18)->toDateString(),
                'created_at'        => Carbon::now()->subDays(50),
                'updated_at'        => Carbon::now()->subDays(18),
            ]);
        }

        $this->command->info('  ✓ Violations seeded');

        // ── 7. Collection Schedules + Records ────────────────────────────────
        $brgyIdList  = array_values($brgys->toArray());
        $wasteTypes  = ['Mixed Solid Waste', 'Biodegradable', 'Recyclable Materials', 'Residual Waste'];
        $teams       = ['Team Alpha', 'Team Bravo', 'Team Charlie'];
        $vehicles    = ['Dump Truck DT-01', 'Garbage Truck GT-02', 'Mini Truck MT-03'];

        $scheduleRows = [];
        $recordRows   = [];

        // Past completed/missed — 12 weeks back, biweekly per barangay
        for ($w = 1; $w <= 12; $w++) {
            $date = Carbon::now()->subWeeks($w)->startOfWeek()->addDays(1); // Tuesday
            foreach ($brgyIdList as $bid) {
                $status = rand(0, 9) > 1 ? 'completed' : 'missed';
                $schId  = DB::table('collection_schedules')->insertGetId([
                    'barangay_id'      => $bid,
                    'collection_date'  => $date->toDateString(),
                    'waste_type'       => $wasteTypes[array_rand($wasteTypes)],
                    'assigned_team'    => $teams[array_rand($teams)],
                    'assigned_vehicle' => $vehicles[array_rand($vehicles)],
                    'status'           => $status,
                    'notes'            => null,
                    'created_by'       => $menroId,
                    'created_at'       => $date->copy()->subDays(7),
                    'updated_at'       => $date->copy()->addDay(),
                ]);

                if ($status === 'completed') {
                    $recordRows[] = [
                        'schedule_id'            => $schId,
                        'actual_collection_date' => $date->toDateString(),
                        'collected_quantity'     => round(rand(80, 500) + (rand(0, 99) / 100), 2),
                        'unit'                   => 'kg',
                        'remarks'                => null,
                        'completed_by'           => $encoderId,
                        'created_at'             => $date->copy()->addHours(rand(8, 16)),
                        'updated_at'             => $date->copy()->addHours(rand(8, 16)),
                    ];
                }
            }
        }

        // Upcoming — 8 weeks ahead, pending/confirmed
        for ($w = 0; $w < 8; $w++) {
            $date = Carbon::now()->addWeeks($w)->next(Carbon::TUESDAY);
            foreach ($brgyIdList as $bid) {
                DB::table('collection_schedules')->insert([
                    'barangay_id'      => $bid,
                    'collection_date'  => $date->toDateString(),
                    'waste_type'       => $wasteTypes[array_rand($wasteTypes)],
                    'assigned_team'    => $teams[array_rand($teams)],
                    'assigned_vehicle' => $vehicles[array_rand($vehicles)],
                    'status'           => $w === 0 ? 'confirmed' : 'pending',
                    'notes'            => null,
                    'created_by'       => $menroId,
                    'created_at'       => now()->subDays(rand(1, 7)),
                    'updated_at'       => now(),
                ]);
            }
        }

        if (!empty($recordRows)) {
            foreach (array_chunk($recordRows, 100) as $chunk) {
                DB::table('collection_records')->insert($chunk);
            }
        }

        $totalSchedules = DB::table('collection_schedules')->count();
        $this->command->info('  ✓ ' . $totalSchedules . ' collection schedules + ' . count($recordRows) . ' collection records seeded');

        // ── 8. Incidents ──────────────────────────────────────────────────────
        $incidentData = [
            ['brgy' => 'Linibonan',    'type' => 'illegal_dumping',   'status' => 'under_investigation',
             'desc' => 'Illegal dumping of construction debris found along the creek embankment.',
             'loc'  => 'Near Linibonan creek access road', 'days' => 3],

            ['brgy' => 'Magsaysay',    'type' => 'improper_disposal', 'status' => 'under_investigation',
             'desc' => 'Rice mill waste water and husk discharged directly into the irrigation canal.',
             'loc'  => 'Irrigation canal near Magsaysay Rice Mill', 'days' => 5],

            ['brgy' => 'San Juan',     'type' => 'illegal_dumping',   'status' => 'for_validation',
             'desc' => 'Suspected midnight dumping of fish processing waste at the port access road.',
             'loc'  => 'San Juan Fishing Port perimeter', 'days' => 4],

            ['brgy' => 'Patong Patong','type' => 'improper_disposal', 'status' => 'reported',
             'desc' => 'Poultry farm waste not properly treated, causing foul odour complaints from neighbours.',
             'loc'  => 'Patong Patong Poultry Farm vicinity', 'days' => 2],

            ['brgy' => 'San Roque',    'type' => 'open_burning',      'status' => 'reported',
             'desc' => 'Residents reported smoke from piggery burning plastic waste and animal remains.',
             'loc'  => 'San Roque Piggery, south compound', 'days' => 1],

            ['brgy' => 'Quirino',      'type' => 'improper_disposal', 'status' => 'for_validation',
             'desc' => 'Coconut processing liquid waste found seeping into the riverbank.',
             'loc'  => 'North side of Quirino Processing Plant', 'days' => 6],

            ['brgy' => 'Union',        'type' => 'illegal_dumping',   'status' => 'reported',
             'desc' => 'Livestock farm waste found dumped on the road shoulder near the barangay boundary.',
             'loc'  => 'Union-Songkit boundary road', 'days' => 1],

            ['brgy' => 'Manga',        'type' => 'other',             'status' => 'resolved',
             'desc' => 'Overflowing waste bins at the fishing community caused spillage onto the walkway.',
             'loc'  => 'Manga Fish Landing Area', 'days' => 20],

            ['brgy' => 'San Vicente',  'type' => 'open_burning',      'status' => 'resolved',
             'desc' => 'Resort staff burning garden waste and plastic together near the shoreline.',
             'loc'  => 'San Vicente Beach Resort back area', 'days' => 18],

            ['brgy' => 'Songkit',      'type' => 'improper_disposal', 'status' => 'under_investigation',
             'desc' => 'Agricultural chemical containers found improperly disposed near water source.',
             'loc'  => 'Songkit farm eastern boundary', 'days' => 7],
        ];

        foreach ($incidentData as $d) {
            $bid        = $brgys[$d['brgy']] ?? null;
            if (!$bid) continue;
            $resolved   = $d['status'] === 'resolved';
            DB::table('incidents')->insert([
                'barangay_id'      => $bid,
                'reported_by'      => $inspId,
                'incident_type'    => $d['type'],
                'description'      => $d['desc'],
                'location_details' => $d['loc'],
                'date_reported'    => Carbon::now()->subDays($d['days'])->toDateString(),
                'status'           => $d['status'],
                'assigned_to'      => $menroId,
                'resolution_notes' => $resolved ? 'Site cleaned up. Generator formally warned. Monitoring in place.' : null,
                'resolved_at'      => $resolved ? Carbon::now()->subDays($d['days'] - 3) : null,
                'created_at'       => Carbon::now()->subDays($d['days']),
                'updated_at'       => now(),
            ]);
        }

        $this->command->info('  ✓ ' . count($incidentData) . ' incidents seeded');

        // ── 9. Notifications ─────────────────────────────────────────────────
        $allUserIds = DB::table('users')->pluck('user_id')->toArray();
        $notifRows  = [];

        $notifTemplates = [
            ['title' => 'New Violation Recorded',          'msg' => 'A new high-severity violation has been recorded for Magsaysay Rice Mill. Immediate action required.',              'type' => 'danger',  'days' => 7],
            ['title' => 'Inspection Due — San Juan FP',    'msg' => 'San Juan Fishing Port is scheduled for a follow-up inspection within 7 days.',                                    'type' => 'warning', 'days' => 5],
            ['title' => 'Collection Completed — Cluster 1','msg' => 'All Cluster 1 collection routes were completed successfully this week.',                                          'type' => 'success', 'days' => 3],
            ['title' => 'Incident Reported — Linibonan',   'msg' => 'An illegal dumping incident has been reported along the Linibonan creek road. Investigation underway.',           'type' => 'warning', 'days' => 3],
            ['title' => 'Compliance Rate Update',          'msg' => 'Monthly compliance rate is at 71% — 10 generators still flagged for inspection.',                                 'type' => 'info',    'days' => 2],
            ['title' => 'New User Account Created',        'msg' => 'A new Data Encoder account has been activated.',                                                                  'type' => 'info',    'days' => 14],
            ['title' => 'Violation Resolved',              'msg' => 'The drainage clogging violation has been marked as resolved after a satisfactory site visit.',                    'type' => 'success', 'days' => 18],
            ['title' => 'Monthly Waste Volume Report',     'msg' => 'Total waste volume recorded this month: approx 12,450 kg. See Reports for the full breakdown.',                  'type' => 'info',    'days' => 1],
            ['title' => 'Open Burning Incident — Panayogon','msg' => 'Open burning reported near the sitio boundary. Field inspector dispatched.',                                    'type' => 'danger',  'days' => 1],
            ['title' => 'Schedule Confirmation Required',  'msg' => '8 upcoming collection schedules are still marked as Pending. Please confirm assignments.',                        'type' => 'warning', 'days' => 0],
        ];

        foreach ($allUserIds as $uid) {
            foreach ($notifTemplates as $i => $t) {
                $isRead = $i < 6; // first 6 are read, last 4 unread
                $notifRows[] = [
                    'user_id'    => $uid,
                    'title'      => $t['title'],
                    'message'    => $t['msg'],
                    'type'       => $t['type'],
                    'is_read'    => $isRead,
                    'read_at'    => $isRead ? Carbon::now()->subDays($t['days'])->addHours(2) : null,
                    'created_at' => Carbon::now()->subDays($t['days']),
                    'updated_at' => Carbon::now()->subDays($t['days']),
                ];
            }
        }

        foreach (array_chunk($notifRows, 200) as $chunk) {
            DB::table('notifications')->insert($chunk);
        }

        $this->command->info('  ✓ ' . count($notifRows) . ' notifications seeded');
        $this->command->info('');
        $this->command->info('✅ FreshDemoSeeder complete!');
        $this->command->info('   Login: admin / menro / encoder / inspector / brgy_user / viewer');
        $this->command->info('   Password for all: admin123');
    }
}
