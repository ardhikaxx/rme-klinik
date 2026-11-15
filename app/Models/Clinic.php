<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clinic extends Model
{
    protected $fillable = [
        'code',
        'name',
        'license_number',
        'phone',
        'email',
        'address',
        'city',
        'province',
        'postal_code',
        'logo_path',
        'header_note',
        'footer_note',
    ];

    public function settings(): HasMany
    {
        return $this->hasMany(ClinicSetting::class);
    }
}
