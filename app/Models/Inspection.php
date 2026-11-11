<?php

namespace App\Models;

use App\Models\Concerns\CascadesSoftDeletes;
use App\Models\Concerns\InvalidatesDataCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inspection extends Model
{
    use SoftDeletes;
    use CascadesSoftDeletes;
    use InvalidatesDataCache;

    /** @return list<string> */
    protected static function cascadeSoftDeletes(): array
    {
        // A violation only means anything as a finding of its inspection, so
        // it follows the inspection into and out of the archive.
        return ['violations'];
    }

    protected static function dataCacheGroups(): array
    {
        return ['inspections'];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::deleting(function (Inspection $inspection) {
            $inspection->violations()->each(fn($v) => $v->delete());
        });
    }
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
        // Who did this is history. Archiving someone's account must not blank
        // their name out of the records they touched, so this relation ignores
        // the soft-delete scope. (BelongsTo::withTrashed() does not exist in
        // Laravel 9; withoutGlobalScope is the supported equivalent.)
        return $this->belongsTo(User::class, 'inspector_id', 'user_id')
                    ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class, 'inspection_id', 'inspection_id');
    }
}
