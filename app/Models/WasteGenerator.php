<?php

namespace App\Models;

use App\Models\Concerns\CascadesSoftDeletes;
use App\Models\Concerns\InvalidatesDataCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WasteGenerator extends Model
{
    use CascadesSoftDeletes;
    use InvalidatesDataCache;
    use SoftDeletes;

    protected $table = 'waste_generators';
    protected $primaryKey = 'generator_id';

    /** @return list<string> */
    protected static function cascadeSoftDeletes(): array
    {
        // A generator's entries and inspections used to be removed by the
        // database's ON DELETE CASCADE; soft deleting has to carry them along
        // itself or they outlive the generator they describe.
        return ['wasteEntries', 'inspections'];
    }

    protected static function dataCacheGroups(): array
    {
        return ['generators'];
    }

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
        // Who did this is history. Archiving someone's account must not blank
        // their name out of the records they touched, so this relation ignores
        // the soft-delete scope. (BelongsTo::withTrashed() does not exist in
        // Laravel 9; withoutGlobalScope is the supported equivalent.)
        return $this->belongsTo(User::class, 'created_by', 'user_id')
                    ->withoutGlobalScope(SoftDeletingScope::class);
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
