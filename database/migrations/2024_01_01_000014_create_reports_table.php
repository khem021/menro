<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id('report_id');
            $table->enum('report_type', ['monthly_waste', 'compliance_summary', 'incident_summary', 'collection_summary']);
            $table->foreignId('generated_by')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->timestamp('generated_at')->useCurrent();
            $table->string('file_path', 255)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
