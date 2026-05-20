<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE barangay_sectors
                ADD COLUMN waste_frequency VARCHAR(10) NULL AFTER estimated_daily_waste_kg
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE barangay_sectors
                DROP COLUMN waste_frequency
        ");
    }
};
