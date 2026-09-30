<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->enum('period', ['daily', 'weekly', 'monthly', 'yearly'])->nullable()->after('report_type');
            $table->date('period_start')->nullable()->after('period');
            $table->date('period_end')->nullable()->after('period_start');
            $table->string('pdf_path', 255)->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['period', 'period_start', 'period_end', 'pdf_path']);
        });
    }
};
