<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionRecord extends Model
{
    protected $table = 'collection_records';
    protected $primaryKey = 'record_id';

    protected $fillable = [
        'schedule_id',
        'actual_collection_date',
        'collected_quantity',
        'unit',
        'remarks',
        'completed_by',
    ];

    public function collectionSchedule(): BelongsTo
    {
        return $this->belongsTo(CollectionSchedule::class, 'schedule_id', 'schedule_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by', 'user_id');
    }
}
