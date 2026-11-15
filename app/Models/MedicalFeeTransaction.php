<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalFeeTransaction extends Model
{
    protected $fillable = [
        'billing_item_id',
        'visit_id',
        'doctor_id',
        'nurse_id',
        'doctor_fee',
        'nurse_fee',
        'clinic_fee',
        'status', // pending, paid
    ];

    protected function casts(): array
    {
        return [
            'doctor_fee' => 'decimal:2',
            'nurse_fee' => 'decimal:2',
            'clinic_fee' => 'decimal:2',
        ];
    }

    public function billingItem(): BelongsTo
    {
        return $this->belongsTo(BillingItem::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'nurse_id');
    }
}
