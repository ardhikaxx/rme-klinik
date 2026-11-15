<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrescriptionCompound extends Model
{
    protected $fillable = [
        'prescription_id',
        'compound_name',
        'dosage_form',
        'package_count',
        'frequency',
        'instructions',
        'special_notes',
    ];

    protected function casts(): array
    {
        return [
            'package_count' => 'integer',
        ];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionCompoundItem::class);
    }
}
