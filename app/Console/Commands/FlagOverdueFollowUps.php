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

        // Only a generator's latest inspection counts, in the order InspectionForm
        // uses to decide which inspection sets the generator's status. A newer
        // inspection means the follow-up took place, so an older one's date
        // passing is not overdue.
        $generatorIds = Inspection::where('compliance_status', 'for_follow_up')
            ->whereNotNull('next_follow_up')
            ->where('next_follow_up', '<', $today)
            ->whereNotExists(function ($q) {
                $q->from('inspections as newer')
                    ->whereColumn('newer.generator_id', 'inspections.generator_id')
                    ->whereNull('newer.deleted_at')
                    ->where(function ($q) {
                        $q->whereColumn('newer.inspection_date', '>', 'inspections.inspection_date')
                            ->orWhere(function ($q) {
                                $q->whereColumn('newer.inspection_date', 'inspections.inspection_date')
                                    ->whereColumn('newer.inspection_id', '>', 'inspections.inspection_id');
                            });
                    });
            })
            ->distinct()
            ->pluck('generator_id');

        // "For follow-up" on an inspection puts its generator at for_inspection
        // (InspectionForm), so that is the status an overdue follow-up moves on to
        // non_compliant. This used to match 'for_follow_up' instead, which is an
        // inspection result and never a generator status, so nothing changed.
        // A status set by hand since then is left alone.
        $flagged = WasteGenerator::whereIn('generator_id', $generatorIds)
            ->where('status', 'active')
            ->where('compliance_status', 'for_inspection')
            ->pluck('generator_id');

        if ($flagged->isEmpty()) {
            $this->info('No overdue follow-ups found.');
            return 0;
        }

        $count = $flagged->count();
        WasteGenerator::whereIn('generator_id', $flagged)->update(['compliance_status' => 'non_compliant']);

        $recipientIds = User::whereHas('role', fn($q) =>
            $q->whereIn('role_name', ROLES_STAFF())
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

        // Kept deliberately. Elsewhere the models clear their own caches via
        // InvalidatesDataCache, but the mass update above is a query-builder
        // write, which fires no model events — so this has to be done by hand.
        Cache::forget('stats:inspections');
        Cache::forget('stats:generators');
        Cache::forget('stats:compliance_pipeline');
        Cache::forget('dashboard:kpis');

        $this->info("Marked {$count} generator(s) with overdue follow-ups as non-compliant.");
        return 0;
    }
}
