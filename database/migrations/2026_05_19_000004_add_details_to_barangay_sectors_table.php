<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('barangay_sectors', function (Blueprint $table) {
            $table->smallInteger('household_count')->unsigned()->nullable()->after('sector_name');
            $table->string('purok_leader_name', 100)->nullable()->after('household_count');
            $table->string('purok_leader_contact', 30)->nullable()->after('purok_leader_name');
            $table->decimal('estimated_daily_waste_kg', 8, 2)->nullable()->after('purok_leader_contact');
            $table->string('collection_day', 15)->nullable()->after('estimated_daily_waste_kg');
        });
    }

    public function down(): void
    {
        Schema::table('barangay_sectors', function (Blueprint $table) {
            $table->dropColumn([
                'household_count',
                'purok_leader_name',
                'purok_leader_contact',
                'estimated_daily_waste_kg',
                'collection_day',
            ]);
        });
    }
};
