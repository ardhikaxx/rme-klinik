<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AntenatalCare extends Model
{
    protected $fillable = [
        'visit_id',
        'patient_id',
        'doctor_id',
        'gravida',
        'para',
        'abortus',
        'hpht',
        'estimated_due_date',
        'gestational_age_weeks',
        'fundal_height',
        'fetal_heart_rate',
        'fetal_presentation',
        'systolic',
        'diastolic',
        'weight',
        'complaints',
        'treatment_plan',
    ];

    protected function casts(): array
    {
        return [
            'hpht' => 'date',
            'estimated_due_date' => 'date',
            'gestational_age_weeks' => 'decimal:1',
            'fundal_height' => 'decimal:1',
            'weight' => 'decimal:2',
            'gravida' => 'integer',
            'para' => 'integer',
            'abortus' => 'integer',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }
}
