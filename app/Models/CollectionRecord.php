<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
        // Who did this is history. Archiving someone's account must not blank
        // their name out of the records they touched, so this relation ignores
        // the soft-delete scope. (BelongsTo::withTrashed() does not exist in
        // Laravel 9; withoutGlobalScope is the supported equivalent.)
        return $this->belongsTo(User::class, 'completed_by', 'user_id')
                    ->withoutGlobalScope(SoftDeletingScope::class);
    }
}
