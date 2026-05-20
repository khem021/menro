<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('violations', function (Blueprint $table) {
            $table->id('violation_id');
            $table->foreignId('inspection_id')->constrained('inspections', 'inspection_id')->onDelete('cascade');
            $table->string('violation_type', 150);
            $table->text('description')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->enum('penalty_status', ['none', 'warning_issued', 'penalty_pending', 'penalty_applied'])->default('none');
            $table->enum('resolution_status', ['open', 'in_progress', 'resolved', 'dismissed'])->default('open');
            $table->date('resolved_date')->nullable();
            $table->timestamps();

            $table->index('resolution_status');
            $table->index('severity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
