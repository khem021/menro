<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SectorMember extends Model
{
    protected $primaryKey = 'member_id';

    protected $fillable = [
        'sector_id',
        'full_name',
        'address',
        'contact_number',
    ];

    public function sector(): BelongsTo
    {
        return $this->belongsTo(BarangaySector::class, 'sector_id', 'sector_id');
    }
}
