<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN email DROP NOT NULL');
        } else {
            DB::statement('ALTER TABLE users MODIFY COLUMN email VARCHAR(150) NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN email SET NOT NULL');
        } else {
            DB::statement('ALTER TABLE users MODIFY COLUMN email VARCHAR(150) NOT NULL');
        }
    }
};
