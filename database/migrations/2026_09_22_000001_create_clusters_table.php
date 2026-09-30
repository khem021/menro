<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clusters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->timestamps();
        });

        // Backfill a row for every cluster number already assigned to a barangay,
        // so existing dashboard/analytics queries (which group raw barangays.cluster
        // integers) keep matching the same cluster identities.
        $existingClusters = DB::table('barangays')
            ->whereNotNull('cluster')
            ->distinct()
            ->orderBy('cluster')
            ->pluck('cluster');

        foreach ($existingClusters as $number) {
            DB::table('clusters')->insert([
                'id'         => $number,
                'name'       => 'Cluster ' . $number,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($existingClusters->isNotEmpty() && DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('clusters','id'), (SELECT MAX(id) FROM clusters))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('clusters');
    }
};
