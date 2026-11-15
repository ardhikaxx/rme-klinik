<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalRecordProcedure extends Model
{
    protected $fillable = [
        'medical_record_id',
        'icd9cm_code',
        'description',
        'tariff',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'tariff' => 'decimal:2',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }
}
