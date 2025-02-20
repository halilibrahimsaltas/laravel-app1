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
        'type',
        'from_code',
        'to_code',
        'rate',
        'bid_price',
        'ask_price',
        'data',
        'params',
        'status',
        'error_message',
        'timestamp'
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'bid_price' => 'decimal:8',
        'ask_price' => 'decimal:8',
        'data' => 'json',
        'params' => 'json',
        'timestamp' => 'datetime'
    ];

    /**
     * Bu verinin ait olduğu veri kaynağı
     */
    public function dataSource(): BelongsTo
    {
        return $this->belongsTo(DataSource::class, 'data_source_id');
    }

    /**
     * Belirli bir para birimi çifti için en son kuru getir
     */
    public function scopeLatestRate($query, string $from, string $to)
    {
        return $query->where('from_code', $from)
                    ->where('to_code', $to)
                    ->latest('timestamp')
                    ->first();
    }

    /**
     * Para birimi çifti için en son veriyi getir
     */
    public static function latestForPair(string $currencyPair)
    {
        return static::where('from_code', substr($currencyPair, 0, 3))
                    ->where('to_code', substr($currencyPair, 4, 3))
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
        return $query->where('data_source_id', $sourceId);
    }

    // Veri tipine göre filtreleme
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Başarılı verileri filtreleme
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    // Son X kaydı getirme
    public function scopeLatest($query, int $limit = 1)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    // Belirli bir tarih aralığındaki verileri getirme
    public function scopeBetweenDates($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
} 