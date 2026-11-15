<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Icd9cmCode extends Model
{
    protected $table = 'icd9cm_codes';

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
