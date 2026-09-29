<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('violation_tickets', function (Blueprint $table) {
            $table->id('ticket_id');
            $table->string('ticket_number', 20)->unique();
            $table->string('violator_name', 255);
            $table->enum('violation_type', ['littering', 'dumping', 'burning', 'no_segregation', 'other']);
            $table->string('other_violation_description', 255)->nullable();
            $table->string('address', 255);
            $table->unsignedTinyInteger('offense_number')->default(1);
            $table->decimal('penalty_amount', 10, 2)->default(0);
            $table->date('issued_date');
            $table->foreignId('issued_by')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->text('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('violator_name');
            $table->index('violation_type');
            $table->index('issued_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_tickets');
    }
};
