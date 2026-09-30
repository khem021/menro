<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesDataCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    // Placeholder fine amounts loosely modeled on RA 9003 (Phil. Ecological
    // Solid Waste Management Act) penalty tiers. Adjust here if needed.
    public const FINE_TIERS = [
        1 => 500.00,
        2 => 1000.00,
        3 => 2500.00,
    ];

    public static function fineForOffense(int $offenseNumber): float
    {
        return self::FINE_TIERS[min(max($offenseNumber, 1), 3)];
    }

    public static function offenseLabel(int $offenseNumber): string
    {
        return match (min(max($offenseNumber, 1), 3)) {
            1 => '1st Offense',
            2 => '2nd Offense',
            default => '3rd Offense',
        };
    }

    /**
     * Next sequential ticket number for the given year, e.g. "2026-0001".
     * Uses MAX(sequence) over withTrashed() so a deleted ticket's number is
     * never reissued.
     */
    public static function nextTicketNumber(string $year): string
    {
        $maxSeq = (int) static::withTrashed()
            ->where('ticket_number', 'like', $year . '-%')
            ->selectRaw("MAX(CAST(SPLIT_PART(ticket_number, '-', 2) AS INTEGER)) AS max_seq")
            ->value('max_seq');

        return $year . '-' . str_pad((string) ($maxSeq + 1), 4, '0', STR_PAD_LEFT);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by', 'user_id');
    }
}
