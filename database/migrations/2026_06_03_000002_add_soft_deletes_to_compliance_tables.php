<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('deleted_at', 'idx_incidents_deleted_at');
        });

        Schema::table('inspections', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('deleted_at', 'idx_inspections_deleted_at');
        });

        Schema::table('violations', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('deleted_at', 'idx_violations_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropIndex('idx_incidents_deleted_at');
            $table->dropSoftDeletes();
        });

        Schema::table('inspections', function (Blueprint $table) {
            $table->dropIndex('idx_inspections_deleted_at');
            $table->dropSoftDeletes();
        });

        Schema::table('violations', function (Blueprint $table) {
            $table->dropIndex('idx_violations_deleted_at');
            $table->dropSoftDeletes();
        });
    }
};
