<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineStockOpname extends Model
{
    protected $fillable = [
        'opname_number',
        'opname_date',
        'status', // draft, approved, cancelled
        'notes',
        'conducted_by',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'opname_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function conductor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MedicineStockOpnameItem::class);
    }

    public static function generateNextOpnameNumber(): string
    {
        $today = date('Ymd');
        $prefix = "OPN-{$today}-";

        $latest = static::where('opname_number', 'like', "{$prefix}%")
            ->orderBy('opname_number', 'desc')
            ->first();

        if ($latest) {
            $numberPart = (int) substr($latest->opname_number, strlen($prefix));
            $nextNumber = str_pad($numberPart + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}{$nextNumber}";
    }
}
