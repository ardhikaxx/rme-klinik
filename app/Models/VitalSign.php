<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VitalSign extends Model
{
    protected $fillable = [
        'patient_id',
        'visit_id',
        'recorded_by',
        'recorded_at',
        'systolic',
        'diastolic',
        'temperature',
        'pulse_rate',
        'respiratory_rate',
        'oxygen_saturation',
        'weight',
        'height',
        'bmi',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'temperature' => 'decimal:1',
            'oxygen_saturation' => 'decimal:1',
            'weight' => 'decimal:2',
            'height' => 'decimal:2',
            'bmi' => 'decimal:1',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
