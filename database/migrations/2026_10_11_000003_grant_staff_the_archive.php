<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrects the MENRO Staff role description, which still told users the archive
 * was out of reach. The archive moved to the staff routes in the same release;
 * only user management and the audit trail remain administrator-only.
 *
 * RoleSeeder writes the same text, but it only runs on a seed, so the live row
 * needs this. The 2026_10_11_000001 consolidation migration is left alone: it is
 * shipped history, and rewriting it would not change an already-migrated
 * database anyway.
 */
return new class extends Migration
{
    private const NEW_DESCRIPTION =
        'MENRO staff. Full operational access: generators, waste entries, collections, '
      . 'inspections, violations, tickets, incidents, analytics reports and the archive. '
      . 'Not user management or the audit trail.';

    private const OLD_DESCRIPTION =
        'MENRO staff. Full operational access: generators, waste entries, collections, '
      . 'inspections, violations, tickets, incidents, analytics and reports. Not user '
      . 'management, the audit trail or the archive.';

    public function up(): void
    {
        $this->describe(self::NEW_DESCRIPTION);
    }

    public function down(): void
    {
        $this->describe(self::OLD_DESCRIPTION);
    }

    private function describe(string $description): void
    {
        DB::table('roles')
            ->where('role_name', 'MENRO Staff')
            ->update(['description' => $description, 'updated_at' => now()]);
    }
};
