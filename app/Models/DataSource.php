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
        'is_active'
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean'
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
     * Eski kayıtları temizle
     */
    public function prunable()
    {
            return $this->data()->where('processed_at', '<=', now()->subMonth(3));
    }
}