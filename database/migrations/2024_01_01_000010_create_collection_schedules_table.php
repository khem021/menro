<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('collection_schedules', function (Blueprint $table) {
            $table->id('schedule_id');
            $table->foreignId('barangay_id')->constrained('barangays', 'barangay_id')->onDelete('restrict');
            $table->date('collection_date');
            $table->string('waste_type', 100)->nullable();
            $table->string('assigned_team', 150)->nullable();
            $table->string('assigned_vehicle', 100)->nullable();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'missed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->timestamps();

            $table->index('collection_date');
            $table->index('barangay_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_schedules');
    }
};
