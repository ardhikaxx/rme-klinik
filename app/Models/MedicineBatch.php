<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineBatch extends Model
{
    protected $fillable = [
        'medicine_id',
        'batch_number',
        'expiry_date',
        'current_stock',
        'purchase_price',
        'supplier_id',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'current_stock' => 'integer',
            'purchase_price' => 'decimal:2',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function isNearExpiry(int $daysThreshold = 90): bool
    {
        return ! $this->isExpired() && $this->expiry_date->diffInDays(now()) <= $daysThreshold;
    }
}
