<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportController extends Controller
{
    // ── Category / status colour map ─────────────────────────────────────────
    // Each entry: [cell bg ARGB, cell text ARGB]
    private const WASTE_COLORS = [
        'biodegradable'         => ['FF0d2b12', 'FF86efac'],   // dark green / light green
        'recyclable'            => ['FF0c1e3a', 'FF93c5fd'],   // dark navy-blue / light blue
        'residual'              => ['FF2a0f0f', 'FFfca5a5'],   // dark red / light red
        'residual/non-recyclable' => ['FF2a0f0f', 'FFfca5a5'],
        'non-recyclable'        => ['FF2a0f0f', 'FFfca5a5'],
        'special'               => ['FF2a1800', 'FFfde68a'],   // dark amber / light gold
        'hazardous'             => ['FF2a1800', 'FFfde68a'],
        'special/hazardous'     => ['FF2a1800', 'FFfde68a'],
        'mixed'                 => ['FF1a1a2e', 'FFc4b5fd'],   // dark purple / lavender
        // Compliance
        'compliant'             => ['FF0d2b12', 'FF86efac'],
        'for inspection'        => ['FF2a1800', 'FFfde68a'],
        'non compliant'         => ['FF2a0f0f', 'FFfca5a5'],
        'non_compliant'         => ['FF2a0f0f', 'FFfca5a5'],
        'for_inspection'        => ['FF2a1800', 'FFfde68a'],
        // Status
        'completed'             => ['FF0d2b12', 'FF86efac'],
        'missed'                => ['FF2a0f0f', 'FFfca5a5'],
        'pending'               => ['FF1a1810', 'FFfde68a'],
        'open'                  => ['FF2a0f0f', 'FFfca5a5'],
        'resolved'              => ['FF0d2b12', 'FF86efac'],
        'closed'                => ['FF0d2b12', 'FF86efac'],
        'ongoing'               => ['FF0c1e3a', 'FF93c5fd'],
    ];

    public function printView(Request $request)
    {
        $request->validate([
            'type' => 'required|in:monthly_waste,compliance_summary,incident_summary,collection_summary',
            'from' => 'nullable|date',
            'to'   => 'nullable|date',
        ]);

        $type = $request->type;
        $from = $request->from ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $request->to   ?? now()->format('Y-m-d');

        [$headers, $rows, $title, $colorKeyCol] = $this->buildData($type, $from, $to);

        return view('reports.print', compact('headers', 'rows', 'title', 'from', 'to', 'type', 'colorKeyCol'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:monthly_waste,compliance_summary,incident_summary,collection_summary',
            'from' => 'nullable|date',
            'to'   => 'nullable|date',
        ]);

        $type = $request->type;
        $from = $request->from ?? now()->subMonth()->format('Y-m-d');
        $to   = $request->to   ?? now()->format('Y-m-d');

        [$headers, $rows, $title, $colorKeyCol] = $this->buildData($type, $from, $to);

        $filename = $type . '_' . now()->format('Ymd_His') . '.xlsx';

        try {
            Report::create([
                'report_type'  => $type,
                'generated_by' => session('auth_user_id'),
                'generated_at' => now(),
                'file_path'    => 'reports/' . $filename,
                'remarks'      => "Export: {$from} to {$to}",
            ]);
        } catch (\Exception $e) {}

        $spreadsheet = $this->buildSpreadsheet($title, $headers, $rows, $from, $to, $colorKeyCol);
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ── Build data arrays ────────────────────────────────────────────────────

    private function buildData(string $type, string $from, string $to): array
    {
        $rows = [];

        switch ($type) {
            case 'monthly_waste':
                $title      = 'Monthly Waste Report';
                $headers    = ['Date', 'Generator', 'Barangay', 'Category', 'Quantity (kg)', 'Unit', 'Encoded By'];
                $colorKeyCol = 3; // Category column (0-based)
                $data = DB::select("
                    SELECT we.entry_date, wg.generator_name, b.barangay_name,
                           wc.category_name, we.quantity, we.unit, u.full_name AS encoded_by
                    FROM waste_entries we
                    LEFT JOIN waste_generators wg ON we.generator_id = wg.generator_id
                    LEFT JOIN barangays b ON wg.barangay_id = b.barangay_id
                    LEFT JOIN waste_categories wc ON we.category_id = wc.category_id
                    LEFT JOIN users u ON we.encoded_by = u.user_id
                    WHERE we.entry_date BETWEEN ? AND ?
                    ORDER BY we.entry_date DESC
                ", [$from, $to]);
                foreach ($data as $row) {
                    $rows[] = [
                        Carbon::parse($row->entry_date)->format('M d, Y'),
                        $row->generator_name, $row->barangay_name,
                        $row->category_name, $row->quantity, $row->unit, $row->encoded_by,
                    ];
                }
                break;

            case 'compliance_summary':
                $title      = 'Compliance Summary Report';
                $headers    = ['Generator', 'Type', 'Barangay', 'Compliance Status', 'Status', 'Daily Waste (kg)'];
                $colorKeyCol = 3; // Compliance Status
                $data = DB::select("
                    SELECT wg.generator_name, gt.type_name, b.barangay_name,
                           wg.compliance_status, wg.status, wg.estimated_daily_waste_kg
                    FROM waste_generators wg
                    LEFT JOIN generator_types gt ON wg.generator_type_id = gt.generator_type_id
                    LEFT JOIN barangays b ON wg.barangay_id = b.barangay_id
                    ORDER BY wg.compliance_status, wg.generator_name
                ");
                foreach ($data as $row) {
                    $rows[] = [
                        $row->generator_name, $row->type_name, $row->barangay_name,
                        ucfirst(str_replace('_', ' ', $row->compliance_status)),
                        ucfirst($row->status), $row->estimated_daily_waste_kg,
                    ];
                }
                break;

            case 'incident_summary':
                $title      = 'Incident Summary Report';
                $headers    = ['Date Reported', 'Barangay', 'Type', 'Status', 'Assigned To', 'Description'];
                $colorKeyCol = 3; // Status
                $data = DB::select("
                    SELECT i.date_reported, b.barangay_name, i.incident_type,
                           i.status, u.full_name AS assigned_to, i.description
                    FROM incidents i
                    LEFT JOIN barangays b ON i.barangay_id = b.barangay_id
                    LEFT JOIN users u ON i.assigned_to = u.user_id
                    WHERE i.date_reported BETWEEN ? AND ?
                    ORDER BY i.date_reported DESC
                ", [$from, $to]);
                foreach ($data as $row) {
                    $rows[] = [
                        Carbon::parse($row->date_reported)->format('M d, Y'),
                        $row->barangay_name, $row->incident_type,
                        ucfirst($row->status), $row->assigned_to, $row->description,
                    ];
                }
                break;

            case 'collection_summary':
                $title      = 'Collection Summary Report';
                $headers    = ['Date', 'Barangay', 'Waste Type', 'Team', 'Vehicle', 'Status'];
                $colorKeyCol = 5; // Status
                $data = DB::select("
                    SELECT cs.collection_date, b.barangay_name, cs.waste_type,
                           cs.assigned_team, cs.assigned_vehicle, cs.status
                    FROM collection_schedules cs
                    LEFT JOIN barangays b ON cs.barangay_id = b.barangay_id
                    WHERE cs.collection_date BETWEEN ? AND ?
                    ORDER BY cs.collection_date DESC
                ", [$from, $to]);
                foreach ($data as $row) {
                    $rows[] = [
                        Carbon::parse($row->collection_date)->format('M d, Y'),
                        $row->barangay_name, $row->waste_type,
                        $row->assigned_team, $row->assigned_vehicle,
                        ucfirst($row->status),
                    ];
                }
                break;

            default:
                $title = 'Report'; $headers = []; $rows = []; $colorKeyCol = null;
        }

        return [$headers, $rows, $title, $colorKeyCol];
    }

    // ── Build styled spreadsheet ─────────────────────────────────────────────

    private function buildSpreadsheet(
        string $title, array $headers, array $rows,
        string $from, string $to, ?int $colorKeyCol
    ): Spreadsheet {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($title, 0, 31));

        $colCount = count($headers);
        $lastCol  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

        // ── Row 1: Title banner ──────────────────────────────────────────────
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', strtoupper($title));
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFDB813'], 'name' => 'Calibri'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF071020']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(36);

        // ── Row 2: Sub-title ─────────────────────────────────────────────────
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2',
            'Municipality of Madrid, Surigao del Sur  |  MENRO  |  Period: '
            . Carbon::parse($from)->format('M d, Y') . ' – ' . Carbon::parse($to)->format('M d, Y')
            . '  |  Generated: ' . now()->format('M d, Y h:i A')
        );
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['size' => 9, 'color' => ['argb' => 'FF7b8fad'], 'name' => 'Calibri'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0a1628']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // ── Row 3: Legend (only when colour-key column exists) ───────────────
        $headerRow = 3;
        if ($colorKeyCol !== null) {
            $sheet->mergeCells("A3:{$lastCol}3");
            // Build legend label
            $legendGroups = $this->getLegendForCol($colorKeyCol, $headers);
            $sheet->setCellValue('A3', $legendGroups['label']);
            $sheet->getStyle('A3')->applyFromArray([
                'font'      => ['size' => 8, 'color' => ['argb' => 'FF4a5d7a'], 'italic' => true, 'name' => 'Calibri'],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0a1628']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension(3)->setRowHeight(16);
            $headerRow = 4;
        }

        // ── Column headers ───────────────────────────────────────────────────
        foreach ($headers as $i => $label) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . $headerRow, strtoupper($label));
        }
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FFe8edf5'], 'name' => 'Calibri'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0f1d35']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FFFDB813']]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(22);

        // ── Data rows ────────────────────────────────────────────────────────
        foreach ($rows as $r => $row) {
            $excelRow = $r + $headerRow + 1;
            $isEven   = ($r % 2 === 0);
            $rowBg    = $isEven ? 'FF0f1d35' : 'FF0a1628';

            foreach ($row as $c => $value) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c + 1);
                $sheet->setCellValue($col . $excelRow, $value ?? '—');
            }

            // Base row style
            $sheet->getStyle("A{$excelRow}:{$lastCol}{$excelRow}")->applyFromArray([
                'font'      => ['size' => 9, 'color' => ['argb' => $isEven ? 'FFe8edf5' : 'FFc5cfe0'], 'name' => 'Calibri'],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $rowBg]],
                'borders'   => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF152540']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Colour-coded cell for the key column
            if ($colorKeyCol !== null && isset($row[$colorKeyCol])) {
                $keyValue  = strtolower(trim((string) $row[$colorKeyCol]));
                $colorPair = $this->resolveColor($keyValue);

                $keyColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colorKeyCol + 1);
                $sheet->getStyle($keyColLetter . $excelRow)->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => $colorPair[1]], 'name' => 'Calibri'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $colorPair[0]]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
            }

            $sheet->getRowDimension($excelRow)->setRowHeight(18);
        }

        // ── Empty state ───────────────────────────────────────────────────────
        if (empty($rows)) {
            $emptyRow = $headerRow + 1;
            $sheet->mergeCells("A{$emptyRow}:{$lastCol}{$emptyRow}");
            $sheet->setCellValue("A{$emptyRow}", 'No records found for the selected date range.');
            $sheet->getStyle("A{$emptyRow}")->applyFromArray([
                'font'      => ['size' => 9, 'color' => ['argb' => 'FF7b8fad'], 'italic' => true],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0a1628']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        // ── Auto-fit column widths ────────────────────────────────────────────
        foreach (range(1, $colCount) as $i) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ── Freeze pane & tab ────────────────────────────────────────────────
        $sheet->freezePane('A' . ($headerRow + 1));
        $sheet->getTabColor()->setARGB('FFFDB813');

        // ── Page setup ───────────────────────────────────────────────────────
        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToPage(true)
            ->setFitToWidth(1);

        $spreadsheet->getProperties()
            ->setCreator('MENRO System')
            ->setTitle($title)
            ->setCompany('Municipality of Madrid, Surigao del Sur');

        return $spreadsheet;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function resolveColor(string $key): array
    {
        // Exact match
        if (isset(self::WASTE_COLORS[$key])) {
            return self::WASTE_COLORS[$key];
        }
        // Partial match (e.g. "residual/non-recyclable" contains "residual")
        foreach (self::WASTE_COLORS as $pattern => $colors) {
            if (str_contains($key, $pattern) || str_contains($pattern, $key)) {
                return $colors;
            }
        }
        // Fallback: muted gray
        return ['FF1c2d4a', 'FF7b8fad'];
    }

    private function getLegendForCol(int $col, array $headers): array
    {
        $colName = strtolower($headers[$col] ?? '');

        if (str_contains($colName, 'category') || str_contains($colName, 'waste type')) {
            $label = 'Color Key:  🟢 Biodegradable   🔵 Recyclable   🔴 Residual / Non-Recyclable   🟡 Special / Hazardous';
        } elseif (str_contains($colName, 'compliance')) {
            $label = 'Color Key:  🟢 Compliant   🟡 For Inspection   🔴 Non-Compliant';
        } elseif (str_contains($colName, 'status')) {
            $label = 'Color Key:  🟢 Completed / Resolved   🟡 Pending / Ongoing   🔴 Missed / Open';
        } else {
            $label = '';
        }

        return ['label' => $label];
    }
}
