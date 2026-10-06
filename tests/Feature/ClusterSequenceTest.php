<?php

namespace Tests\Feature;

use App\Http\Livewire\Dashboard\ClusterConfig;
use App\Models\Cluster;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Clusters 1-3 are written with explicit ids (create_clusters_table backfill,
 * FreshDemoSeeder). On PostgreSQL that leaves the identity sequence behind the
 * table, so the next insert reuses an id that already exists and "Add Cluster"
 * dies with a duplicate key violation — a 500 on a freshly seeded database.
 *
 * Writes run in a rolled-back transaction.
 */
class ClusterSequenceTest extends TestCase
{
    use DatabaseTransactions;

    private const RESYNC = <<<'SQL'
        SELECT setval(
            pg_get_serial_sequence('clusters', 'id'),
            GREATEST(COALESCE((SELECT MAX(id) FROM clusters), 0), 1),
            (SELECT COUNT(*) FROM clusters) > 0
        )
    SQL;

    private function asAdmin(): void
    {
        $u = User::whereHas('role', fn ($q) => $q->where('role_name', 'System Administrator'))
            ->firstOrFail();

        $state = [
            'auth_user_id' => $u->user_id,
            'auth_role'    => $u->role->role_name,
            'auth_pw'      => passwordFingerprint($u->password_hash),
        ];
        session($state);
        $this->withSession($state);
    }

    /** The sequence a seeded database leaves behind must be able to issue a free id. */
    public function test_the_cluster_id_sequence_is_ahead_of_the_rows_that_exist(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Sequence behaviour is PostgreSQL-specific.');
        }

        $seq  = DB::selectOne("SELECT pg_get_serial_sequence('clusters','id') AS s")->s;
        $row  = DB::selectOne("SELECT last_value, is_called FROM {$seq}");
        $next = $row->is_called ? $row->last_value + 1 : $row->last_value;

        $this->assertGreaterThan(
            (int) DB::table('clusters')->max('id'),
            $next,
            'The clusters id sequence is behind the table, so the next insert would collide.'
        );
    }

    /** Reproduces the production failure, then proves the resync clears it. */
    public function test_adding_a_cluster_survives_a_sequence_left_behind_by_explicit_ids(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Sequence behaviour is PostgreSQL-specific.');
        }

        // Mirror the seeder: write the row with an explicit id, which is what
        // leaves the sequence behind. Going through the query builder on purpose,
        // since `id` is not fillable on the model.
        DB::table('clusters')->insertOrIgnore([
            'id'         => 1,
            'name'       => 'Cluster 1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::statement("SELECT setval(pg_get_serial_sequence('clusters','id'), 1, false)");

        $this->assertDatabaseHas('clusters', ['id' => 1]);

        // Wrapped in a nested transaction so the failure rolls back to a savepoint.
        // Postgres aborts the enclosing transaction otherwise, and DatabaseTransactions
        // has already opened one around this test.
        try {
            DB::transaction(fn () => Cluster::create(['name' => null]));
            $this->fail('Expected a duplicate key violation from the stale sequence.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertSame('23505', $e->getCode(), 'Expected a unique violation.');
        }

        DB::statement(self::RESYNC);

        $cluster = Cluster::create(['name' => null]);
        $this->assertTrue($cluster->exists);
        $this->assertGreaterThan(1, $cluster->id);
    }

    /** The button a user actually clicks, end to end. */
    public function test_the_add_cluster_action_creates_a_cluster(): void
    {
        $this->asAdmin();

        $before = Cluster::count();

        Livewire::test(ClusterConfig::class)
            ->call('addCluster')
            ->assertHasNoErrors();

        $this->assertSame($before + 1, Cluster::count());
    }
}
