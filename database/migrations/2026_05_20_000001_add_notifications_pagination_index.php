<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Optimises paginated per-user listing: WHERE user_id = ? ORDER BY created_at DESC
            $table->index(['user_id', 'created_at'], 'idx_notifications_user_created_at');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_created_at');
        });
    }
};
