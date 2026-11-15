<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Odontogram extends Model
{
    protected $fillable = [
        'visit_id',
        'patient_id',
        'doctor_id',
        'notes',
    ];

    public function getGeneralNotesAttribute(): ?string
    {
        return $this->notes;
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

    public function teeth(): HasMany
    {
        return $this->hasMany(OdontogramTooth::class);
    }
}
