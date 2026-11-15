<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Polyclinic extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type', // umum, gigi, kia, spesialis
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function doctorSchedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function isDental(): bool
    {
        return $this->type === 'gigi';
    }

    public function isMaternal(): bool
    {
        return $this->type === 'kia';
    }
}
