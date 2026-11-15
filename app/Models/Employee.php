<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'department_id',
        'employee_number',
        'name',
        'title_prefix',
        'title_suffix',
        'gender',
        'profession',
        'specialization',
        'sip_str_number',
        'phone',
        'email',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function doctorSchedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'doctor_id');
    }

    public function getFormattedNameAttribute(): string
    {
        $prefix = $this->title_prefix ? trim($this->title_prefix).' ' : '';
        $suffix = $this->title_suffix ? ', '.trim($this->title_suffix) : '';

        return $prefix.$this->name.$suffix;
    }
}
