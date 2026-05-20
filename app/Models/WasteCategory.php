<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WasteCategory extends Model
{
    protected $table = 'waste_categories';
    protected $primaryKey = 'category_id';

    protected $fillable = [
        'category_name',
        'description',
    ];

    public function wasteEntries(): HasMany
    {
        return $this->hasMany(WasteEntry::class, 'category_id', 'category_id');
    }
}
