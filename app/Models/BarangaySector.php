<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BarangaySector extends Model
{
    protected $primaryKey = 'sector_id';

    protected $fillable = [
        'barangay_id',
        'sector_number',
        'sector_name',
        'household_count',
        'purok_leader_name',
        'purok_leader_contact',
        'estimated_daily_waste_kg',
        'waste_frequency',
        'collection_day',
    ];

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id', 'barangay_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(SectorMember::class, 'sector_id', 'sector_id')->orderBy('full_name');
    }
}
