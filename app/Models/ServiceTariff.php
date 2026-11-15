<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTariff extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category', // administrasi, konsultasi, tindakan_medis, tindakan_keperawatan, laboratorium, bmhp, lainnya
        'polyclinic_id',
        'icd9cm_code',
        'tariff',
        'doctor_fee',
        'nurse_fee',
        'clinic_fee',
        'doctor_fee_percent',
        'nurse_fee_percent',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tariff' => 'decimal:2',
            'doctor_fee' => 'decimal:2',
            'nurse_fee' => 'decimal:2',
            'clinic_fee' => 'decimal:2',
            'doctor_fee_percent' => 'decimal:2',
            'nurse_fee_percent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function polyclinic(): BelongsTo
    {
        return $this->belongsTo(Polyclinic::class);
    }
}
