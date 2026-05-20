<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Flag overdue follow-ups at 6 AM daily — marks non-compliant & notifies staff
        $schedule->command('menro:flag-overdue-followups')
            ->dailyAt('06:00')
            ->withoutOverlapping();

        // Send collection reminders at 4 PM daily — alerts staff about tomorrow's collections
        $schedule->command('menro:send-collection-reminders')
            ->dailyAt('16:00')
            ->withoutOverlapping();

        // Decay stale compliant generators to for_inspection at 7 AM daily
        $schedule->command('menro:decay-compliance')
            ->dailyAt('07:00')
            ->withoutOverlapping();

        // Auto-log daily report records and notify staff at 8 AM (covers yesterday)
        $schedule->command('menro:generate-report daily')
            ->dailyAt('08:00')
            ->withoutOverlapping();

        // Auto-log weekly report records and notify staff at 8:30 AM every Monday (covers last week)
        $schedule->command('menro:generate-report weekly')
            ->weeklyOn(1, '08:30')
            ->withoutOverlapping();

        // Auto-log monthly report records and notify staff at 9 AM on the 1st (covers last month)
        $schedule->command('menro:generate-report monthly')
            ->monthlyOn(1, '09:00')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
