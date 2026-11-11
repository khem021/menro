<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesDataCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\SoftDeletes;

class Incident extends Model
{
    use SoftDeletes;
    use InvalidatesDataCache;

    protected static function dataCacheGroups(): array
    {
        return ['incidents'];
    }

    protected $table = 'incidents';
    protected $primaryKey = 'incident_id';

    protected $fillable = [
        'barangay_id',
        'reported_by',
        'incident_type',
        'description',
        'location_details',
        'date_reported',
        'status',
        'assigned_to',
        'resolution_notes',
        'resolved_at',
    ];

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id', 'barangay_id');
    }

    public function reporter(): BelongsTo
    {
        // Who did this is history. Archiving someone's account must not blank
        // their name out of the records they touched, so this relation ignores
        // the soft-delete scope. (BelongsTo::withTrashed() does not exist in
        // Laravel 9; withoutGlobalScope is the supported equivalent.)
        return $this->belongsTo(User::class, 'reported_by', 'user_id')
                    ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function assignee(): BelongsTo
    {
        // Who did this is history. Archiving someone's account must not blank
        // their name out of the records they touched, so this relation ignores
        // the soft-delete scope. (BelongsTo::withTrashed() does not exist in
        // Laravel 9; withoutGlobalScope is the supported equivalent.)
        return $this->belongsTo(User::class, 'assigned_to', 'user_id')
                    ->withoutGlobalScope(SoftDeletingScope::class);
    }
}
