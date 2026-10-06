<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * "No score recorded" used to be stored as 0, which the compliance table then
 * showed as a real 0/100. Allow NULL so a blank stays blank. Existing rows are
 * left as they are: a stored 0 cannot be told apart from a deliberate one.
 *
 * Raw statements because changing a column in place needs doctrine/dbal, which
 * this project does not install.
 */
return new class extends Migration
{
    public function up()
    {
        match (DB::getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE inspections ALTER COLUMN segregation_score DROP NOT NULL, ALTER COLUMN segregation_score DROP DEFAULT'),
            default => DB::statement('ALTER TABLE inspections MODIFY segregation_score TINYINT UNSIGNED NULL DEFAULT NULL'),
        };
    }

    public function down()
    {
        DB::table('inspections')->whereNull('segregation_score')->update(['segregation_score' => 0]);

        match (DB::getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE inspections ALTER COLUMN segregation_score SET DEFAULT 0, ALTER COLUMN segregation_score SET NOT NULL'),
            default => DB::statement('ALTER TABLE inspections MODIFY segregation_score TINYINT UNSIGNED NOT NULL DEFAULT 0'),
        };
    }
};
