<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'patient_id',
        'doctor_id',
        'examination_time',
        'subjective',
        'objective',
        'assessment',
        'plan',
        'status', // draft, final, addended
        'finalized_at',
        'finalized_by',
        'education_plan',
        'follow_up_plan',
        'next_visit_date',
    ];

    protected function casts(): array
    {
        return [
            'examination_time' => 'datetime',
            'finalized_at' => 'datetime',
            'next_visit_date' => 'date',
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

    public function finalizedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function addendums(): HasMany
    {
        return $this->hasMany(MedicalRecordAddendum::class)->orderBy('created_at', 'asc');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(MedicalRecordDiagnosis::class);
    }

    public function primaryDiagnosis()
    {
        return $this->hasOne(MedicalRecordDiagnosis::class)->where('type', 'primary');
    }

    public function secondaryDiagnoses(): HasMany
    {
        return $this->hasMany(MedicalRecordDiagnosis::class)->where('type', 'secondary');
    }

    public function procedures(): HasMany
    {
        return $this->hasMany(MedicalRecordProcedure::class);
    }

    public function getIsLockedAttribute(): bool
    {
        return $this->isLocked();
    }

    public function isLocked(): bool
    {
        return in_array($this->status, ['final', 'addended']);
    }
}
