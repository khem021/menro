<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesDataCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cluster extends Model
{
    use InvalidatesDataCache;

    protected $fillable = ['name'];

    protected static function dataCacheGroups(): array
    {
        return ['barangays'];
    }

    public function barangays(): HasMany
    {
        return $this->hasMany(Barangay::class, 'cluster', 'id');
    }
}
