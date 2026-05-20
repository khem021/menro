<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inspection extends Model
{
    protected $table = 'inspections';
    protected $primaryKey = 'inspection_id';

    protected $fillable = [
        'generator_id',
        'inspection_date',
        'inspector_id',
        'compliance_status',
        'segregation_score',
        'remarks',
        'recommendation',
        'next_follow_up',
    ];

    public function wasteGenerator(): BelongsTo
    {
        return $this->belongsTo(WasteGenerator::class, 'generator_id', 'generator_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id', 'user_id');
    }

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class, 'inspection_id', 'inspection_id');
    }
}
