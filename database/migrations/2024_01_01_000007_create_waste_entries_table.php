<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('waste_entries', function (Blueprint $table) {
            $table->id('entry_id');
            $table->foreignId('generator_id')->constrained('waste_generators', 'generator_id')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('waste_categories', 'category_id')->onDelete('restrict');
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 20)->default('kg');
            $table->date('entry_date');
            $table->text('remarks')->nullable();
            $table->foreignId('encoded_by')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->timestamps();

            $table->index('entry_date');
            $table->index('generator_id');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_entries');
    }
};
