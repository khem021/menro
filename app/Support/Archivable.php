<?php

namespace App\Support;

use App\Models\Barangay;
use App\Models\Cluster;
use App\Models\CollectionSchedule;
use App\Models\Incident;
use App\Models\Inspection;
use App\Models\SectorMember;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationTicket;
use App\Models\WasteEntry;
use App\Models\WasteGenerator;

/**
 * Every soft-deleting model the archive can show, with the little a generic view
 * needs to know about each: what to call it, which column carries the row's
 * human label, which other columns to list, who may restore it, and which
 * parents have to be live again first.
 *
 * Notifications are deliberately absent. Dismissing an alert is a per-user
 * read-and-clear action rather than a record deletion, so they are still hard
 * deleted (NotificationIndex::deleteNotification and ::deleteAll).
 */
class Archivable
{
    /**
     * `restore` mirrors the guard on the matching delete action, because the 16
     * delete sites are not uniform: staff may remove tickets and entries, but
     * only an administrator may remove a barangay, a sector member or a user. A
     * blanket "staff may restore" would let someone put back a record they were
     * never allowed to remove.
     */
    public const TYPES = [
        'violation_tickets' => [
            'model'   => ViolationTicket::class,
            'label'   => 'Violation Tickets',
            'module'  => 'ViolationTicket',
            'key'     => 'ticket_id',
            'title'   => 'violator_name',
            'search'  => 'violator_name',
            'columns' => ['ticket_number' => 'Ticket #', 'address' => 'Address', 'issued_date' => 'Issued'],
            'print'   => 'violation-tickets.receipt',
            'restore' => 'staff',
            'parents' => [],
        ],
        'waste_entries' => [
            'model'   => WasteEntry::class,
            'label'   => 'Waste Entries',
            'module'  => 'WasteEntry',
            'key'     => 'entry_id',
            'title'   => 'entry_date',
            'search'  => null,
            'columns' => ['quantity' => 'Quantity', 'unit' => 'Unit', 'entry_date' => 'Date'],
            'print'   => null,
            // Entries belong to whoever encoded them, so restoring one follows
            // the same rule as deleting it rather than a flat role check.
            'restore' => 'owner',
            'owner'   => 'encoded_by',
            'parents' => ['wasteGenerator' => 'generator'],
        ],
        'waste_generators' => [
            'model'   => WasteGenerator::class,
            'label'   => 'Waste Generators',
            'module'  => 'WasteGenerator',
            'key'     => 'generator_id',
            'title'   => 'generator_name',
            'search'  => 'generator_name',
            'columns' => ['compliance_status' => 'Compliance', 'status' => 'Status'],
            'print'   => null,
            'restore' => 'staff',
            'parents' => ['barangay' => 'barangay'],
        ],
        'inspections' => [
            'model'   => Inspection::class,
            'label'   => 'Inspections',
            'module'  => 'Inspection',
            'key'     => 'inspection_id',
            'title'   => 'inspection_date',
            'search'  => null,
            'columns' => ['compliance_status' => 'Result', 'inspection_date' => 'Date'],
            'print'   => null,
            'restore' => 'staff',
            'parents' => ['wasteGenerator' => 'generator'],
        ],
        'violations' => [
            'model'   => Violation::class,
            'label'   => 'Violations',
            'module'  => 'Violation',
            'key'     => 'violation_id',
            'title'   => 'violation_type',
            'search'  => 'violation_type',
            'columns' => ['severity' => 'Severity', 'resolution_status' => 'Resolution'],
            'print'   => null,
            'restore' => 'staff',
            'parents' => ['inspection' => 'inspection'],
        ],
        'incidents' => [
            'model'   => Incident::class,
            'label'   => 'Incidents',
            'module'  => 'Incident',
            'key'     => 'incident_id',
            'title'   => 'incident_type',
            'search'  => 'incident_type',
            'columns' => ['status' => 'Status', 'date_reported' => 'Reported'],
            'print'   => null,
            'restore' => 'staff',
            'parents' => ['barangay' => 'barangay'],
        ],
        'collection_schedules' => [
            'model'   => CollectionSchedule::class,
            'label'   => 'Collection Schedules',
            'module'  => 'CollectionSchedule',
            'key'     => 'schedule_id',
            'title'   => 'collection_date',
            'search'  => 'waste_type',
            'columns' => ['waste_type' => 'Waste Type', 'status' => 'Status'],
            'print'   => null,
            'restore' => 'staff',
            'parents' => ['barangay' => 'barangay'],
        ],
        'clusters' => [
            'model'   => Cluster::class,
            'label'   => 'Barangay Clusters',
            'module'  => 'Cluster',
            'key'     => 'id',
            'title'   => 'name',
            'search'  => 'name',
            'columns' => [],
            'print'   => null,
            'restore' => 'staff',
            'parents' => [],
        ],
        'barangays' => [
            'model'   => Barangay::class,
            'label'   => 'Barangays',
            'module'  => 'Barangay',
            'key'     => 'barangay_id',
            'title'   => 'barangay_name',
            'search'  => 'barangay_name',
            'columns' => ['cluster' => 'Cluster'],
            'print'   => null,
            'restore' => 'admin',
            'parents' => [],
        ],
        'sector_members' => [
            'model'   => SectorMember::class,
            'label'   => 'Sector Members',
            'module'  => 'SectorMember',
            'key'     => 'member_id',
            'title'   => 'full_name',
            'search'  => 'full_name',
            'columns' => ['address' => 'Address', 'contact_number' => 'Contact'],
            'print'   => null,
            'restore' => 'admin',
            'parents' => [],
        ],
        'users' => [
            'model'   => User::class,
            'label'   => 'User Accounts',
            'module'  => 'User',
            'key'     => 'user_id',
            'title'   => 'full_name',
            'search'  => 'full_name',
            'columns' => ['username' => 'Username', 'status' => 'Status'],
            'print'   => null,
            'restore' => 'admin',
            'parents' => [],
        ],
    ];

    /** @return array<string, string> type key => human label, for the filter. */
    public static function options(): array
    {
        return array_map(fn ($t) => $t['label'], self::TYPES);
    }

    public static function get(string $type): ?array
    {
        return self::TYPES[$type] ?? null;
    }

    /**
     * Whether the current session may restore records of this type.
     *
     * $record is needed only for the 'owner' mode, where the rule is per row
     * rather than per role. Without it the method answers the weaker question
     * "could this user restore any record of this type", which is what the list
     * view needs to decide whether to draw the button at all.
     */
    public static function canRestore(string $type, $record = null): bool
    {
        $config = self::get($type);

        if (! $config) {
            return false;
        }

        if ($config['restore'] === 'admin') {
            return isAdmin();
        }

        if (! canAccess(...ROLES_STAFF())) {
            return false;
        }

        if ($config['restore'] === 'owner' && $record !== null) {
            return isAdmin() || $record->{$config['owner']} === session('auth_user_id');
        }

        return true;
    }
}
