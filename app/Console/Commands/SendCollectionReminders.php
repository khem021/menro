<?php

namespace App\Console\Commands;

use App\Models\CollectionSchedule;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class SendCollectionReminders extends Command
{
    protected $signature   = 'menro:send-collection-reminders';
    protected $description = 'Send next-day collection reminders to staff.';

    public function handle(): int
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $schedules = CollectionSchedule::with('barangay:barangay_id,barangay_name')
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('collection_date', $tomorrow)
            ->get(['schedule_id', 'barangay_id', 'waste_type', 'assigned_team']);

        if ($schedules->isEmpty()) {
            $this->info('No collections scheduled for tomorrow.');
            return 0;
        }

        $recipientIds = User::whereHas('role', fn($q) =>
            $q->whereIn('role_name', ['System Administrator', 'MENRO Officer'])
        )->where('status', 'active')->pluck('user_id');

        if ($recipientIds->isEmpty()) {
            $this->warn('No active staff found to notify.');
            return 0;
        }

        $now  = now();
        $rows = [];

        foreach ($schedules as $schedule) {
            $barangay  = $schedule->barangay?->barangay_name ?? "Barangay #{$schedule->barangay_id}";
            $wasteType = $schedule->waste_type ? " ({$schedule->waste_type})" : '';
            $team      = $schedule->assigned_team ? " — Team: {$schedule->assigned_team}" : '';

            foreach ($recipientIds as $userId) {
                $rows[] = [
                    'user_id'    => $userId,
                    'title'      => 'Collection Reminder',
                    'message'    => "Collection tomorrow in {$barangay}{$wasteType}{$team}.",
                    'type'       => 'info',
                    'is_read'    => false,
                    'read_at'    => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            Notification::insert($chunk);
        }

        foreach ($recipientIds as $uid) {
            Cache::forget("nav:unread:{$uid}");
        }

        $this->info("Sent reminders for {$schedules->count()} collection(s) scheduled tomorrow.");
        return 0;
    }
}
