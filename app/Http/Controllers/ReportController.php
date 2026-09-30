<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\ReportBuilder;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function __construct(private ReportBuilder $reportBuilder)
    {
    }

    public function printView(Request $request)
    {
        if (!canAccess('System Administrator', 'MENRO Officer')) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'type' => 'required|in:monthly_waste,compliance_summary,incident_summary,collection_summary',
            'from' => 'nullable|date|before_or_equal:to',
            'to'   => 'nullable|date|after_or_equal:from',
        ]);

        $type = $request->type;
        $from = $request->from ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $request->to   ?? now()->format('Y-m-d');

        [$headers, $rows, $title, $colorKeyCol] = $this->reportBuilder->buildData($type, $from, $to);

        return view('reports.print', compact('headers', 'rows', 'title', 'from', 'to', 'type', 'colorKeyCol'));
    }

    public function export(Request $request)
    {
        if (!canAccess('System Administrator', 'MENRO Officer')) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'type' => 'required|in:monthly_waste,compliance_summary,incident_summary,collection_summary',
            'from' => 'nullable|date|before_or_equal:to',
            'to'   => 'nullable|date|after_or_equal:from',
        ]);

        $type = $request->type;
        $from = $request->from ?? now()->subMonth()->format('Y-m-d');
        $to   = $request->to   ?? now()->format('Y-m-d');

        [$headers, $rows, $title, $colorKeyCol] = $this->reportBuilder->buildData($type, $from, $to);

        $filename = $type . '_' . now()->format('Ymd_His') . '.xlsx';

        try {
            Report::create([
                'report_type'  => $type,
                'generated_by' => session('auth_user_id'),
                'generated_at' => now(),
                'file_path'    => 'reports/' . $filename,
                'remarks'      => "Export: {$from} to {$to}",
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Report audit record failed: ' . $e->getMessage());
        }

        $spreadsheet = $this->reportBuilder->buildSpreadsheet($title, $headers, $rows, $from, $to, $colorKeyCol);
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
