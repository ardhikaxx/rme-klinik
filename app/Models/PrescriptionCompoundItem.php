<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionCompoundItem extends Model
{
    protected $fillable = [
        'prescription_compound_id',
        'medicine_id',
        'quantity',
        'unit',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
        ];
    }

    public function compound(): BelongsTo
    {
        return $this->belongsTo(PrescriptionCompound::class, 'prescription_compound_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
