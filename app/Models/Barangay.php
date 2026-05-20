<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barangay extends Model
{
    protected $table = 'barangays';
    protected $primaryKey = 'barangay_id';

    protected $fillable = [
        'barangay_name',
        'cluster',
        'municipality',
        'province',
    ];

    public function sectors(): HasMany
    {
        return $this->hasMany(BarangaySector::class, 'barangay_id', 'barangay_id')
                    ->orderBy('sector_number');
    }

    public function wasteGenerators(): HasMany
    {
        return $this->hasMany(WasteGenerator::class, 'barangay_id', 'barangay_id');
    }

    public function collectionSchedules(): HasMany
    {
        return $this->hasMany(CollectionSchedule::class, 'barangay_id', 'barangay_id');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'barangay_id', 'barangay_id');
    }
}
