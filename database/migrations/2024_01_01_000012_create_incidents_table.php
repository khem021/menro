<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id('incident_id');
            $table->foreignId('barangay_id')->constrained('barangays', 'barangay_id')->onDelete('restrict');
            $table->foreignId('reported_by')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->enum('incident_type', ['illegal_dumping', 'open_burning', 'improper_disposal', 'other']);
            $table->text('description');
            $table->string('location_details', 255)->nullable();
            $table->date('date_reported');
            $table->enum('status', ['reported', 'for_validation', 'under_investigation', 'resolved', 'closed'])->default('reported');
            $table->foreignId('assigned_to')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('incident_type');
            $table->index('date_reported');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
