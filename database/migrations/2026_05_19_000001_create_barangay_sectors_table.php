<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barangay_sectors', function (Blueprint $table) {
            $table->id('sector_id');
            $table->unsignedBigInteger('barangay_id');
            $table->unsignedTinyInteger('sector_number'); // 1–6
            $table->string('sector_name', 100);
            $table->timestamps();

            $table->unique(['barangay_id', 'sector_number']);
            $table->foreign('barangay_id')
                  ->references('barangay_id')
                  ->on('barangays')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangay_sectors');
    }
};
