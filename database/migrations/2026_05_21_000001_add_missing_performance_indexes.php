<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Speed up role-based user lookups and user search
        Schema::table('users', function (Blueprint $table) {
            $table->index('role_id',   'idx_users_role_id');
            $table->index('status',    'idx_users_status');
        });

        // Speed up audit log multi-column filter (module + action + user)
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['module', 'action'],   'idx_audit_module_action');
            $table->index('user_id',              'idx_audit_user_id');
        });

        // Speed up generator compliance/status filters
        Schema::table('waste_generators', function (Blueprint $table) {
            $table->index('compliance_status', 'idx_generators_compliance');
            $table->index('status',            'idx_generators_status');
        });

        // Speed up incident date + status queries
        Schema::table('incidents', function (Blueprint $table) {
            $table->index(['status', 'date_reported'], 'idx_incidents_status_date');
        });

        // Speed up collection date range queries
        Schema::table('collection_schedules', function (Blueprint $table) {
            $table->index('collection_date', 'idx_collection_date');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role_id');
            $table->dropIndex('idx_users_status');
        });
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_module_action');
            $table->dropIndex('idx_audit_user_id');
        });
        Schema::table('waste_generators', function (Blueprint $table) {
            $table->dropIndex('idx_generators_compliance');
            $table->dropIndex('idx_generators_status');
        });
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropIndex('idx_incidents_status_date');
        });
        Schema::table('collection_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_collection_date');
        });
    }
};
