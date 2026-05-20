<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Violation extends Model
{
    protected $table = 'violations';
    protected $primaryKey = 'violation_id';

    protected $fillable = [
        'inspection_id',
        'violation_type',
        'description',
        'severity',
        'penalty_status',
        'resolution_status',
        'resolved_date',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'inspection_id', 'inspection_id');
    }
}
