<?php

namespace App\Models\Concerns;

use App\Services\DataCache;

/**
 * Clears the derived caches a model feeds whenever one of its rows changes.
 *
 * Models using this name the {@see DataCache} groups they belong to:
 *
 *     protected static function dataCacheGroups(): array
 *     {
 *         return ['barangays'];
 *     }
 *
 * Invalidation then happens wherever the write happens — a Livewire component,
 * an artisan command, a seeder or tinker — instead of only at the call sites
 * that remembered to clear it.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait InvalidatesDataCache
{
    public static function bootInvalidatesDataCache(): void
    {
        $groups = static::dataCacheGroups();
        $flush  = static fn() => DataCache::flush(...$groups);

        static::saved($flush);
        static::deleted($flush);

        // `deleted` already covers a soft delete, but restoring a row brings it
        // back into the counts, so soft-deleting models need that too. The
        // event only exists where the SoftDeletes trait provides it.
        if (method_exists(static::class, 'restored')) {
            static::restored($flush);
        }
    }

    /**
     * DataCache groups this model's rows contribute to. Overridden per model.
     *
     * @return list<string>
     */
    protected static function dataCacheGroups(): array
    {
        return [];
    }
}
