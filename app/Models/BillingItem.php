<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BillingItem extends Model
{
    protected $fillable = [
        'billing_invoice_id',
        'item_type', // administrasi, konsultasi, tindakan_medis, tindakan_keperawatan, obat, laboratorium, bmhp, lainnya
        'item_code',
        'item_name',
        'quantity',
        'unit_price',
        'subtotal',
        'reference_type',
        'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'billing_invoice_id');
    }

    public function feeTransaction(): HasOne
    {
        return $this->hasOne(MedicalFeeTransaction::class);
    }
}
