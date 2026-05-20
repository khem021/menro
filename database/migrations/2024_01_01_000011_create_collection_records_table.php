<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('collection_records', function (Blueprint $table) {
            $table->id('record_id');
            $table->foreignId('schedule_id')->constrained('collection_schedules', 'schedule_id')->onDelete('cascade');
            $table->date('actual_collection_date');
            $table->decimal('collected_quantity', 10, 2)->nullable();
            $table->string('unit', 20)->default('kg');
            $table->text('remarks')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_records');
    }
};
