<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Make email nullable without Doctrine DBAL (Laravel 9 limitation)
        DB::statement('ALTER TABLE users MODIFY COLUMN email VARCHAR(150) NULL');
    }

    public function down(): void
    {
        // Restore to NOT NULL (will fail if any rows have NULL email)
        DB::statement('ALTER TABLE users MODIFY COLUMN email VARCHAR(150) NOT NULL');
    }
};
