<?php

namespace App\Console\Commands;

use App\Models\Inspection;
use App\Models\Notification;
use App\Models\User;
use App\Models\WasteGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class FlagOverdueFollowUps extends Command
{
    protected $signature   = 'menro:flag-overdue-followups';
    protected $description = 'Flag generators with overdue follow-up inspections and notify staff.';

    public function handle(): int
    {
        $today = Carbon::today()->toDateString();

        $overdue = Inspection::with('wasteGenerator:generator_id,generator_name')
            ->where('compliance_status', 'for_follow_up')
            ->whereNotNull('next_follow_up')
            ->where('next_follow_up', '<', $today)
            ->get(['inspection_id', 'generator_id', 'next_follow_up']);

        if ($overdue->isEmpty()) {
            $this->info('No overdue follow-ups found.');
            return 0;
        }

        $count        = $overdue->count();
        $generatorIds = $overdue->pluck('generator_id')->unique()->values()->all();

        // Revert generator compliance to non_compliant
        WasteGenerator::whereIn('generator_id', $generatorIds)
            ->where('compliance_status', 'for_follow_up')
            ->update(['compliance_status' => 'non_compliant']);

        $recipientIds = User::whereHas('role', fn($q) =>
            $q->whereIn('role_name', ['System Administrator', 'MENRO Officer'])
        )->where('status', 'active')->pluck('user_id');

        if ($recipientIds->isNotEmpty()) {
            $now  = now();
            $rows = [];

            foreach ($recipientIds as $userId) {
                $rows[] = [
                    'user_id'    => $userId,
                    'title'      => 'Overdue Follow-Up Alert',
                    'message'    => "{$count} generator(s) have overdue follow-up inspections and were marked non-compliant.",
                    'type'       => 'danger',
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

        Cache::forget('stats:inspections');
        Cache::forget('stats:generators');
        Cache::forget('stats:compliance_pipeline');
        Cache::forget('dashboard:kpis');

        $this->info("Flagged {$count} overdue follow-up(s) as non-compliant.");
        return 0;
    }
}
