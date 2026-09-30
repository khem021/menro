<?php

namespace Tests\Unit;

use App\Services\DataCache;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;
use Tests\TestCase;

/**
 * The dashboard, analytics and lookup queries are cached for 1–10 minutes, so a
 * write that fails to invalidate shows the user stale data with no error to
 * signal it. These tests pin that invalidation down.
 *
 * No database is needed: the model events are dispatched directly.
 */
class DataCacheTest extends TestCase
{
    /**
     * Each model, and one cache key that a write to it must clear.
     *
     * @return array<string, array{class-string, string}>
     */
    public static function modelProvider(): array
    {
        return [
            'barangay'            => [\App\Models\Barangay::class,           'lookup:barangays'],
            'cluster'             => [\App\Models\Cluster::class,            'lookup:barangays'],
            'waste entry'         => [\App\Models\WasteEntry::class,         'analytics:waste_by_category'],
            'waste generator'     => [\App\Models\WasteGenerator::class,     'lookup:generators'],
            'violation'           => [\App\Models\Violation::class,          'nav:open_violations'],
            'incident'            => [\App\Models\Incident::class,           'dashboard:recent_incidents'],
            'inspection'          => [\App\Models\Inspection::class,         'lookup:inspections_recent'],
            'collection schedule' => [\App\Models\CollectionSchedule::class, 'dashboard:upcoming_collections'],
            'user'                => [\App\Models\User::class,               'lookup:inspectors'],
            'violation ticket'    => [\App\Models\ViolationTicket::class,    'stats:violation_tickets'],
        ];
    }

    /**
     * @dataProvider modelProvider
     */
    public function test_saving_a_record_clears_its_cached_read_models(string $model, string $key): void
    {
        $this->assertCacheClearedBy($model, 'saved', $key);
    }

    /**
     * @dataProvider modelProvider
     */
    public function test_deleting_a_record_clears_its_cached_read_models(string $model, string $key): void
    {
        $this->assertCacheClearedBy($model, 'deleted', $key);
    }

    /**
     * Soft deletes already fire `deleted`, but restoring a row puts it back into
     * the counts and must invalidate too.
     *
     * @dataProvider modelProvider
     */
    public function test_restoring_a_soft_deleted_record_clears_its_cached_read_models(string $model, string $key): void
    {
        if (!method_exists($model, 'restored')) {
            $this->markTestSkipped($model . ' does not use SoftDeletes.');
        }

        $this->assertCacheClearedBy($model, 'restored', $key);
    }

    public function test_flush_clears_every_key_in_a_group(): void
    {
        $keys = ['lookup:barangays', 'lookup:barangays_full', 'dashboard:charts',
                 'dashboard:upcoming_collections', 'analytics:waste_by_cluster'];

        foreach ($keys as $key) {
            Cache::put($key, 'stale', 300);
        }

        DataCache::flush('barangays');

        foreach ($keys as $key) {
            $this->assertFalse(Cache::has($key), "{$key} should have been cleared");
        }
    }

    public function test_flushing_an_unknown_group_is_harmless(): void
    {
        Cache::put('dashboard:kpis', 'fresh', 300);

        DataCache::flush('no-such-group');

        $this->assertTrue(Cache::has('dashboard:kpis'));
    }

    /**
     * The scheduled commands write with the query builder, which fires no model
     * events, so they clear their caches by hand. Those keys must stay mapped —
     * if one stops being covered, the hand-written call is the only thing left
     * keeping that view fresh.
     */
    public function test_keys_cleared_by_scheduled_commands_are_covered_by_a_group(): void
    {
        $covered = $this->allCoveredKeys();

        foreach (glob(base_path('app/Console/Commands/*.php')) as $file) {
            preg_match_all("/Cache::forget\('([^']+)'\)/", file_get_contents($file), $matches);

            foreach ($matches[1] as $key) {
                $this->assertContains($key, $covered,
                    basename($file) . " clears {$key}, which no DataCache group covers");
            }
        }
    }

    private function assertCacheClearedBy(string $model, string $event, string $key): void
    {
        Cache::put($key, 'stale', 300);
        $this->assertTrue(Cache::has($key), 'cache fixture did not take');

        $model::getEventDispatcher()->dispatch("eloquent.{$event}: {$model}", [new $model()]);

        $this->assertFalse(Cache::has($key),
            class_basename($model) . " {$event} should have cleared {$key}");
    }

    /** @return list<string> */
    private function allCoveredKeys(): array
    {
        $groups = (new ReflectionClass(DataCache::class))->getConstant('GROUPS');

        return array_values(array_unique(array_merge(...array_values($groups))));
    }
}
