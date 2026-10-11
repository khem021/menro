<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Brings the remaining deletable tables onto soft deletes, so that every record
 * a user removes lands in the archive instead of vanishing.
 *
 * Incidents, inspections and violations already had this from
 * 2026_06_03_000002, and violation_tickets from its create migration.
 *
 * Notifications are deliberately absent: dismissing an alert is a per-user
 * read-and-clear action, not a record deletion, and archiving them would bury
 * the real deletions under thousands of dismissals.
 */
return new class extends Migration
{
    /** table => the deleted_at index name. */
    private const TABLES = [
        'barangays'            => 'idx_barangays_deleted_at',
        'sector_members'       => 'idx_sector_members_deleted_at',
        'collection_schedules' => 'idx_collection_schedules_deleted_at',
        'clusters'             => 'idx_clusters_deleted_at',
        'waste_entries'        => 'idx_waste_entries_deleted_at',
        'waste_generators'     => 'idx_waste_generators_deleted_at',
        'users'                => 'idx_users_deleted_at',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table => $index) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($index) {
                $t->softDeletes();
                // Every list query now filters on this column, so it earns an
                // index even though almost every row will hold NULL.
                $t->index('deleted_at', $index);
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::TABLES, true) as $table => $index) {
            if (! Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($index) {
                $t->dropIndex($index);
                $t->dropSoftDeletes();
            });
        }
    }
};
