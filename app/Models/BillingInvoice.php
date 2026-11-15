<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'visit_id',
        'patient_id',
        'total_amount',
        'discount_amount',
        'discount_reason',
        'final_amount',
        'paid_amount',
        'balance_amount',
        'status', // belum_dibayar, pembayaran_sebagian, lunas, dibatalkan, refund
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillingItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'belum_dibayar' => 'Belum Dibayar',
            'pembayaran_sebagian' => 'Sebagian',
            'lunas' => 'Lunas',
            'dibatalkan' => 'Dibatalkan',
            'refund' => 'Refund',
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'belum_dibayar' => 'bg-danger',
            'pembayaran_sebagian' => 'bg-warning text-dark',
            'lunas' => 'bg-success',
            'dibatalkan', 'refund' => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    public static function generateNextInvoiceNumber(): string
    {
        $today = date('Ymd');
        $prefix = "INV-{$today}-";

        $latest = static::where('invoice_number', 'like', "{$prefix}%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($latest) {
            $numberPart = (int) substr($latest->invoice_number, strlen($prefix));
            $nextNumber = str_pad($numberPart + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}{$nextNumber}";
    }
}
