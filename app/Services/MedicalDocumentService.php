<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\MedicalDocument;
use App\Models\PatientDocument;
use App\Models\Visit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class MedicalDocumentService
{
    /**
     * Generate medical document (sick leave, healthy certificate, referral, etc.)
     */
    public function createDocument(Visit $visit, string $type, array $contentData, ?string $notes = null): MedicalDocument
    {
        return DB::transaction(function () use ($visit, $type, $contentData, $notes) {
            $typePrefix = match ($type) {
                'surat_keterangan_sakit' => 'SKS',
                'surat_keterangan_sehat' => 'SKH',
                'surat_rujukan' => 'SRJ',
                'surat_pengantar_lab' => 'SPL',
                'informed_consent' => 'IC',
                default => 'MED',
            };

            $docNumber = MedicalDocument::generateNextDocumentNumber($typePrefix);
            $doctorId = auth()->user()?->employee?->id ?? $visit->doctor_id;

            $document = MedicalDocument::create([
                'document_number' => $docNumber,
                'visit_id' => $visit->id,
                'patient_id' => $visit->patient_id,
                'doctor_id' => $doctorId,
                'type' => $type,
                'issue_date' => now()->toDateString(),
                'content_data' => $contentData,
                'notes' => $notes,
            ]);

            AuditLog::log('MEDICAL_DOCUMENT_CREATED', 'DOCUMENTS', "Dokumen {$document->type_label} No {$docNumber} diterbitkan untuk pasien {$visit->patient->name}");

            return $document;
        });
    }

    /**
     * Securely store patient attachment document (radiology, external lab, ID).
     */
    public function uploadPatientDocument(int $patientId, ?int $visitId, UploadedFile $file, string $title, string $documentType): PatientDocument
    {
        $path = $file->store("patient_docs/{$patientId}", 'local');

        $doc = PatientDocument::create([
            'patient_id' => $patientId,
            'visit_id' => $visitId,
            'title' => $title,
            'document_type' => $documentType,
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id() ?? 1,
        ]);

        AuditLog::log('PATIENT_FILE_UPLOADED', 'DOCUMENTS', "File penunjang '{$title}' ({$documentType}) diunggah untuk pasien #{$patientId}");

        return $doc;
    }
}
