<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalRecordDiagnosis extends Model
{
    protected $fillable = [
        'medical_record_id',
        'icd10_code',
        'description',
        'type', // primary, secondary
        'notes',
    ];

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }
}
