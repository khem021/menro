<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesDataCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionSchedule extends Model
{
    use InvalidatesDataCache;

    protected static function dataCacheGroups(): array
    {
        return ['collections'];
    }

    protected $table = 'collection_schedules';
    protected $primaryKey = 'schedule_id';

    protected $fillable = [
        'barangay_id',
        'collection_date',
        'waste_type',
        'assigned_team',
        'assigned_vehicle',
        'status',
        'notes',
        'created_by',
    ];

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id', 'barangay_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function collectionRecords(): HasMany
    {
        return $this->hasMany(CollectionRecord::class, 'schedule_id', 'schedule_id');
    }
}
