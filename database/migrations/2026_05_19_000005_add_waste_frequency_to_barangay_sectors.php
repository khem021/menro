<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('barangay_sectors', function (Blueprint $table) {
            $table->string('waste_frequency', 10)->nullable()->after('estimated_daily_waste_kg');
        });
    }

    public function down(): void
    {
        Schema::table('barangay_sectors', function (Blueprint $table) {
            $table->dropColumn('waste_frequency');
        });
    }
};
