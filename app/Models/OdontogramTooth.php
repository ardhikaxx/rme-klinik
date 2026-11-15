<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdontogramTooth extends Model
{
    protected $fillable = [
        'odontogram_id',
        'tooth_number',
        'condition', // sound, caries, filling, missing, radix, crown, root_canal, bridge, unerupted
        'surfaces',
        'treatment_notes',
    ];

    public function odontogram(): BelongsTo
    {
        return $this->belongsTo(Odontogram::class);
    }

    public function getConditionLabelAttribute(): string
    {
        $labels = [
            'sound' => 'Sehat (Sound)',
            'caries' => 'Karies (Caries)',
            'filling' => 'Tambalan (Filling)',
            'missing' => 'Hilang (Missing)',
            'radix' => 'Sisa Akar (Radix)',
            'crown' => 'Mahkota (Crown)',
            'root_canal' => 'Perawatan Saluran Akar (PSA)',
            'bridge' => 'Jembatan (Bridge)',
            'unerupted' => 'Belum Tumbuh (Unerupted)',
        ];

        return $labels[$this->condition] ?? ucfirst($this->condition);
    }

    public function getConditionColorAttribute(): string
    {
        return match ($this->condition) {
            'sound' => '#28a745',
            'caries' => '#dc3545',
            'filling' => '#007bff',
            'missing' => '#6c757d',
            'radix' => '#e83e8c',
            'crown' => '#ffc107',
            'root_canal' => '#fd7e14',
            'bridge' => '#17a2b8',
            default => '#20c997',
        };
    }
}
