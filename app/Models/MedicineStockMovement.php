<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineStockMovement extends Model
{
    protected $fillable = [
        'medicine_id',
        'batch_id',
        'movement_type', // receipt, dispense, adjustment_plus, adjustment_minus, return, opname
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
        ];
    }

    public function getQuantityInAttribute(): int
    {
        return in_array($this->movement_type, ['receipt', 'penerimaan', 'adjustment_plus']) ? $this->quantity : 0;
    }

    public function getQuantityOutAttribute(): int
    {
        return in_array($this->movement_type, ['dispense', 'dispensing', 'adjustment_minus', 'return', 'retur']) ? abs($this->quantity) : 0;
    }

    public function getBalanceAfterAttribute(): int
    {
        return $this->stock_after;
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MedicineBatch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
