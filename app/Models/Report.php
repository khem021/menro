<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $table = 'reports';
    protected $primaryKey = 'report_id';

    protected $fillable = [
        'report_type',
        'period',
        'period_start',
        'period_end',
        'generated_by',
        'generated_at',
        'file_path',
        'pdf_path',
        'remarks',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'generated_at' => 'datetime',
    ];

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by', 'user_id');
    }
}
