<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE barangay_sectors
                ADD COLUMN household_count          SMALLINT UNSIGNED NULL AFTER sector_name,
                ADD COLUMN purok_leader_name        VARCHAR(100)      NULL AFTER household_count,
                ADD COLUMN purok_leader_contact     VARCHAR(30)       NULL AFTER purok_leader_name,
                ADD COLUMN estimated_daily_waste_kg DECIMAL(8,2)      NULL AFTER purok_leader_contact,
                ADD COLUMN collection_day           VARCHAR(15)       NULL AFTER estimated_daily_waste_kg
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE barangay_sectors
                DROP COLUMN household_count,
                DROP COLUMN purok_leader_name,
                DROP COLUMN purok_leader_contact,
                DROP COLUMN estimated_daily_waste_kg,
                DROP COLUMN collection_day
        ");
    }
};
