<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (DB::table('roles')->count() === 0) {
            DB::table('roles')->insert([
                [
                    'role_name'   => 'System Administrator',
                    'description' => 'Full system access and administration privileges.',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'role_name'   => 'MENRO Officer',
                    'description' => 'Municipal Environment and Natural Resources Office officer with management access.',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'role_name'   => 'Data Encoder',
                    'description' => 'Responsible for encoding waste data and collection records.',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'role_name'   => 'Field Inspector',
                    'description' => 'Conducts field inspections and monitors waste management compliance.',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'role_name'   => 'Barangay User',
                    'description' => 'Barangay-level user with access to barangay-specific data.',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'role_name'   => 'Report Viewer',
                    'description' => 'Read-only access to reports and dashboards.',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
            ]);
        }
    }
}
