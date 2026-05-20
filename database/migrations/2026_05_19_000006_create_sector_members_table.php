<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sector_members', function (Blueprint $table) {
            $table->id('member_id');
            $table->unsignedBigInteger('sector_id');
            $table->string('full_name', 150);
            $table->string('address', 255)->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->timestamps();

            $table->foreign('sector_id')
                  ->references('sector_id')
                  ->on('barangay_sectors')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sector_members');
    }
};
