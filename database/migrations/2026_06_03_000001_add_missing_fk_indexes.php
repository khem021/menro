<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->index('inspection_id', 'idx_violations_inspection_id');
        });

        Schema::table('barangay_sectors', function (Blueprint $table) {
            $table->index('barangay_id', 'idx_barangay_sectors_barangay_id');
        });

        if (Schema::hasTable('collection_records')) {
            Schema::table('collection_records', function (Blueprint $table) {
                $table->index('schedule_id', 'idx_collection_records_schedule_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->dropIndex('idx_violations_inspection_id');
        });

        Schema::table('barangay_sectors', function (Blueprint $table) {
            $table->dropIndex('idx_barangay_sectors_barangay_id');
        });

        if (Schema::hasTable('collection_records')) {
            Schema::table('collection_records', function (Blueprint $table) {
                $table->dropIndex('idx_collection_records_schedule_id');
            });
        }
    }
};
