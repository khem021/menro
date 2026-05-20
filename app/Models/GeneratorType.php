<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeneratorType extends Model
{
    protected $table = 'generator_types';
    protected $primaryKey = 'generator_type_id';

    protected $fillable = [
        'type_name',
        'description',
    ];

    public function wasteGenerators(): HasMany
    {
        return $this->hasMany(WasteGenerator::class, 'generator_type_id', 'generator_type_id');
    }
}
