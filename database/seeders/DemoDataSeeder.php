<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $adminId    = DB::table('users')->where('username', 'admin')->value('user_id');
        $menroId    = DB::table('users')->where('username', 'menro')->value('user_id');
        $encoderId  = DB::table('users')->where('username', 'encoder')->value('user_id');
        $inspectorId= DB::table('users')->where('username', 'inspector')->value('user_id');

        // Barangay name → ID lookup
        // 14 barangays: Bagsac, Bayogo, Linibonan, Magsaysay, Manga,
        //               Panayogon, Patong Patong, Quirino, San Antonio, San Juan,
        //               San Roque, San Vicente, Songkit, Union
        $brgys = DB::table('barangays')->pluck('barangay_id', 'barangay_name');

        // ─── Waste Generators ────────────────────────────────────────────────
        // Types: 1=Residential, 2=Commercial, 3=Industrial, 4=Institutional, 5=Agricultural
        $generators = [
            ['generator_name' => 'Madrid Public Market',           'generator_type_id' => 2, 'brgy' => 'Magsaysay',
             'address' => 'Magsaysay, Madrid',           'contact_person' => 'Maria Santos',
             'contact_number' => '09171234567', 'email' => 'market@madrid.gov.ph',
             'estimated_daily_waste_kg' => 320.00, 'compliance_status' => 'compliant',     'status' => 'active'],

            ['generator_name' => 'Madrid Municipal Hall',          'generator_type_id' => 4, 'brgy' => 'Magsaysay',
             'address' => 'Magsaysay, Madrid',           'contact_person' => 'Jose Reyes',
             'contact_number' => '09182345678', 'email' => 'lgu@madrid.gov.ph',
             'estimated_daily_waste_kg' =>  45.00, 'compliance_status' => 'compliant',     'status' => 'active'],

            ['generator_name' => 'Madrid Central Elementary School','generator_type_id' => 4, 'brgy' => 'Magsaysay',
             'address' => 'Magsaysay, Madrid',           'contact_person' => 'Ana Cruz',
             'contact_number' => '09193456789', 'email' => 'mces@deped.ph',
             'estimated_daily_waste_kg' =>  60.00, 'compliance_status' => 'compliant',     'status' => 'active'],

            ['generator_name' => 'St. Joseph Parish Church',       'generator_type_id' => 4, 'brgy' => 'Magsaysay',
             'address' => 'Magsaysay, Madrid',           'contact_person' => 'Fr. Eduardo Tan',
             'contact_number' => '09204567890', 'email' => '',
             'estimated_daily_waste_kg' =>  25.00, 'compliance_status' => 'compliant',     'status' => 'active'],

            ['generator_name' => 'Golden Palace Restaurant',       'generator_type_id' => 2, 'brgy' => 'Magsaysay',
             'address' => 'National Hwy, Magsaysay, Madrid', 'contact_person' => 'Lin Wei',
             'contact_number' => '09215678901', 'email' => '',
             'estimated_daily_waste_kg' =>  85.00, 'compliance_status' => 'for_inspection','status' => 'active'],

            ['generator_name' => 'Bagsac Barangay Hall',           'generator_type_id' => 4, 'brgy' => 'Bagsac',
             'address' => 'Bagsac, Madrid',              'contact_person' => 'Capt. Pedro Velasco',
             'contact_number' => '09226789012', 'email' => '',
             'estimated_daily_waste_kg' =>  15.00, 'compliance_status' => 'compliant',     'status' => 'active'],

            ['generator_name' => 'Bayogo Elementary School',       'generator_type_id' => 4, 'brgy' => 'Bayogo',
             'address' => 'Bayogo, Madrid',              'contact_person' => 'Mila Domingo',
             'contact_number' => '09237890123', 'email' => '',
             'estimated_daily_waste_kg' =>  20.00, 'compliance_status' => 'compliant',     'status' => 'active'],

            ['generator_name' => 'Linibonan Tiangge Market',       'generator_type_id' => 2, 'brgy' => 'Linibonan',
             'address' => 'Linibonan, Madrid',           'contact_person' => 'Rosa Montoya',
             'contact_number' => '09248901234', 'email' => '',
             'estimated_daily_waste_kg' => 110.00, 'compliance_status' => 'non_compliant', 'status' => 'active'],

            ['generator_name' => 'Manga Farm Supply Store',        'generator_type_id' => 2, 'brgy' => 'Manga',
             'address' => 'Manga, Madrid',               'contact_person' => 'Renato Flores',
             'contact_number' => '09259012345', 'email' => '',
             'estimated_daily_waste_kg' =>  30.00, 'compliance_status' => 'for_inspection','status' => 'active'],

            ['generator_name' => 'Magsaysay Rice Mill',            'generator_type_id' => 3, 'brgy' => 'Magsaysay',
             'address' => 'Magsaysay, Madrid',           'contact_person' => 'Eduardo Bautista',
             'contact_number' => '09260123456', 'email' => 'ricemill@gmail.com',
             'estimated_daily_waste_kg' => 200.00, 'compliance_status' => 'non_compliant', 'status' => 'active'],

            ['generator_name' => 'Panayogon Health Center',        'generator_type_id' => 4, 'brgy' => 'Panayogon',
             'address' => 'Panayogon, Madrid',           'contact_person' => 'Dr. Carmen Lim',
             'contact_number' => '09271234560', 'email' => '',
             'estimated_daily_waste_kg' =>  18.00, 'compliance_status' => 'compliant',     'status' => 'active'],

            ['generator_name' => 'Quirino Coconut Processing Plant','generator_type_id' => 3, 'brgy' => 'Quirino',
             'address' => 'Quirino, Madrid',             'contact_person' => 'Fernando Garcia',
             'contact_number' => '09282345671', 'email' => '',
             'estimated_daily_waste_kg' => 280.00, 'compliance_status' => 'for_inspection','status' => 'active'],

            ['generator_name' => 'San Antonio Fishermen\'s Village','generator_type_id' => 1, 'brgy' => 'San Antonio',
             'address' => 'San Antonio, Madrid',         'contact_person' => 'Benjamin Navarro',
             'contact_number' => '09293456782', 'email' => '',
             'estimated_daily_waste_kg' =>  95.00, 'compliance_status' => 'for_inspection','status' => 'active'],

            ['generator_name' => 'San Juan Barangay Hall',         'generator_type_id' => 4, 'brgy' => 'San Juan',
             'address' => 'San Juan, Madrid',            'contact_person' => 'Capt. Leonora Padilla',
             'contact_number' => '09204567893', 'email' => '',
             'estimated_daily_waste_kg' =>  12.00, 'compliance_status' => 'compliant',     'status' => 'active'],

            ['generator_name' => 'San Roque Cooperative Store',    'generator_type_id' => 2, 'brgy' => 'San Roque',
             'address' => 'San Roque, Madrid',           'contact_person' => 'Gloria Ramos',
             'contact_number' => '09215678904', 'email' => '',
             'estimated_daily_waste_kg' =>  40.00, 'compliance_status' => 'compliant',     'status' => 'active'],

            ['generator_name' => 'Patong Patong Poultry Farm',     'generator_type_id' => 5, 'brgy' => 'Patong Patong',
             'address' => 'Patong Patong, Madrid',       'contact_person' => 'Ricardo Santos',
             'contact_number' => '09226789015', 'email' => '',
             'estimated_daily_waste_kg' => 150.00, 'compliance_status' => 'non_compliant', 'status' => 'active'],

            ['generator_name' => 'San Vicente Gasoline Station',   'generator_type_id' => 2, 'brgy' => 'San Vicente',
             'address' => 'San Vicente, Madrid',         'contact_person' => 'Alfredo Cruz',
             'contact_number' => '09237890126', 'email' => '',
             'estimated_daily_waste_kg' =>  22.00, 'compliance_status' => 'compliant',     'status' => 'active'],

            ['generator_name' => 'Songkit National High School',   'generator_type_id' => 4, 'brgy' => 'Songkit',
             'address' => 'Songkit, Madrid',             'contact_person' => 'Principal Roberto De Leon',
             'contact_number' => '09248901237', 'email' => 'snhs@deped.ph',
             'estimated_daily_waste_kg' =>  75.00, 'compliance_status' => 'compliant',     'status' => 'active'],

            ['generator_name' => 'Union Barangay Hall',            'generator_type_id' => 4, 'brgy' => 'Union',
             'address' => 'Union, Madrid',               'contact_person' => 'Capt. Virgilio Abad',
             'contact_number' => '09259012348', 'email' => '',
             'estimated_daily_waste_kg' =>  10.00, 'compliance_status' => 'compliant',     'status' => 'active'],

            ['generator_name' => 'Linibonan Hardware Store',       'generator_type_id' => 2, 'brgy' => 'Linibonan',
             'address' => 'Linibonan, Madrid',           'contact_person' => 'Dante Mercado',
             'contact_number' => '09260123459', 'email' => '',
             'estimated_daily_waste_kg' =>  35.00, 'compliance_status' => 'compliant',     'status' => 'active'],

            ['generator_name' => 'Manga Banana Plantation',        'generator_type_id' => 5, 'brgy' => 'Manga',
             'address' => 'Manga, Madrid',               'contact_person' => 'Nestor Villanueva',
             'contact_number' => '09271234570', 'email' => '',
             'estimated_daily_waste_kg' => 180.00, 'compliance_status' => 'for_inspection','status' => 'active'],

            ['generator_name' => 'San Roque Piggery Farm',         'generator_type_id' => 5, 'brgy' => 'San Roque',
             'address' => 'San Roque, Madrid',           'contact_person' => 'Erlinda Castro',
             'contact_number' => '09282345681', 'email' => '',
             'estimated_daily_waste_kg' => 220.00, 'compliance_status' => 'non_compliant', 'status' => 'active'],

            ['generator_name' => 'Bagsac Day Care Center',         'generator_type_id' => 4, 'brgy' => 'Bagsac',
             'address' => 'Bagsac, Madrid',              'contact_person' => 'Teresita Gomez',
             'contact_number' => '09293456792', 'email' => '',
             'estimated_daily_waste_kg' =>   8.00, 'compliance_status' => 'compliant',     'status' => 'active'],

            ['generator_name' => 'Bayogo Sari-Sari Stores Cluster','generator_type_id' => 2, 'brgy' => 'Bayogo',
             'address' => 'Bayogo, Madrid',              'contact_person' => 'Corazon Dela Cruz',
             'contact_number' => '09204567803', 'email' => '',
             'estimated_daily_waste_kg' =>  55.00, 'compliance_status' => 'for_inspection','status' => 'active'],

            ['generator_name' => 'Madrid District Hospital',       'generator_type_id' => 4, 'brgy' => 'Magsaysay',
             'address' => 'Magsaysay, Madrid',           'contact_person' => 'Dr. Ramon Aquino',
             'contact_number' => '09215678914', 'email' => 'hospital@doh.gov.ph',
             'estimated_daily_waste_kg' =>  90.00, 'compliance_status' => 'compliant',     'status' => 'active'],

            ['generator_name' => 'Madrid Abattoir',                'generator_type_id' => 3, 'brgy' => 'Magsaysay',
             'address' => 'Magsaysay, Madrid',           'contact_person' => 'Rodrigo Espiritu',
             'contact_number' => '09226789025', 'email' => '',
             'estimated_daily_waste_kg' => 130.00, 'compliance_status' => 'for_inspection','status' => 'active'],

            ['generator_name' => 'San Vicente Beach Resort',       'generator_type_id' => 2, 'brgy' => 'San Vicente',
             'address' => 'San Vicente, Madrid',         'contact_person' => 'Cristina Morales',
             'contact_number' => '09237890136', 'email' => 'resort@sanvicente.ph',
             'estimated_daily_waste_kg' =>  65.00, 'compliance_status' => 'compliant',     'status' => 'active'],

            ['generator_name' => 'San Juan Fishing Port',          'generator_type_id' => 2, 'brgy' => 'San Juan',
             'address' => 'San Juan, Madrid',            'contact_person' => 'Abelardo Jimenez',
             'contact_number' => '09248901247', 'email' => '',
             'estimated_daily_waste_kg' => 175.00, 'compliance_status' => 'non_compliant', 'status' => 'active'],

            ['generator_name' => 'Magsaysay Hardware & Lumber',    'generator_type_id' => 2, 'brgy' => 'Magsaysay',
             'address' => 'Magsaysay, Madrid',           'contact_person' => 'Hernando Velarde',
             'contact_number' => '09259012358', 'email' => '',
             'estimated_daily_waste_kg' =>  50.00, 'compliance_status' => 'compliant',     'status' => 'active'],

            ['generator_name' => 'Bagsac Farm Cooperative',        'generator_type_id' => 5, 'brgy' => 'Bagsac',
             'address' => 'Bagsac, Madrid',              'contact_person' => 'Conchita Peralta',
             'contact_number' => '09260123469', 'email' => '',
             'estimated_daily_waste_kg' => 140.00, 'compliance_status' => 'compliant',     'status' => 'active'],

            ['generator_name' => 'Madrid Cockpit Arena',           'generator_type_id' => 2, 'brgy' => 'Songkit',
             'address' => 'Songkit, Madrid',             'contact_person' => 'Dionisio Aguilar',
             'contact_number' => '09271234580', 'email' => '',
             'estimated_daily_waste_kg' =>  70.00, 'compliance_status' => 'for_inspection','status' => 'inactive'],
        ];

        $now = now();
        $generatorRows = [];
        foreach ($generators as $g) {
            $bid = $brgys[$g['brgy']] ?? null;
            if (!$bid) continue;
            $generatorRows[] = [
                'generator_name'           => $g['generator_name'],
                'generator_type_id'        => $g['generator_type_id'],
                'barangay_id'              => $bid,
                'address'                  => $g['address'],
                'contact_person'           => $g['contact_person'],
                'contact_number'           => $g['contact_number'],
                'email'                    => $g['email'],
                'estimated_daily_waste_kg' => $g['estimated_daily_waste_kg'],
                'compliance_status'        => $g['compliance_status'],
                'status'                   => $g['status'],
                'created_by'               => $adminId,
                'created_at'               => $now->copy()->subDays(rand(60, 365)),
                'updated_at'               => $now,
            ];
        }
        DB::table('waste_generators')->insert($generatorRows);

        $generatorIds = DB::table('waste_generators')->pluck('generator_id')->toArray();

        // ─── Waste Entries (12 months of data) ───────────────────────────────
        // Categories: 1=Biodegradable, 2=Recyclable, 3=Residual, 4=Special/Hazardous, 5=E-Waste, 6=Construction
        $entries = [];
        $startDate = Carbon::now()->subMonths(12)->startOfMonth();

        foreach ($generatorIds as $gid) {
            $entriesPerMonth = rand(2, 5);
            for ($m = 0; $m < 12; $m++) {
                $monthDate = $startDate->copy()->addMonths($m);
                for ($e = 0; $e < $entriesPerMonth; $e++) {
                    $dayOffset = rand(0, 27);
                    $entries[] = [
                        'generator_id' => $gid,
                        'category_id'  => rand(1, 3),
                        'quantity'     => round(rand(50, 500) + (rand(0, 99) / 100), 2),
                        'unit'         => 'kg',
                        'entry_date'   => $monthDate->copy()->addDays($dayOffset)->toDateString(),
                        'remarks'      => null,
                        'encoded_by'   => $encoderId,
                        'created_at'   => $monthDate->copy()->addDays($dayOffset)->addHours(rand(8, 17)),
                        'updated_at'   => $monthDate->copy()->addDays($dayOffset)->addHours(rand(8, 17)),
                    ];
                }
            }
        }

        // Add some special/hazardous entries for hospital and processing plants
        $hospitalId = DB::table('waste_generators')->where('generator_name', 'Madrid District Hospital')->value('generator_id');
        $riceMillId = DB::table('waste_generators')->where('generator_name', 'Magsaysay Rice Mill')->value('generator_id');

        for ($m = 0; $m < 12; $m++) {
            $monthDate = $startDate->copy()->addMonths($m);
            $entries[] = [
                'generator_id' => $hospitalId,
                'category_id'  => 4,
                'quantity'     => round(rand(15, 45) + (rand(0, 99) / 100), 2),
                'unit'         => 'kg',
                'entry_date'   => $monthDate->copy()->addDays(rand(1, 28))->toDateString(),
                'remarks'      => 'Medical waste — properly segregated and sealed',
                'encoded_by'   => $encoderId,
                'created_at'   => $monthDate->copy()->addDays(rand(1, 28))->addHours(9),
                'updated_at'   => $monthDate->copy()->addDays(rand(1, 28))->addHours(9),
            ];
            $entries[] = [
                'generator_id' => $riceMillId,
                'category_id'  => 6,
                'quantity'     => round(rand(200, 600) + (rand(0, 99) / 100), 2),
                'unit'         => 'kg',
                'entry_date'   => $monthDate->copy()->addDays(rand(1, 28))->toDateString(),
                'remarks'      => 'Rice hull and processing residue',
                'encoded_by'   => $encoderId,
                'created_at'   => $monthDate->copy()->addDays(rand(1, 28))->addHours(10),
                'updated_at'   => $monthDate->copy()->addDays(rand(1, 28))->addHours(10),
            ];
        }

        foreach (array_chunk($entries, 200) as $chunk) {
            DB::table('waste_entries')->insert($chunk);
        }

        // ─── Inspections ─────────────────────────────────────────────────────
        $inspectionData = [
            ['gid_name' => 'Madrid Public Market',            'status' => 'compliant',    'score' => 88, 'days_ago' => 30],
            ['gid_name' => 'Madrid Municipal Hall',            'status' => 'compliant',    'score' => 95, 'days_ago' => 45],
            ['gid_name' => 'Madrid Central Elementary School', 'status' => 'compliant',    'score' => 91, 'days_ago' => 20],
            ['gid_name' => 'St. Joseph Parish Church',         'status' => 'compliant',    'score' => 92, 'days_ago' => 60],
            ['gid_name' => 'Bagsac Barangay Hall',             'status' => 'compliant',    'score' => 89, 'days_ago' => 15],
            ['gid_name' => 'Bayogo Elementary School',         'status' => 'compliant',    'score' => 93, 'days_ago' => 25],
            ['gid_name' => 'Madrid District Hospital',         'status' => 'compliant',    'score' => 97, 'days_ago' => 10],
            ['gid_name' => 'Panayogon Health Center',          'status' => 'compliant',    'score' => 94, 'days_ago' => 35],
            ['gid_name' => 'Golden Palace Restaurant',         'status' => 'warning',      'score' => 65, 'days_ago' => 14],
            ['gid_name' => 'Manga Farm Supply Store',          'status' => 'for_follow_up','score' => 58, 'days_ago' => 21],
            ['gid_name' => 'Quirino Coconut Processing Plant', 'status' => 'warning',      'score' => 60, 'days_ago' =>  7],
            ['gid_name' => 'San Antonio Fishermen\'s Village', 'status' => 'for_follow_up','score' => 62, 'days_ago' => 18],
            ['gid_name' => 'Bayogo Sari-Sari Stores Cluster',  'status' => 'warning',      'score' => 68, 'days_ago' => 12],
            ['gid_name' => 'Manga Banana Plantation',          'status' => 'for_follow_up','score' => 55, 'days_ago' =>  5],
            ['gid_name' => 'Madrid Abattoir',                  'status' => 'warning',      'score' => 63, 'days_ago' =>  9],
            ['gid_name' => 'Linibonan Tiangge Market',         'status' => 'violation',    'score' => 32, 'days_ago' =>  8],
            ['gid_name' => 'Magsaysay Rice Mill',              'status' => 'violation',    'score' => 28, 'days_ago' => 11],
            ['gid_name' => 'Patong Patong Poultry Farm',       'status' => 'violation',    'score' => 25, 'days_ago' =>  6],
            ['gid_name' => 'San Roque Piggery Farm',           'status' => 'violation',    'score' => 20, 'days_ago' => 13],
            ['gid_name' => 'San Juan Fishing Port',            'status' => 'violation',    'score' => 30, 'days_ago' =>  4],
        ];

        $inspectionIds = [];
        foreach ($inspectionData as $d) {
            $gid = DB::table('waste_generators')->where('generator_name', $d['gid_name'])->value('generator_id');
            if (!$gid) continue;

            $insDate = Carbon::now()->subDays($d['days_ago']);
            $followUp = $d['status'] === 'compliant' ? null : $insDate->copy()->addDays(30)->toDateString();

            $remarks = match($d['status']) {
                'compliant'    => 'Waste segregation practices are up to standard. Proper labeling and storage observed.',
                'warning'      => 'Minor issues found — no consistent segregation containers. Verbal warning issued.',
                'for_follow_up'=> 'Partial compliance. Some areas need improvement. Follow-up inspection scheduled.',
                'violation'    => 'Significant violations found. Open dumping and improper waste storage observed. Formal notice issued.',
                default        => 'Inspection completed.',
            };
            $recommendation = match($d['status']) {
                'compliant'    => 'Continue current waste management practices. Maintain segregation records.',
                'warning'      => 'Provide segregation bins for each waste type. Train staff on proper disposal.',
                'for_follow_up'=> 'Submit corrective action plan within 15 days. Re-inspection in 30 days.',
                'violation'    => 'Immediately cease improper disposal practices. Attend MENRO compliance seminar. Penalty notice filed.',
                default        => '',
            };

            $inspectionId = DB::table('inspections')->insertGetId([
                'generator_id'      => $gid,
                'inspection_date'   => $insDate->toDateString(),
                'inspector_id'      => $inspectorId,
                'compliance_status' => $d['status'],
                'segregation_score' => $d['score'],
                'remarks'           => $remarks,
                'recommendation'    => $recommendation,
                'next_follow_up'    => $followUp,
                'created_at'        => $insDate,
                'updated_at'        => $insDate,
            ], 'inspection_id');

            if (in_array($d['status'], ['violation', 'warning'])) {
                $inspectionIds[$d['gid_name']] = ['inspection_id' => $inspectionId, 'status' => $d['status']];
            }
        }

        // ─── Violations ───────────────────────────────────────────────────────
        $violationTemplates = [
            ['violation_type' => 'Improper Waste Segregation', 'severity' => 'medium',
             'description' => 'Biodegradable and non-biodegradable wastes were found mixed together in collection bins.'],
            ['violation_type' => 'Open Dumping', 'severity' => 'high',
             'description' => 'Waste was found openly dumped in an unauthorized area within the premises.'],
            ['violation_type' => 'Lack of Segregation Bins', 'severity' => 'low',
             'description' => 'Establishment does not have designated bins for each waste category.'],
            ['violation_type' => 'Improper Storage of Hazardous Waste', 'severity' => 'critical',
             'description' => 'Hazardous waste materials were stored in open containers without proper labels or protection.'],
            ['violation_type' => 'Burning of Waste', 'severity' => 'high',
             'description' => 'Evidence of open burning of solid waste found within the establishment grounds.'],
            ['violation_type' => 'No Waste Management Plan', 'severity' => 'medium',
             'description' => 'Establishment does not have a documented waste management and disposal plan.'],
        ];

        foreach ($inspectionIds as $name => $data) {
            $isViolation = $data['status'] === 'violation';
            $count = $isViolation ? rand(2, 3) : 1;
            $shuffled = $violationTemplates;
            shuffle($shuffled);

            for ($v = 0; $v < $count; $v++) {
                $template = $shuffled[$v];
                $resStatus = $isViolation ? 'open' : 'in_progress';
                $penalty   = $isViolation ? 'penalty_pending' : 'warning_issued';

                DB::table('violations')->insert([
                    'inspection_id'     => $data['inspection_id'],
                    'violation_type'    => $template['violation_type'],
                    'description'       => $template['description'],
                    'severity'          => $template['severity'],
                    'penalty_status'    => $penalty,
                    'resolution_status' => $resStatus,
                    'resolved_date'     => null,
                    'created_at'        => now()->subDays(rand(1, 15)),
                    'updated_at'        => now(),
                ]);
            }
        }

        // Add some resolved violations for history
        $anyInspection = DB::table('inspections')->first();
        if ($anyInspection) {
            DB::table('violations')->insert([
                'inspection_id'     => $anyInspection->inspection_id,
                'violation_type'    => 'Clogged Drainage from Waste Runoff',
                'description'       => 'Waste disposal causing drainage blockage — resolved after cleanup.',
                'severity'          => 'medium',
                'penalty_status'    => 'penalty_applied',
                'resolution_status' => 'resolved',
                'resolved_date'     => Carbon::now()->subDays(20)->toDateString(),
                'created_at'        => Carbon::now()->subDays(45),
                'updated_at'        => Carbon::now()->subDays(20),
            ]);
        }

        // ─── Collection Schedules ─────────────────────────────────────────────
        $barangayIds = DB::table('barangays')->pluck('barangay_id')->toArray();
        $wasteTypes  = ['Mixed Solid Waste', 'Biodegradable', 'Recyclable Materials', 'Residual Waste'];
        $teams       = ['Team Alpha', 'Team Bravo', 'Team Charlie'];
        $vehicles    = ['Dump Truck DT-01', 'Garbage Truck GT-02', 'Mini Truck MT-03'];

        $schedules = [];
        // Past completed schedules (past 3 months)
        for ($week = 0; $week < 12; $week++) {
            $date = Carbon::now()->subWeeks($week + 1)->startOfWeek()->addDays(1); // Tuesdays
            foreach (array_slice($barangayIds, 0, 12) as $bid) {
                $schedules[] = [
                    'barangay_id'      => $bid,
                    'collection_date'  => $date->toDateString(),
                    'waste_type'       => $wasteTypes[array_rand($wasteTypes)],
                    'assigned_team'    => $teams[array_rand($teams)],
                    'assigned_vehicle' => $vehicles[array_rand($vehicles)],
                    'status'           => rand(0, 10) > 1 ? 'completed' : 'missed',
                    'notes'            => null,
                    'created_by'       => $menroId,
                    'created_at'       => $date->copy()->subDays(7),
                    'updated_at'       => $date->copy()->addDays(1),
                ];
            }
        }

        // Upcoming/pending schedules (next 2 months)
        for ($week = 0; $week < 8; $week++) {
            $date = Carbon::now()->addWeeks($week)->next(Carbon::TUESDAY);
            foreach ($barangayIds as $bid) {
                $schedules[] = [
                    'barangay_id'      => $bid,
                    'collection_date'  => $date->toDateString(),
                    'waste_type'       => $wasteTypes[array_rand($wasteTypes)],
                    'assigned_team'    => $teams[array_rand($teams)],
                    'assigned_vehicle' => $vehicles[array_rand($vehicles)],
                    'status'           => $week === 0 ? 'confirmed' : 'pending',
                    'notes'            => null,
                    'created_by'       => $menroId,
                    'created_at'       => Carbon::now()->subDays(rand(1, 7)),
                    'updated_at'       => Carbon::now(),
                ];
            }
        }

        foreach (array_chunk($schedules, 100) as $chunk) {
            DB::table('collection_schedules')->insert($chunk);
        }

        // ─── Incidents ────────────────────────────────────────────────────────
        $incidentData = [
            ['brgy' => 'Bagsac',       'type' => 'illegal_dumping',   'status' => 'under_investigation',
             'desc' => 'Illegal dumping of construction debris found along the access road near the creek.',
             'location' => 'Near the creek, Bagsac', 'days_ago' => 3],

            ['brgy' => 'Linibonan',    'type' => 'open_burning',      'status' => 'reported',
             'desc' => 'Residents reported smoke from open burning of solid waste at the back of the market area.',
             'location' => 'Behind Linibonan Tiangge Market', 'days_ago' => 1],

            ['brgy' => 'Magsaysay',    'type' => 'improper_disposal', 'status' => 'under_investigation',
             'desc' => 'Rice mill waste water and husk discharged directly into the irrigation canal.',
             'location' => 'Irrigation canal near Magsaysay Rice Mill', 'days_ago' => 5],

            ['brgy' => 'Patong Patong','type' => 'improper_disposal', 'status' => 'reported',
             'desc' => 'Poultry farm waste not properly treated before disposal, causing foul odor complaints.',
             'location' => 'Patong Patong Poultry Farm vicinity', 'days_ago' => 2],

            ['brgy' => 'San Juan',     'type' => 'illegal_dumping',   'status' => 'for_validation',
             'desc' => 'Suspected midnight dumping of fish processing waste near the fishing port access road.',
             'location' => 'San Juan Fishing Port access road', 'days_ago' => 4],

            ['brgy' => 'Magsaysay',    'type' => 'other',             'status' => 'resolved',
             'desc' => 'Overflowing public market waste bins causing spillover onto pedestrian walkway.',
             'location' => 'Madrid Public Market, Magsaysay', 'days_ago' => 14],

            ['brgy' => 'San Roque',    'type' => 'open_burning',      'status' => 'resolved',
             'desc' => 'Piggery farm burning animal waste and plastic materials together.',
             'location' => 'San Roque, behind the piggery compound', 'days_ago' => 21],

            ['brgy' => 'Quirino',      'type' => 'improper_disposal', 'status' => 'under_investigation',
             'desc' => 'Coconut husk and processing liquid waste found dumped in the nearby ravine.',
             'location' => 'Ravine at the north side of Quirino Processing Plant', 'days_ago' => 7],

            ['brgy' => 'San Vicente',  'type' => 'illegal_dumping',   'status' => 'reported',
             'desc' => 'Tourist area littering issue — overnight visitors leaving waste on the beach.',
             'location' => 'San Vicente Beach shoreline', 'days_ago' => 1],

            ['brgy' => 'Manga',        'type' => 'improper_disposal', 'status' => 'for_validation',
             'desc' => 'Agricultural chemicals containers improperly disposed near water source.',
             'location' => 'Manga Banana Plantation, east boundary', 'days_ago' => 6],
        ];

        foreach ($incidentData as $d) {
            $bid = $brgys[$d['brgy']] ?? null;
            if (!$bid) continue;
            $isResolved = $d['status'] === 'resolved';
            DB::table('incidents')->insert([
                'barangay_id'      => $bid,
                'reported_by'      => $inspectorId,
                'incident_type'    => $d['type'],
                'description'      => $d['desc'],
                'location_details' => $d['location'],
                'date_reported'    => Carbon::now()->subDays($d['days_ago'])->toDateString(),
                'status'           => $d['status'],
                'assigned_to'      => $menroId,
                'resolution_notes' => $isResolved ? 'Site cleaned up. Generator notified and warned. Monitor for repeat occurrence.' : null,
                'resolved_at'      => $isResolved ? Carbon::now()->subDays($d['days_ago'] - 3) : null,
                'created_at'       => Carbon::now()->subDays($d['days_ago']),
                'updated_at'       => Carbon::now(),
            ]);
        }
    }
}
