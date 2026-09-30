<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesDataCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Violation extends Model
{
    use SoftDeletes;
    use InvalidatesDataCache;

    protected static function dataCacheGroups(): array
    {
        return ['violations'];
    }

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
