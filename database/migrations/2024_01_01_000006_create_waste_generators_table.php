<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('waste_generators', function (Blueprint $table) {
            $table->id('generator_id');
            $table->string('generator_name', 200);
            $table->foreignId('generator_type_id')->constrained('generator_types', 'generator_type_id')->onDelete('restrict');
            $table->foreignId('barangay_id')->constrained('barangays', 'barangay_id')->onDelete('restrict');
            $table->string('address', 255)->nullable();
            $table->string('contact_person', 150)->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->decimal('estimated_daily_waste_kg', 10, 2)->nullable();
            $table->enum('compliance_status', ['compliant', 'for_inspection', 'non_compliant'])->default('for_inspection');
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->timestamps();

            $table->index('barangay_id');
            $table->index('compliance_status');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_generators');
    }
};
