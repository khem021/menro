<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repairs the clusters id sequence on PostgreSQL.
 *
 * Clusters 1-3 are written with explicit ids: by the create_clusters_table
 * migration when barangays already carry cluster numbers, and otherwise by
 * FreshDemoSeeder. Writing an explicit id does not advance Postgres's identity
 * sequence, so on a database seeded in that order the sequence was still at 1
 * and the next "Add Cluster" tried to reuse id 1 — a duplicate key violation
 * that surfaced as a 500 on the clusters page.
 *
 * Safe to run on an already-correct database: setval just rewrites the sequence
 * to match the table, and on MySQL this is a no-op.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql' || ! Schema::hasTable('clusters')) {
            return;
        }

        // Third argument is is_called: true means nextval returns value + 1, so
        // a populated table continues after MAX(id). An empty table passes false
        // with value 1, so the first insert still gets id 1.
        DB::statement(<<<'SQL'
            SELECT setval(
                pg_get_serial_sequence('clusters', 'id'),
                GREATEST(COALESCE((SELECT MAX(id) FROM clusters), 0), 1),
                (SELECT COUNT(*) FROM clusters) > 0
            )
        SQL);
    }

    public function down(): void
    {
        // Nothing to undo: the sequence is derived state, not schema.
    }
};
