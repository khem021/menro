<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Report;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class GenerateScheduledReport extends Command
{
    protected $signature   = 'menro:generate-report {period : daily, weekly, or monthly}';
    protected $description = 'Log report records and notify staff that scheduled reports are ready.';

    private const REPORT_TYPES = [
        'monthly_waste'       => 'Monthly Waste Report',
        'compliance_summary'  => 'Compliance Summary Report',
        'incident_summary'    => 'Incident Summary Report',
        'collection_summary'  => 'Collection Summary Report',
    ];

    public function handle(): int
    {
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
            default   => $this->exitWithError("Invalid period '{$period}'. Use daily, weekly, or monthly."),
        };

        $fromFmt = Carbon::parse($from)->format('M d, Y');
        $toFmt   = Carbon::parse($to)->format('M d, Y');
        $range   = $from === $to ? $fromFmt : "{$fromFmt} – {$toFmt}";

        // Log one Report record per type
        $now = now();
        foreach (self::REPORT_TYPES as $type => $title) {
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

        $typeCount = count(self::REPORT_TYPES);
        $rows = [];
        foreach ($recipientIds as $userId) {
            $rows[] = [
                'user_id'    => $userId,
                'title'      => "{$label} Reports Ready",
                'message'    => "{$typeCount} {$label} reports for {$range} have been logged and are ready to export from the Reports section.",
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
