<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesDataCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteEntry extends Model
{
    use InvalidatesDataCache;

    protected $table = 'waste_entries';
    protected $primaryKey = 'entry_id';

    protected static function dataCacheGroups(): array
    {
        return ['entries'];
    }

    protected $fillable = [
        'generator_id',
        'category_id',
        'quantity',
        'unit',
        'entry_date',
        'remarks',
        'encoded_by',
    ];

    public function wasteGenerator(): BelongsTo
    {
        return $this->belongsTo(WasteGenerator::class, 'generator_id', 'generator_id');
    }

    public function wasteCategory(): BelongsTo
    {
        return $this->belongsTo(WasteCategory::class, 'category_id', 'category_id');
    }

    public function encodedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by', 'user_id');
    }
}
