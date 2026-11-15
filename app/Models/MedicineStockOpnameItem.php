<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineStockOpnameItem extends Model
{
    protected $fillable = [
        'medicine_stock_opname_id',
        'medicine_id',
        'batch_id',
        'system_stock',
        'physical_stock',
        'difference',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'system_stock' => 'integer',
            'physical_stock' => 'integer',
            'difference' => 'integer',
        ];
    }

    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(MedicineStockOpname::class, 'medicine_stock_opname_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MedicineBatch::class, 'batch_id');
    }
}
