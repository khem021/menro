<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\User;
use App\Models\WasteGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class DecayComplianceStatus extends Command
{
    protected $signature   = 'menro:decay-compliance {--days=90 : Days without inspection before decay}';
    protected $description = 'Revert generators with no recent inspection from compliant to for_inspection.';

    public function handle(): int
    {
        $days      = (int) $this->option('days');
        $threshold = Carbon::now()->subDays($days)->toDateString();

        // Generators marked compliant but with no inspection in the past $days days
        $decayed = WasteGenerator::where('compliance_status', 'compliant')
            ->where('status', 'active')
            ->whereRaw('NOT EXISTS (
                SELECT 1 FROM inspections
                WHERE inspections.generator_id = waste_generators.generator_id
                AND inspection_date >= ?
            )', [$threshold])
            ->get(['generator_id', 'generator_name']);

        if ($decayed->isEmpty()) {
            $this->info("No generators to decay (threshold: {$days} days).");
            return 0;
        }

        $count = $decayed->count();

        WasteGenerator::whereIn('generator_id', $decayed->pluck('generator_id'))
            ->update(['compliance_status' => 'for_inspection']);

        $recipientIds = User::whereHas('role', fn($q) =>
            $q->whereIn('role_name', ['System Administrator', 'MENRO Officer'])
        )->where('status', 'active')->pluck('user_id');

        if ($recipientIds->isNotEmpty()) {
            $now  = now();
            $rows = [];

            foreach ($recipientIds as $userId) {
                $rows[] = [
                    'user_id'    => $userId,
                    'title'      => 'Compliance Status Update',
                    'message'    => "{$count} generator(s) were set to \"For Inspection\" — no inspection recorded in the past {$days} days.",
                    'type'       => 'warning',
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
        }

        Cache::forget('stats:generators');
        Cache::forget('stats:compliance_pipeline');
        Cache::forget('dashboard:kpis');

        $this->info("Decayed {$count} generator(s) to \"for_inspection\" (no inspection in {$days}+ days).");
        return 0;
    }
}
