<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Icd10Code extends Model
{
    protected $table = 'icd10_codes';

    protected $fillable = [
        'code',
        'description',
        'category',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
