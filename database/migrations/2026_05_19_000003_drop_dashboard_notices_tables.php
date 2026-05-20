<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('dashboard_notices');
        Schema::dropIfExists('dashboard_box_settings');
    }

    public function down(): void
    {
        // Restore is handled by the original 2026_05_15_085507 migration if needed
    }
};
