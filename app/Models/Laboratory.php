<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laboratory extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'sample_type',
        'unit',
        'reference_range',
        'critical_low',
        'critical_high',
        'tariff',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'critical_low' => 'decimal:2',
            'critical_high' => 'decimal:2',
            'tariff' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function requestItems(): HasMany
    {
        return $this->hasMany(LaboratoryRequestItem::class);
    }
}
