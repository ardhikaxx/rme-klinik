<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FhirSyncLog extends Model
{
    protected $fillable = [
        'fhir_mapping_id',
        'resource_type',
        'payload',
        'response_code',
        'response_body',
        'error_message',
        'attempt',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'response_body' => 'array',
            'synced_at' => 'datetime',
            'attempt' => 'integer',
        ];
    }

    public function mapping(): BelongsTo
    {
        return $this->belongsTo(FhirMapping::class, 'fhir_mapping_id');
    }
}
