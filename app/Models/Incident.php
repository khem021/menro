<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
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
        return $this->belongsTo(User::class, 'reported_by', 'user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }
}
