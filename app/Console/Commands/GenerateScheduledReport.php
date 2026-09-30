<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Report;
use App\Models\User;
use App\Services\ReportBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateScheduledReport extends Command
{
    protected $signature   = 'menro:generate-report {period : daily, weekly, monthly, or yearly}';
    protected $description = 'Archive waste-collection reports as downloadable files and log/notify staff about all scheduled reports.';

    // Waste-collection reports: archived as real, downloadable Excel + PDF files.
    private const ARCHIVE_TYPES = [
        'monthly_waste'      => 'Monthly Waste Report',
        'collection_summary' => 'Collection Summary Report',
    ];

    // Other reports: logged as before (no file, on-demand export/print stays manual).
    private const LOG_ONLY_TYPES = [
        'compliance_summary'  => 'Compliance Summary Report',
        'incident_summary'    => 'Incident Summary Report',
    ];

    public function __construct(private ReportBuilder $reportBuilder)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        // Rendering a full year of records to PDF can exceed the default 128M
        // CLI memory limit; this runs as a background job, so raise it safely
        // without affecting web request memory limits.
        ini_set('memory_limit', '512M');

        $period = $this->argument('period');

        [$from, $to, $label] = match ($period) {
            'daily'   => [
                Carbon::yesterday()->toDateString(),
                Carbon::yesterday()->toDateString(),
                'Daily',
            ],
            'weekly'  => [
                Carbon::now()->subWeek()->startOfWeek()->toDateString(),
                Carbon::now()->subWeek()->endOfWeek()->toDateString(),
                'Weekly',
            ],
            'monthly' => [
                Carbon::now()->subMonth()->startOfMonth()->toDateString(),
                Carbon::now()->subMonth()->endOfMonth()->toDateString(),
                'Monthly',
            ],
            'yearly'  => [
                Carbon::now()->subYear()->startOfYear()->toDateString(),
                Carbon::now()->subYear()->endOfYear()->toDateString(),
                'Yearly',
            ],
            default   => $this->exitWithError("Invalid period '{$period}'. Use daily, weekly, monthly, or yearly."),
        };

        $fromFmt = Carbon::parse($from)->format('M d, Y');
        $toFmt   = Carbon::parse($to)->format('M d, Y');
        $range   = $from === $to ? $fromFmt : "{$fromFmt} – {$toFmt}";

        $now = now();

        // Archive the waste-collection reports as real, downloadable files.
        foreach (array_keys(self::ARCHIVE_TYPES) as $type) {
            [$headers, $rows, $builtTitle, $colorKeyCol] = $this->reportBuilder->buildData($type, $from, $to);

            $stamp    = $now->format('Ymd_His');
            $basename = "{$type}_{$period}_{$stamp}";

            $spreadsheet = $this->reportBuilder->buildSpreadsheet($builtTitle, $headers, $rows, $from, $to, $colorKeyCol);
            $writer      = new Xlsx($spreadsheet);
            $tmpXlsx     = tempnam(sys_get_temp_dir(), 'menro_report_');
            $writer->save($tmpXlsx);
            $xlsxPath = "archives/{$type}/{$basename}.xlsx";
            Storage::disk('local')->put($xlsxPath, file_get_contents($tmpXlsx));
            @unlink($tmpXlsx);

            $pdfPath = "archives/{$type}/{$basename}.pdf";
            Storage::disk('local')->put(
                $pdfPath,
                $this->reportBuilder->buildPdf($builtTitle, $headers, $rows, $from, $to, $colorKeyCol)->output()
            );

            Report::create([
                'report_type'  => $type,
                'period'       => $period,
                'period_start' => $from,
                'period_end'   => $to,
                'generated_by' => null,
                'generated_at' => $now,
                'file_path'    => $xlsxPath,
                'pdf_path'     => $pdfPath,
                'remarks'      => "{$label} auto-archive: {$from} to {$to}",
            ]);
        }

        // Log the remaining report types as before (no file generated).
        foreach (self::LOG_ONLY_TYPES as $type => $title) {
            Report::create([
                'report_type'  => $type,
                'generated_by' => null,
                'generated_at' => $now,
                'file_path'    => null,
                'remarks'      => "{$label} auto-log: {$from} to {$to}",
            ]);
        }

        // Notify all active admins / MENRO officers
        $recipientIds = User::whereHas('role', fn($q) =>
            $q->whereIn('role_name', ['System Administrator', 'MENRO Officer'])
        )->where('status', 'active')->pluck('user_id');

        if ($recipientIds->isEmpty()) {
            $this->warn('No active staff found to notify.');
            return 0;
        }

        $typeCount = count(self::ARCHIVE_TYPES) + count(self::LOG_ONLY_TYPES);
        $rows = [];
        foreach ($recipientIds as $userId) {
            $rows[] = [
                'user_id'    => $userId,
                'title'      => "{$label} Reports Ready",
                'message'    => "{$typeCount} {$label} reports for {$range} have been logged. Waste collection reports are archived and downloadable from the Archive section.",
                'type'       => 'info',
                'is_read'    => false,
                'read_at'    => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            Notification::insert($chunk);
        }

        foreach ($recipientIds as $uid) {
            Cache::forget("nav:unread:{$uid}");
        }

        $this->info("{$label} reports logged and {$recipientIds->count()} staff notified ({$range}).");
        return 0;
    }

    private function exitWithError(string $msg): never
    {
        $this->error($msg);
        exit(1);
    }
}
