<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialData extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_id',
        'rate',
        'base_currency',
        'target_currency',
        'timestamp',
        'meta'
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'meta' => 'array',
        'timestamp' => 'datetime'
    ];

    /**
     * Bu verinin ait olduğu veri kaynağı
     */
    public function dataSource(): BelongsTo
    {
        return $this->belongsTo(DataSource::class, 'source_id');
    }

    /**
     * Belirli bir para birimi çifti için en son kuru getir
     */
    public function scopeLatestRate($query, string $base, string $target)
    {
        return $query->where('base_currency', $base)
                    ->where('target_currency', $target)
                    ->latest('timestamp')
                    ->first();
    }

    /**
     * Para birimi çifti için en son veriyi getir
     */
    public static function latestForPair(string $currencyPair)
    {
        return static::where('base_currency', substr($currencyPair, 0, 3))
                    ->where('target_currency', substr($currencyPair, 4, 3))
                    ->latest('timestamp')
                    ->first();
    }

    /**
     * Belirli bir tarih aralığındaki kurları getir
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('timestamp', [$startDate, $endDate]);
    }

    /**
     * Belirli bir kaynaktan gelen verileri filtrele
     */
    public function scopeFromSource($query, $sourceId)
    {
        return $query->where('source_id', $sourceId);
    }
} 