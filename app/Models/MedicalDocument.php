<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalDocument extends Model
{
    protected $fillable = [
        'document_number',
        'visit_id',
        'patient_id',
        'doctor_id',
        'type', // surat_keterangan_sakit, surat_keterangan_sehat, surat_rujukan, surat_pengantar_lab, informed_consent
        'issue_date',
        'content_data',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'content_data' => 'array',
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

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'doctor_id');
    }

    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'surat_keterangan_sakit' => 'Surat Keterangan Sakit',
            'surat_keterangan_sehat' => 'Surat Keterangan Sehat',
            'surat_rujukan' => 'Surat Rujukan Medis',
            'surat_pengantar_lab' => 'Surat Pengantar Laboratorium',
            'informed_consent' => 'Persetujuan Tindakan Medis (Informed Consent)',
        ];

        return $labels[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public static function generateNextDocumentNumber(string $typePrefix = 'DOC'): string
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "{$typePrefix}/{$year}/{$month}/";

        $latest = static::where('document_number', 'like', "{$prefix}%")
            ->orderBy('document_number', 'desc')
            ->first();

        if ($latest) {
            $numberPart = (int) substr($latest->document_number, strlen($prefix));
            $nextNumber = str_pad($numberPart + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}{$nextNumber}";
    }
}
