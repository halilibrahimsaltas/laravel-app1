<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Prunable;
use App\Models\FinancialData;


class DataSource extends Model
{
    use HasFactory, Prunable;

    protected $fillable = [
        'name',
        'type',
        'config',
        'is_active',
        'last_fetch_at'
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'last_fetch_at' => 'datetime'
    ];

    /**
     * Veri kaynağına ait tüm veriler
     */
    public function data(): HasMany
    {
        return $this->hasMany(FinancialData::class);
    }

    /**
     * Scope: Aktif veri kaynakları
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Belirli bir tipteki veri kaynaklarını filtrele
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Eski kayıtları temizle
     */
    public function prunable()
    {
            return $this->data()->where('processed_at', '<=', now()->subMonth(3));
    }

    /**
     * Son çekme zamanını güncelle
     */
    public function updateLastFetch(): bool
    {
        return $this->update([
            'last_fetch_at' => now()
        ]);
    }

    /**
     * Config değerini al
     */
    public function getConfigValue(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }
}