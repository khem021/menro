<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (DB::table('users')->count() === 0) {
            // Fetch all roles keyed by role_name for easy lookup
            $roles = DB::table('roles')->pluck('role_id', 'role_name');

            $users = [
                [
                    'username'      => 'admin',
                    'full_name'     => 'System Admin',
                    'email'         => 'admin@menro.gov.ph',
                    'role_name'     => 'System Administrator',
                    'status'        => 'active',
                ],
                [
                    'username'      => 'menro',
                    'full_name'     => 'MENRO Officer',
                    'email'         => 'menro@menro.gov.ph',
                    'role_name'     => 'MENRO Officer',
                    'status'        => 'active',
                ],
                [
                    'username'      => 'encoder',
                    'full_name'     => 'Data Encoder',
                    'email'         => 'encoder@menro.gov.ph',
                    'role_name'     => 'Data Encoder',
                    'status'        => 'active',
                ],
                [
                    'username'      => 'inspector',
                    'full_name'     => 'Field Inspector',
                    'email'         => 'inspector@menro.gov.ph',
                    'role_name'     => 'Field Inspector',
                    'status'        => 'active',
                ],
                [
                    'username'      => 'barangay',
                    'full_name'     => 'Barangay User',
                    'email'         => 'barangay@menro.gov.ph',
                    'role_name'     => 'Barangay User',
                    'status'        => 'active',
                ],
                [
                    'username'      => 'viewer',
                    'full_name'     => 'Report Viewer',
                    'email'         => 'viewer@menro.gov.ph',
                    'role_name'     => 'Report Viewer',
                    'status'        => 'active',
                ],
            ];

            $rows = [];
            foreach ($users as $user) {
                $rows[] = [
                    'username'      => $user['username'],
                    'full_name'     => $user['full_name'],
                    'email'         => $user['email'],
                    'password_hash' => bcrypt('admin123'),
                    'role_id'       => $roles[$user['role_name']],
                    'status'        => $user['status'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            DB::table('users')->insert($rows);
        }
    }
}
