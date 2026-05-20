<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->get();

        $notifications = [];
        foreach ($users as $user) {
            $notifications[] = [
                'user_id'    => $user->user_id,
                'title'      => 'Welcome to MENRO System',
                'message'    => 'Your account has been set up successfully. You can now access all assigned waste management modules.',
                'type'       => 'success',
                'is_read'    => false,
                'read_at'    => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $notifications[] = [
                'user_id'    => $user->user_id,
                'title'      => 'System Ready',
                'message'    => 'The MENRO Waste Management System is now fully operational. All modules are available for use.',
                'type'       => 'info',
                'is_read'    => false,
                'read_at'    => null,
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ];
            $notifications[] = [
                'user_id'    => $user->user_id,
                'title'      => 'Reminder: Monthly Report Due',
                'message'    => 'Please ensure all waste entries for this month are recorded before the end of the month.',
                'type'       => 'warning',
                'is_read'    => false,
                'read_at'    => null,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ];
        }

        DB::table('notifications')->insert($notifications);
    }
}
