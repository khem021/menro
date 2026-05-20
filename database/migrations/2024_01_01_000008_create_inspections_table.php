<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id('inspection_id');
            $table->foreignId('generator_id')->constrained('waste_generators', 'generator_id')->onDelete('cascade');
            $table->date('inspection_date');
            $table->foreignId('inspector_id')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->enum('compliance_status', ['compliant', 'warning', 'violation', 'for_follow_up'])->default('for_follow_up');
            $table->tinyInteger('segregation_score')->unsigned()->default(0)->comment('0-100');
            $table->text('remarks')->nullable();
            $table->text('recommendation')->nullable();
            $table->date('next_follow_up')->nullable();
            $table->timestamps();

            $table->index('generator_id');
            $table->index('inspection_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
