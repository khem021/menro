<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Six accounts across the two roles. The five staff accounts kept their
     * original usernames through the role consolidation — the test suite resolves
     * users by those exact names, and several rules are per-user rather than
     * per-role (you may only edit the waste entries you encoded), which needs
     * more than one non-admin account to exercise.
     *
     * @return void
     */
    public function run()
    {
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
                    'full_name'     => 'MENRO Staff',
                    'email'         => 'menro@menro.gov.ph',
                    'role_name'     => 'MENRO Staff',
                    'status'        => 'active',
                ],
                [
                    'username'      => 'encoder',
                    'full_name'     => 'Waste Data Staff',
                    'email'         => 'encoder@menro.gov.ph',
                    'role_name'     => 'MENRO Staff',
                    'status'        => 'active',
                ],
                [
                    'username'      => 'inspector',
                    'full_name'     => 'Field Staff',
                    'email'         => 'inspector@menro.gov.ph',
                    'role_name'     => 'MENRO Staff',
                    'status'        => 'active',
                ],
                [
                    'username'      => 'barangay',
                    'full_name'     => 'Barangay Liaison',
                    'email'         => 'barangay@menro.gov.ph',
                    'role_name'     => 'MENRO Staff',
                    'status'        => 'active',
                ],
                [
                    'username'      => 'viewer',
                    'full_name'     => 'Reports Staff',
                    'email'         => 'viewer@menro.gov.ph',
                    'role_name'     => 'MENRO Staff',
                    'status'        => 'active',
                ],
            ];

        // Create any user that is missing; never touch an existing one
        // (so passwords changed in production are preserved).
        foreach ($users as $user) {
            if (DB::table('users')->where('username', $user['username'])->exists()) {
                continue;
            }

            // users.role_id is NOT NULL, so a missing role would otherwise
            // surface as an opaque Postgres constraint violation.
            $roleId = $roles[$user['role_name']] ?? null;

            if (! $roleId) {
                throw new \RuntimeException(
                    "UserSeeder: role '{$user['role_name']}' is missing. Run RoleSeeder first."
                );
            }

            DB::table('users')->insert([
                'username'      => $user['username'],
                'full_name'     => $user['full_name'],
                'email'         => $user['email'],
                'password_hash' => bcrypt('admin123'),
                'role_id'       => $roleId,
                'status'        => $user['status'],
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }
}
