<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'generic_name',
        'brand_name',
        'category_id',
        'unit_id',
        'dosage_form',
        'strength',
        'purchase_price',
        'selling_price',
        'min_stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'min_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MedicineCategory::class, 'category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(MedicineUnit::class, 'unit_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(MedicineStockMovement::class);
    }

    public function getTotalStockAttribute(): int
    {
        return (int) $this->batches()->where('expiry_date', '>=', now()->toDateString())->sum('current_stock');
    }

    public function isLowStock(): bool
    {
        return $this->total_stock <= $this->min_stock;
    }

    public function getActiveBatchesFefo()
    {
        return $this->batches()
            ->where('current_stock', '>', 0)
            ->where('expiry_date', '>=', now()->toDateString())
            ->orderBy('expiry_date', 'asc')
            ->get();
    }
}
