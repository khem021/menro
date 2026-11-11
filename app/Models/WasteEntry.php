<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesDataCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WasteEntry extends Model
{
    use InvalidatesDataCache;
    use SoftDeletes;

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
        // Who did this is history. Archiving someone's account must not blank
        // their name out of the records they touched, so this relation ignores
        // the soft-delete scope. (BelongsTo::withTrashed() does not exist in
        // Laravel 9; withoutGlobalScope is the supported equivalent.)
        return $this->belongsTo(User::class, 'encoded_by', 'user_id')
                    ->withoutGlobalScope(SoftDeletingScope::class);
    }
}
