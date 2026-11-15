<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaboratoryRequestItem extends Model
{
    protected $fillable = [
        'laboratory_request_id',
        'laboratory_id',
        'tariff',
    ];

    protected function casts(): array
    {
        return [
            'tariff' => 'decimal:2',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(LaboratoryRequest::class, 'laboratory_request_id');
    }

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }
}
