<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DispensingTransaction extends Model
{
    protected $fillable = [
        'dispensing_number',
        'prescription_id',
        'visit_id',
        'dispensed_by',
        'dispensed_at',
        'total_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'dispensed_at' => 'datetime',
            'total_amount' => 'decimal:2',
        ];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function dispenser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DispensingItem::class);
    }

    public static function generateNextDispensingNumber(): string
    {
        $today = date('Ymd');
        $prefix = "DSP-{$today}-";

        $latest = static::where('dispensing_number', 'like', "{$prefix}%")
            ->orderBy('dispensing_number', 'desc')
            ->first();

        if ($latest) {
            $numberPart = (int) substr($latest->dispensing_number, strlen($prefix));
            $nextNumber = str_pad($numberPart + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}{$nextNumber}";
    }
}
