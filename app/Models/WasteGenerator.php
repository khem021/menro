<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WasteGenerator extends Model
{
    protected $table = 'waste_generators';
    protected $primaryKey = 'generator_id';

    protected $fillable = [
        'generator_name',
        'generator_type_id',
        'barangay_id',
        'address',
        'contact_person',
        'contact_number',
        'email',
        'estimated_daily_waste_kg',
        'compliance_status',
        'status',
        'created_by',
    ];

    public function generatorType(): BelongsTo
    {
        return $this->belongsTo(GeneratorType::class, 'generator_type_id', 'generator_type_id');
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id', 'barangay_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function wasteEntries(): HasMany
    {
        return $this->hasMany(WasteEntry::class, 'generator_id', 'generator_id');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'generator_id', 'generator_id');
    }
}
