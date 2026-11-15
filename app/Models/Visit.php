<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_number',
        'patient_id',
        'polyclinic_id',
        'doctor_id',
        'registered_by',
        'registration_date',
        'visit_type',
        'payment_type',
        'insurance_number',
        'status',
        'queue_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'registration_date' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function polyclinic(): BelongsTo
    {
        return $this->belongsTo(Polyclinic::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    public function registeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(VisitStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function nursingAssessment(): HasOne
    {
        return $this->hasOne(NursingAssessment::class);
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public function odontogram(): HasOne
    {
        return $this->hasOne(Odontogram::class);
    }

    public function antenatalCare(): HasOne
    {
        return $this->hasOne(AntenatalCare::class);
    }

    public function childGrowth(): HasOne
    {
        return $this->hasOne(ChildGrowth::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function laboratoryRequests(): HasMany
    {
        return $this->hasMany(LaboratoryRequest::class);
    }

    public function billingInvoice(): HasOne
    {
        return $this->hasOne(BillingInvoice::class);
    }

    public function medicalDocuments(): HasMany
    {
        return $this->hasMany(MedicalDocument::class);
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'terdaftar' => 'Terdaftar',
            'menunggu_pemeriksaan_awal' => 'Menunggu Pengkajian Awal',
            'pemeriksaan_awal_berlangsung' => 'Pengkajian Berlangsung',
            'menunggu_dokter' => 'Menunggu Dokter',
            'pemeriksaan_dokter_berlangsung' => 'Pemeriksaan Dokter',
            'menunggu_pemeriksaan_penunjang' => 'Menunggu Penunjang',
            'menunggu_farmasi' => 'Menunggu Farmasi',
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
        ];

        return $labels[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'terdaftar', 'menunggu_pemeriksaan_awal' => 'bg-secondary',
            'pemeriksaan_awal_berlangsung', 'menunggu_dokter' => 'bg-info text-dark',
            'pemeriksaan_dokter_berlangsung' => 'bg-primary',
            'menunggu_pemeriksaan_penunjang' => 'bg-warning text-dark',
            'menunggu_farmasi' => 'bg-warning text-dark',
            'menunggu_pembayaran' => 'bg-info',
            'selesai' => 'bg-success',
            'dibatalkan' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public static function generateNextVisitNumber(): string
    {
        $today = date('Ymd');
        $prefix = "REG-{$today}-";

        $latest = static::where('visit_number', 'like', "{$prefix}%")
            ->orderBy('visit_number', 'desc')
            ->first();

        if ($latest) {
            $numberPart = (int) substr($latest->visit_number, strlen($prefix));
            $nextNumber = str_pad($numberPart + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}{$nextNumber}";
    }
}
