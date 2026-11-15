<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineReceiptItem extends Model
{
    protected $fillable = [
        'medicine_receipt_id',
        'medicine_id',
        'batch_number',
        'expiry_date',
        'quantity',
        'purchase_price',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'quantity' => 'integer',
            'purchase_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(MedicineReceipt::class, 'medicine_receipt_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
