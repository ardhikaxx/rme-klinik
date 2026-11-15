<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NursingAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'patient_id',
        'nurse_id',
        'assessment_time',
        'chief_complaint',
        'current_illness_history',
        'past_illness_history',
        'family_illness_history',
        'current_medication',
        'systolic',
        'diastolic',
        'temperature',
        'pulse_rate',
        'respiratory_rate',
        'oxygen_saturation',
        'weight',
        'height',
        'bmi',
        'waist_circumference',
        'consciousness',
        'pain_scale',
        'fall_risk',
        'nursing_care_plan',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'assessment_time' => 'datetime',
            'temperature' => 'decimal:1',
            'oxygen_saturation' => 'decimal:1',
            'weight' => 'decimal:2',
            'height' => 'decimal:2',
            'bmi' => 'decimal:1',
            'waist_circumference' => 'decimal:2',
            'pain_scale' => 'integer',
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

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'nurse_id');
    }

    public static function calculateBmi(?float $weight, ?float $height): ?float
    {
        if (! $weight || ! $height || $height <= 0) {
            return null;
        }

        $heightInMeters = $height / 100;

        return round($weight / ($heightInMeters * $heightInMeters), 1);
    }

    public function getBmiCategoryAttribute(): string
    {
        if (! $this->bmi) {
            return '-';
        }

        return match (true) {
            $this->bmi < 18.5 => 'Kurus (Underweight)',
            $this->bmi >= 18.5 && $this->bmi < 25.0 => 'Normal',
            $this->bmi >= 25.0 && $this->bmi < 30.0 => 'Kelebihan Berat Badan (Overweight)',
            default => 'Obesitas (Obese)',
        };
    }

    public function getBmiBadgeClassAttribute(): string
    {
        if (! $this->bmi) {
            return 'bg-secondary';
        }

        return match (true) {
            $this->bmi < 18.5 => 'bg-info text-dark',
            $this->bmi >= 18.5 && $this->bmi < 25.0 => 'bg-success',
            $this->bmi >= 25.0 && $this->bmi < 30.0 => 'bg-warning text-dark',
            default => 'bg-danger',
        };
    }

    public function getBloodPressureAttribute(): string
    {
        if ($this->systolic && $this->diastolic) {
            return "{$this->systolic}/{$this->diastolic} mmHg";
        }

        return '-';
    }

    public function getIsBloodPressureAbnormalAttribute(): bool
    {
        return $this->isBloodPressureAbnormal();
    }

    public function getIsTemperatureAbnormalAttribute(): bool
    {
        return $this->isTemperatureAbnormal();
    }

    public function isBloodPressureAbnormal(): bool
    {
        if (! $this->systolic || ! $this->diastolic) {
            return false;
        }

        return $this->systolic >= 140 || $this->systolic < 90 || $this->diastolic >= 90 || $this->diastolic < 60;
    }

    public function isTemperatureAbnormal(): bool
    {
        if (! $this->temperature) {
            return false;
        }

        return $this->temperature < 36.0 || $this->temperature > 37.5;
    }

    public function isPulseRateAbnormal(): bool
    {
        if (! $this->pulse_rate) {
            return false;
        }

        return $this->pulse_rate < 60 || $this->pulse_rate > 100;
    }

    public function isRespiratoryRateAbnormal(): bool
    {
        if (! $this->respiratory_rate) {
            return false;
        }

        return $this->respiratory_rate < 12 || $this->respiratory_rate > 24;
    }

    public function isOxygenSaturationAbnormal(): bool
    {
        if (! $this->oxygen_saturation) {
            return false;
        }

        return $this->oxygen_saturation < 95.0;
    }
}
