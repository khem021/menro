<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('barangays', function (Blueprint $table) {
            $table->id('barangay_id');
            $table->string('barangay_name', 100);
            $table->tinyInteger('cluster')->unsigned()->comment('1, 2, or 3');
            $table->string('municipality', 100)->default('Madrid');
            $table->string('province', 100)->default('Surigao del Sur');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangays');
    }
};
