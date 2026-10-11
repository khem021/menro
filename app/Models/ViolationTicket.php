<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesDataCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\SoftDeletes;

class ViolationTicket extends Model
{
    use SoftDeletes;
    use InvalidatesDataCache;

    protected static function dataCacheGroups(): array
    {
        return ['violation_tickets'];
    }

    protected $table = 'violation_tickets';
    protected $primaryKey = 'ticket_id';

    protected $fillable = [
        'ticket_number',
        'violator_name',
        'violation_type',
        'other_violation_description',
        'address',
        'offense_number',
        'penalty_amount',
        'issued_date',
        'issued_by',
        'remarks',
    ];

    protected $casts = [
        'issued_date'    => 'date:Y-m-d',
        'offense_number' => 'integer',
        'penalty_amount' => 'decimal:2',
    ];

    public const VIOLATION_TYPES = [
        'littering'      => 'Littering',
        'dumping'        => 'Throwing/Dumping of Waste in Public',
        'burning'        => 'Burning of Solid Waste',
        'no_segregation' => 'No Segregation of Waste',
        'other'          => 'Other Violations',
    ];

    /**
     * The municipal penalty schedule. A first offense is a reprimand carrying no
     * fine; the schedule caps at the third tier, so a fourth and every later
     * offense costs the same as the third.
     */
    public const FINE_TIERS = [
        1 => 0.00,
        2 => 500.00,
        3 => 1000.00,
    ];

    /**
     * The tier an offense count falls into. The one place the cap lives, so the
     * label, the badge and the fine can never disagree about where it sits.
     */
    public static function offenseTier(int $offenseNumber): int
    {
        return min(max($offenseNumber, 1), 3);
    }

    public static function fineForOffense(int $offenseNumber): float
    {
        return self::FINE_TIERS[self::offenseTier($offenseNumber)];
    }

    /**
     * The money caps at the third tier but the count does not: an officer
     * serving a sixth ticket on the same person should read "6th Offense", not
     * "3rd". Only the amount is capped, by fineForOffense() above.
     */
    public static function offenseLabel(int $offenseNumber): string
    {
        $n = max($offenseNumber, 1);

        if ($n === 1) {
            return 'Reprimand';
        }

        // 11th, 12th and 13th are the cases a bare modulo-10 lookup gets wrong.
        $suffix = match (true) {
            in_array($n % 100, [11, 12, 13], true) => 'th',
            $n % 10 === 1 => 'st',
            $n % 10 === 2 => 'nd',
            $n % 10 === 3 => 'rd',
            default       => 'th',
        };

        return $n . $suffix . ' Offense';
    }

    /** Badge class for the shared `badge-*` vocabulary (index list and form). */
    public static function offenseBadge(int $offenseNumber): string
    {
        return match (self::offenseTier($offenseNumber)) {
            1 => 'badge-blue',
            2 => 'badge-yellow',
            default => 'badge-red',
        };
    }

    /**
     * Next sequential ticket number for the given year, e.g. "2026-0001".
     * Uses MAX(sequence) over withTrashed() so a deleted ticket's number is
     * never reissued.
     */
    public static function nextTicketNumber(string $year): string
    {
        // Longest number first so "2026-10000" outranks "2026-9999"; LENGTH()
        // is portable across PostgreSQL and MySQL, unlike string-splitting functions.
        $last = static::withTrashed()
            ->where('ticket_number', 'like', $year . '-%')
            ->orderByRaw('LENGTH(ticket_number) DESC')
            ->orderByDesc('ticket_number')
            ->value('ticket_number');

        $maxSeq = $last ? (int) substr($last, strlen($year) + 1) : 0;

        return $year . '-' . str_pad((string) ($maxSeq + 1), 4, '0', STR_PAD_LEFT);
    }

    public function issuedBy(): BelongsTo
    {
        // Who did this is history. Archiving someone's account must not blank
        // their name out of the records they touched, so this relation ignores
        // the soft-delete scope. (BelongsTo::withTrashed() does not exist in
        // Laravel 9; withoutGlobalScope is the supported equivalent.)
        return $this->belongsTo(User::class, 'issued_by', 'user_id')
                    ->withoutGlobalScope(SoftDeletingScope::class);
    }
}
