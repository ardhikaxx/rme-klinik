<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineReceipt extends Model
{
    protected $fillable = [
        'receipt_number',
        'supplier_id',
        'invoice_number',
        'received_date',
        'total_amount',
        'notes',
        'received_by',
    ];

    protected function casts(): array
    {
        return [
            'received_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MedicineReceiptItem::class);
    }

    public static function generateNextReceiptNumber(): string
    {
        $today = date('Ymd');
        $prefix = "RCV-{$today}-";

        $latest = static::where('receipt_number', 'like', "{$prefix}%")
            ->orderBy('receipt_number', 'desc')
            ->first();

        if ($latest) {
            $numberPart = (int) substr($latest->receipt_number, strlen($prefix));
            $nextNumber = str_pad($numberPart + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}{$nextNumber}";
    }
}
