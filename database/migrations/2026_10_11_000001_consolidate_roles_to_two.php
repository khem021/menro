<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Consolidates the six roles down to two: System Administrator and MENRO Staff.
 *
 * The ordering is forced by the schema. users.role_id is
 * `foreignId->constrained('roles','role_id')->onDelete('restrict')` and NOT
 * NULL, so a role row cannot be deleted while any user still points at it:
 *
 *   1. rename MENRO Officer -> MENRO Staff  (in place, so users.role_id stays valid)
 *   2. move every user off the four retired roles onto MENRO Staff
 *   3. only then delete the four rows
 *
 * The rename is an UPDATE on the existing row rather than an insert-and-migrate,
 * which means nobody's role_id changes and AuthMiddleware — which re-reads
 * role_name into session('auth_role') on every request — hands live sessions the
 * new name without a forced sign-out.
 */
return new class extends Migration
{
    private const STAFF   = 'MENRO Staff';
    private const OFFICER = 'MENRO Officer';

    private const RETIRED = ['Data Encoder', 'Field Inspector', 'Barangay User', 'Report Viewer'];

    private const STAFF_DESCRIPTION =
        'MENRO staff. Full operational access: generators, waste entries, collections, '
      . 'inspections, violations, tickets, incidents, analytics and reports. Not user '
      . 'management, the audit trail or the archive.';

    private const OFFICER_DESCRIPTION =
        'Municipal Environment and Natural Resources Office officer with management access.';

    public function up(): void
    {
        // A fresh database is RoleSeeder's job, not this migration's: `migrate`
        // runs before `db:seed`, so creating MENRO Staff here would leave System
        // Administrator to RoleSeeder alone and the two rows would be written by
        // two different owners. Let the seeder populate an empty table.
        if (DB::table('roles')->count() === 0) {
            return;
        }

        DB::transaction(function () {
            // ── 1. The surviving row, under its new name ──────────────────────
            $staffId = DB::table('roles')->where('role_name', self::STAFF)->value('role_id');

            if (! $staffId) {
                $officerId = DB::table('roles')->where('role_name', self::OFFICER)->value('role_id');

                if ($officerId) {
                    // role_name is UNIQUE(50) and 'MENRO Staff' is 11 characters,
                    // so this rename can neither overflow nor collide — the branch
                    // above already established no MENRO Staff row exists.
                    DB::table('roles')->where('role_id', $officerId)->update([
                        'role_name'   => self::STAFF,
                        'description' => self::STAFF_DESCRIPTION,
                        'updated_at'  => now(),
                    ]);

                    $staffId = $officerId;
                } else {
                    // Neither name present on a non-empty table: a hand-edited
                    // database. Create the row so step 2 has somewhere to land.
                    $staffId = DB::table('roles')->insertGetId([
                        'role_name'   => self::STAFF,
                        'description' => self::STAFF_DESCRIPTION,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ], 'role_id');
                }
            }

            // ── 2. Reassign, then 3. delete. Never the other way round. ───────
            $retiredIds = DB::table('roles')->whereIn('role_name', self::RETIRED)->pluck('role_id');

            if ($retiredIds->isNotEmpty()) {
                DB::table('users')
                    ->whereIn('role_id', $retiredIds)
                    ->update(['role_id' => $staffId, 'updated_at' => now()]);

                DB::table('roles')->whereIn('role_id', $retiredIds)->delete();
            }
        });

        $this->forgetRoleCaches();
    }

    /**
     * Structurally reversible, not data-reversible.
     *
     * The four role rows come back (with fresh role_ids) and MENRO Staff goes
     * back to being called MENRO Officer, which is enough for pre-consolidation
     * code to run: the route middleware strings resolve, the dropdowns fill and
     * canAccess() behaves. What cannot come back is who held which role — up()
     * overwrote the role_id of every reassigned user, so a rollback leaves each
     * former encoder, inspector, barangay user and report viewer sitting on
     * MENRO Officer, i.e. with more access than they started with.
     *
     * For a true revert, restore the pre-migration dump of `roles` and `users`
     * instead (pg_dump -t roles -t users, taken before this migration ran).
     */
    public function down(): void
    {
        if (DB::table('roles')->count() === 0) {
            return;
        }

        DB::transaction(function () {
            // Only rename back if the old name is free: role_name is UNIQUE(50).
            if (! DB::table('roles')->where('role_name', self::OFFICER)->exists()) {
                DB::table('roles')->where('role_name', self::STAFF)->update([
                    'role_name'   => self::OFFICER,
                    'description' => self::OFFICER_DESCRIPTION,
                    'updated_at'  => now(),
                ]);
            }

            // insertOrIgnore, not insert: on Postgres this is ON CONFLICT DO
            // NOTHING against the unique index on role_name, so down() is safe to
            // re-run and safe on a database that still holds some of the rows.
            DB::table('roles')->insertOrIgnore([
                ['role_name' => 'Data Encoder',    'description' => 'Responsible for encoding waste data and collection records.',         'created_at' => now(), 'updated_at' => now()],
                ['role_name' => 'Field Inspector', 'description' => 'Conducts field inspections and monitors waste management compliance.', 'created_at' => now(), 'updated_at' => now()],
                ['role_name' => 'Barangay User',   'description' => 'Barangay-level user with access to barangay-specific data.',           'created_at' => now(), 'updated_at' => now()],
                ['role_name' => 'Report Viewer',   'description' => 'Read-only access to reports and dashboards.',                          'created_at' => now(), 'updated_at' => now()],
            ]);
        });

        $this->forgetRoleCaches();
    }

    /**
     * The role dropdowns and the inspector picker cache for 600 seconds, so
     * without this the Add User form keeps offering the retired roles and
     * "notify by role" keeps offering names that now match nobody.
     */
    private function forgetRoleCaches(): void
    {
        foreach (['users:roles', 'lookup:roles', 'lookup:inspectors'] as $key) {
            try {
                Cache::forget($key);
            } catch (\Throwable $e) {
                // The cache store can be unreachable mid-deploy; `cache:clear`
                // in the deploy script is the backstop.
            }
        }
    }
};
