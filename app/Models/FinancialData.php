<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialData extends Model
{
    use HasFactory;

    protected $fillable = [
        'data_source_id',
        'raw_data',
        'processed_at'
    ];

    protected $casts = [
        'raw_data' => 'array',
        'processed_at' => 'datetime'
    ];

    /**
     * Bu verinin ait olduğu veri kaynağı
     */
    public function dataSource(): BelongsTo
    {
        return $this->belongsTo(DataSource::class);
    }
} 