<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Scopes the users.username and users.email uniqueness to live rows.
 *
 * Now that archiving a user leaves the row in place, a plain UNIQUE index would
 * reserve that person's username and email for ever. The one thing a municipal
 * office does with a departing employee's account is retire it and hand the
 * name to their replacement, so the constraint has to mean "unique among the
 * accounts that still exist" — which is what it should always have meant.
 *
 * Must run after the migration that adds users.deleted_at.
 */
return new class extends Migration
{
    /** index name => column. */
    private const PARTIAL = [
        'users_username_live_unique' => 'username',
        'users_email_live_unique'    => 'email',
    ];

    /** The plain unique indexes this replaces, as Laravel named them. */
    private const PLAIN = ['users_username_unique', 'users_email_unique'];

    public function up(): void
    {
        // MySQL has no partial unique index. The plain UNIQUE simply stays,
        // which means an archived user's name remains reserved — documented
        // rather than worked around, since this app runs on PostgreSQL 16.
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            foreach (self::PLAIN as $index) {
                $table->dropUnique($index);
            }
        });

        foreach (self::PARTIAL as $index => $column) {
            DB::statement("CREATE UNIQUE INDEX {$index} ON users ({$column}) WHERE deleted_at IS NULL");
        }
    }

    /**
     * Restoring the plain unique indexes fails if two rows — one of them
     * archived — now share a username or an email. That is honest: the
     * duplicates have to be resolved by hand before the stricter constraint can
     * come back.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (array_keys(self::PARTIAL) as $index) {
            DB::statement("DROP INDEX IF EXISTS {$index}");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username', 'users_username_unique');
            $table->unique('email', 'users_email_unique');
        });
    }
};
