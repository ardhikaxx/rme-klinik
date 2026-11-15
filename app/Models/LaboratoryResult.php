<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaboratoryResult extends Model
{
    protected $fillable = [
        'laboratory_request_id',
        'laboratory_id',
        'result_value',
        'is_critical',
        'notes',
        'tested_by',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_critical' => 'boolean',
            'verified_at' => 'datetime',
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

    public function tester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tested_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
