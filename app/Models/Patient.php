<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_record_number',
        'nik',
        'name',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'blood_type',
        'address',
        'rt',
        'rw',
        'kelurahan',
        'kecamatan',
        'city',
        'province',
        'postal_code',
        'phone',
        'email',
        'marital_status',
        'occupation',
        'religion',
        'education',
        'is_verified',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_verified' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function allergies(): HasMany
    {
        return $this->hasMany(PatientAllergy::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(PatientEmergencyContact::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PatientDocument::class);
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth ? Carbon::parse($this->date_of_birth)->age : 0;
    }

    public function getAgeDetailAttribute(): string
    {
        if (! $this->date_of_birth) {
            return '-';
        }

        $dob = Carbon::parse($this->date_of_birth);
        $diff = $dob->diff(now());

        return "{$diff->y} thn {$diff->m} bln {$diff->d} hr";
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->rt ? "RT {$this->rt}" : null,
            $this->rw ? "RW {$this->rw}" : null,
            $this->kelurahan ? "Kel. {$this->kelurahan}" : null,
            $this->kecamatan ? "Kec. {$this->kecamatan}" : null,
            $this->city,
            $this->province,
            $this->postal_code,
        ]);

        return implode(', ', $parts) ?: '-';
    }

    public static function generateNextMrn(): string
    {
        $year = date('Y');
        $prefix = "RM-{$year}-";

        $latest = static::where('medical_record_number', 'like', "{$prefix}%")
            ->orderBy('medical_record_number', 'desc')
            ->first();

        if ($latest) {
            $numberPart = (int) substr($latest->medical_record_number, strlen($prefix));
            $nextNumber = str_pad($numberPart + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '000001';
        }

        return "{$prefix}{$nextNumber}";
    }
}
