<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Central map of cached, derived read models and the domain data they are
 * built from.
 *
 * Dashboard, analytics and lookup queries are cached for 1–10 minutes. Those
 * caches used to be cleared by hand at each write site, which meant a write
 * path added later silently served stale data until its TTL expired. Listing
 * the relationship in one place instead lets the models themselves invalidate
 * what they affect (see {@see \App\Models\Concerns\InvalidatesDataCache}).
 *
 * Flushing a key that did not actually change is harmless — it costs one
 * recomputation. Failing to flush one that did is a correctness bug, so each
 * group below errs towards listing more.
 */
class DataCache
{
    private const ANALYTICS = [
        'analytics:waste_by_category',
        'analytics:waste_by_generator_type',
        'analytics:waste_by_cluster',
        'analytics:top_generators',
    ];

    private const GROUPS = [
        // Barangays and clusters feed the barangay pickers, the per-cluster
        // dashboard charts and the upcoming-collection board.
        'barangays' => [
            'lookup:barangays',
            'lookup:barangays_full',
            'dashboard:charts',
            'dashboard:upcoming_collections',
            'analytics:waste_by_cluster',
            'analytics:top_generators',
        ],

        // Generators carry the barangay and generator-type a waste entry rolls
        // up through, so editing one reshapes both dashboard and analytics.
        'generators' => [
            'stats:generators',
            'lookup:generators',
            'lookup:generators_active',
            'stats:compliance_pipeline',
            'dashboard:kpis',
            'dashboard:alerts',
            'dashboard:charts',
            'analytics:waste_by_generator_type',
            'analytics:waste_by_cluster',
            'analytics:top_generators',
        ],

        // Every analytics figure is an aggregate over waste_entries.
        'entries' => [
            'stats:entries',
            'dashboard:kpis',
            'dashboard:recent_entries',
            'dashboard:charts',
            ...self::ANALYTICS,
        ],

        'violations' => [
            'stats:violations',
            'nav:open_violations',
            'stats:compliance_pipeline',
            'dashboard:kpis',
            'dashboard:alerts',
        ],

        'incidents' => [
            'stats:incidents',
            'stats:compliance_pipeline',
            'dashboard:kpis',
            'dashboard:recent_incidents',
        ],

        // An inspection outcome also moves the generator's compliance status,
        // which is what stats:generators and the alert counters report on.
        'inspections' => [
            'stats:inspections',
            'stats:generators',
            'stats:compliance_pipeline',
            'lookup:inspections_recent',
            'dashboard:kpis',
            'dashboard:alerts',
        ],

        'collections' => [
            'stats:collections',
            'dashboard:upcoming_collections',
            'dashboard:kpis',
            'dashboard:alerts',
        ],

        'users' => [
            'stats:users',
            'audit:users',
            'lookup:users_active',
            'lookup:users_notif',
            'lookup:inspectors',
        ],

        'violation_tickets' => [
            'stats:violation_tickets',
        ],
    ];

    /**
     * Drop every cached key derived from the named groups.
     */
    public static function flush(string ...$groups): void
    {
        foreach ($groups as $group) {
            foreach (self::GROUPS[$group] ?? [] as $key) {
                Cache::forget($key);
            }
        }
    }
}
