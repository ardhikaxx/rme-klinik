<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalFeeRule extends Model
{
    protected $fillable = [
        'name',
        'profession', // dokter, perawat, bidan
        'calculation_type', // fixed, percentage
        'value',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
