<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE barangays MODIFY COLUMN cluster TINYINT UNSIGNED NULL COMMENT "1, 2, or 3"');
    }

    public function down(): void
    {
        DB::statement('UPDATE barangays SET cluster = 1 WHERE cluster IS NULL');
        DB::statement('ALTER TABLE barangays MODIFY COLUMN cluster TINYINT UNSIGNED NOT NULL COMMENT "1, 2, or 3"');
    }
};
