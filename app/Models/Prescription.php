<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_number',
        'visit_id',
        'medical_record_id',
        'doctor_id',
        'prescription_date',
        'status', // menunggu_verifikasi, diverifikasi, disiapkan, siap_diserahkan, diserahkan_sebagian, selesai, ditolak, dibatalkan
        'doctor_notes',
        'pharmacist_notes',
        'verified_by',
        'verified_at',
        'dispensed_at',
    ];

    protected function casts(): array
    {
        return [
            'prescription_date' => 'datetime',
            'verified_at' => 'datetime',
            'dispensed_at' => 'datetime',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function compounds(): HasMany
    {
        return $this->hasMany(PrescriptionCompound::class);
    }

    public function dispensingTransaction(): HasOne
    {
        return $this->hasOne(DispensingTransaction::class);
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'diverifikasi' => 'Diverifikasi',
            'disiapkan' => 'Sedang Disiapkan',
            'siap_diserahkan' => 'Siap Diserahkan',
            'diserahkan_sebagian' => 'Diserahkan Sebagian',
            'selesai' => 'Selesai (Diserahkan)',
            'ditolak' => 'Ditolak',
            'dibatalkan' => 'Dibatalkan',
        ];

        return $labels[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'menunggu_verifikasi' => 'bg-warning text-dark',
            'diverifikasi' => 'bg-info text-dark',
            'disiapkan' => 'bg-primary',
            'siap_diserahkan' => 'bg-secondary',
            'diserahkan_sebagian' => 'bg-info',
            'selesai' => 'bg-success',
            'ditolak', 'dibatalkan' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public static function generateNextPrescriptionNumber(): string
    {
        $today = date('Ymd');
        $prefix = "RX-{$today}-";

        $latest = static::where('prescription_number', 'like', "{$prefix}%")
            ->orderBy('prescription_number', 'desc')
            ->first();

        if ($latest) {
            $numberPart = (int) substr($latest->prescription_number, strlen($prefix));
            $nextNumber = str_pad($numberPart + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}{$nextNumber}";
    }
}
