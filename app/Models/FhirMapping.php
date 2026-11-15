<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FhirMapping extends Model
{
    protected $fillable = [
        'resource_type',
        'local_table',
        'local_id',
        'fhir_id',
        'status', // pending, synced, failed
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(FhirSyncLog::class);
    }
}
