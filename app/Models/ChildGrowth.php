<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildGrowth extends Model
{
    protected $fillable = [
        'visit_id',
        'patient_id',
        'doctor_id',
        'age_months',
        'weight',
        'height',
        'head_circumference',
        'immunization_status',
        'development_milestone',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'age_months' => 'integer',
            'weight' => 'decimal:2',
            'height' => 'decimal:2',
            'head_circumference' => 'decimal:1',
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
