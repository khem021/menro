<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('created_at', 'idx_audit_logs_created_at');
        });

        Schema::table('collection_schedules', function (Blueprint $table) {
            $table->index(['status', 'collection_date'], 'idx_collection_status_date');
        });

        Schema::table('waste_entries', function (Blueprint $table) {
            $table->index(['entry_date', 'generator_id'], 'idx_waste_entries_date_gen');
        });

        Schema::table('violations', function (Blueprint $table) {
            $table->index(['resolution_status', 'severity'], 'idx_violations_status_severity');
        });

        Schema::table('inspections', function (Blueprint $table) {
            $table->index(['compliance_status', 'next_follow_up'], 'idx_inspections_status_followup');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_logs_created_at');
        });
        Schema::table('collection_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_collection_status_date');
        });
        Schema::table('waste_entries', function (Blueprint $table) {
            $table->dropIndex('idx_waste_entries_date_gen');
        });
        Schema::table('violations', function (Blueprint $table) {
            $table->dropIndex('idx_violations_status_severity');
        });
        Schema::table('inspections', function (Blueprint $table) {
            $table->dropIndex('idx_inspections_status_followup');
        });
    }
};
