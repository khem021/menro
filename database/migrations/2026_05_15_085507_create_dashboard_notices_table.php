<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dashboard_notices', function (Blueprint $table) {
            $table->id('notice_id');
            $table->tinyInteger('box_number')->unsigned();
            $table->string('content', 500);
            $table->foreignId('created_by')->nullable()->constrained('users', 'user_id')->onDelete('set null');
            $table->timestamps();
            $table->index('box_number');
        });

        Schema::create('dashboard_box_settings', function (Blueprint $table) {
            $table->tinyInteger('box_number')->primary()->unsigned();
            $table->string('title', 100)->default('Notice Board');
            $table->string('color', 20)->default('blue');
        });

        // Seed default box settings
        DB::table('dashboard_box_settings')->insert([
            ['box_number' => 1, 'title' => 'Announcements',      'color' => 'blue'],
            ['box_number' => 2, 'title' => 'Reminders',          'color' => 'yellow'],
            ['box_number' => 3, 'title' => 'Important Alerts',   'color' => 'red'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('dashboard_notices');
        Schema::dropIfExists('dashboard_box_settings');
    }
};
