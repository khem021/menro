<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * The two roles the system recognises.
     *
     * Everything else lives in the 2026_10_11 consolidation migration, which
     * retired Data Encoder, Field Inspector, Barangay User and Report Viewer and
     * renamed MENRO Officer to MENRO Staff.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'role_name'   => 'System Administrator',
                'description' => 'Full system access and administration privileges.',
            ],
            [
                'role_name'   => 'MENRO Staff',
                'description' => 'MENRO staff. Full operational access: generators, waste entries, '
                               . 'collections, inspections, violations, tickets, incidents, analytics '
                               . 'reports and the archive. Not user management or the audit trail.',
            ],
        ];

        // Keyed on role_name (the UNIQUE column) rather than guarded by
        // `count() === 0`. The old guard made this seeder a no-op on every
        // database that already had rows, so it could never add or correct
        // anything after the initial install.
        //
        // This never deletes: users.role_id is ON DELETE RESTRICT, and retiring a
        // role is a migration's job — it has to reassign the affected users first.
        foreach ($roles as $role) {
            $existing = DB::table('roles')->where('role_name', $role['role_name']);

            if ($existing->exists()) {
                $existing->update([
                    'description' => $role['description'],
                    'updated_at'  => now(),
                ]);
            } else {
                DB::table('roles')->insert($role + [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
