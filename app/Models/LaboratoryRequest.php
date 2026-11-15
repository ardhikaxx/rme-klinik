<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaboratoryRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'visit_id',
        'doctor_id',
        'request_time',
        'clinical_notes',
        'sample_collected_at',
        'sample_collector',
        'status', // menunggu_sampel, diproses, selesai, dibatalkan
    ];

    protected function casts(): array
    {
        return [
            'request_time' => 'datetime',
            'sample_collected_at' => 'datetime',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LaboratoryRequestItem::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(LaboratoryResult::class);
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'menunggu_sampel' => 'Menunggu Sampel',
            'diproses' => 'Sedang Diproses',
            'selesai' => 'Selesai (Hasil Valid)',
            'dibatalkan' => 'Dibatalkan',
        ];

        return $labels[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'menunggu_sampel' => 'bg-warning text-dark',
            'diproses' => 'bg-info text-dark',
            'selesai' => 'bg-success',
            'dibatalkan' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public static function generateNextRequestNumber(): string
    {
        $today = date('Ymd');
        $prefix = "LAB-{$today}-";

        $latest = static::where('request_number', 'like', "{$prefix}%")
            ->orderBy('request_number', 'desc')
            ->first();

        if ($latest) {
            $numberPart = (int) substr($latest->request_number, strlen($prefix));
            $nextNumber = str_pad($numberPart + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}{$nextNumber}";
    }
}
