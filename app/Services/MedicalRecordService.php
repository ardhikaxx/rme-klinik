<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Icd10Code;
use App\Models\Icd9cmCode;
use App\Models\MedicalFeeTransaction;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordAddendum;
use App\Models\MedicalRecordProcedure;
use App\Models\ServiceTariff;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MedicalRecordService
{
    /**
     * Save draft SOAP notes.
     */
    public function saveDraft(Visit $visit, array $data): MedicalRecord
    {
        return DB::transaction(function () use ($visit, $data) {
            $record = $visit->medicalRecord;

            if ($record && $record->isLocked()) {
                throw ValidationException::withMessages([
                    'status' => 'Rekam medis ini telah difinalisasi dan terkunci. Perubahan hanya dapat dilakukan melalui Addendum.',
                ]);
            }

            $doctorId = auth()->user()?->employee?->id ?? $visit->doctor_id;

            $recordData = [
                'visit_id' => $visit->id,
                'patient_id' => $visit->patient_id,
                'doctor_id' => $doctorId,
                'examination_time' => now(),
                'subjective' => $data['subjective'],
                'objective' => $data['objective'],
                'assessment' => $data['assessment'],
                'plan' => $data['plan'],
                'status' => 'draft',
                'education_plan' => $data['education_plan'] ?? null,
                'follow_up_plan' => $data['follow_up_plan'] ?? null,
                'next_visit_date' => ! empty($data['next_visit_date']) ? $data['next_visit_date'] : null,
            ];

            $medicalRecord = MedicalRecord::updateOrCreate(
                ['visit_id' => $visit->id],
                $recordData
            );

            // Sync Diagnoses (ICD-10)
            if (isset($data['diagnoses']) && is_array($data['diagnoses'])) {
                $medicalRecord->diagnoses()->delete();
                foreach ($data['diagnoses'] as $diag) {
                    if (! empty($diag['code'])) {
                        $desc = $diag['description'] ?? null;
                        if (! $desc) {
                            $icd10 = Icd10Code::where('code', $diag['code'])->first();
                            $desc = $icd10 ? $icd10->description : $diag['code'];
                        }
                        $medicalRecord->diagnoses()->create([
                            'icd10_code' => $diag['code'],
                            'description' => $desc,
                            'type' => $diag['type'] ?? 'secondary',
                            'notes' => $diag['notes'] ?? null,
                        ]);
                    }
                }
            }

            // Sync Procedures (ICD-9-CM)
            if (isset($data['procedures']) && is_array($data['procedures'])) {
                $medicalRecord->procedures()->delete();
                foreach ($data['procedures'] as $proc) {
                    if (! empty($proc['code'])) {
                        $desc = $proc['description'] ?? null;
                        $tariff = $proc['tariff'] ?? null;
                        if (! $desc || $tariff === null) {
                            $icd9 = Icd9cmCode::where('code', $proc['code'])->first();
                            $desc = $desc ?: ($icd9 ? $icd9->description : $proc['code']);
                            if ($tariff === null) {
                                $st = ServiceTariff::where('icd9cm_code', $proc['code'])->where('is_active', true)->first();
                                $tariff = $st ? $st->tariff : 0;
                            }
                        }
                        $medicalRecord->procedures()->create([
                            'icd9cm_code' => $proc['code'],
                            'description' => $desc,
                            'tariff' => (float) $tariff,
                            'notes' => $proc['notes'] ?? null,
                        ]);
                    }
                }
            }

            // Update visit status to examination in progress
            if ($visit->status !== 'pemeriksaan_dokter_berlangsung') {
                app(VisitService::class)->updateStatus(
                    $visit,
                    'pemeriksaan_dokter_berlangsung',
                    'Dokter memulai pemeriksaan medis'
                );
            }

            AuditLog::log('MEDICAL_RECORD_SAVED', 'CLINICAL', "Draf rekam medis SOAP disimpan untuk kunjungan {$visit->visit_number}");

            return $medicalRecord;
        });
    }

    /**
     * Finalize medical examination and lock the record.
     */
    public function finalizeRecord(MedicalRecord $medicalRecord): MedicalRecord
    {
        return DB::transaction(function () use ($medicalRecord) {
            if ($medicalRecord->isLocked()) {
                throw ValidationException::withMessages([
                    'status' => 'Rekam medis ini sudah dalam status final.',
                ]);
            }

            $medicalRecord->update([
                'status' => 'final',
                'finalized_at' => now(),
                'finalized_by' => auth()->id(),
            ]);

            $visit = $medicalRecord->visit;

            // Automatically add Consultation Tariff to billing if not already present
            $invoice = $visit->billingInvoice;
            if ($invoice) {
                $consultationTariff = ServiceTariff::where('category', 'konsultasi')
                    ->where('is_active', true)
                    ->first();

                if ($consultationTariff) {
                    $hasConsultation = $invoice->items()->where('item_type', 'konsultasi')->exists();
                    if (! $hasConsultation) {
                        $item = $invoice->items()->create([
                            'item_type' => 'konsultasi',
                            'item_code' => $consultationTariff->code,
                            'item_name' => $consultationTariff->name,
                            'quantity' => 1,
                            'unit_price' => $consultationTariff->tariff,
                            'subtotal' => $consultationTariff->tariff,
                            'reference_type' => ServiceTariff::class,
                            'reference_id' => $consultationTariff->id,
                        ]);

                        // Distribute Medical Fee
                        $doctorFee = $consultationTariff->doctor_fee;
                        if ($doctorFee == 0 && $consultationTariff->doctor_fee_percent > 0) {
                            $doctorFee = ($consultationTariff->doctor_fee_percent / 100) * $consultationTariff->tariff;
                        }

                        $nurseFee = $consultationTariff->nurse_fee;
                        if ($nurseFee == 0 && $consultationTariff->nurse_fee_percent > 0) {
                            $nurseFee = ($consultationTariff->nurse_fee_percent / 100) * $consultationTariff->tariff;
                        }

                        $clinicFee = $consultationTariff->tariff - ($doctorFee + $nurseFee);

                        MedicalFeeTransaction::create([
                            'billing_item_id' => $item->id,
                            'visit_id' => $visit->id,
                            'doctor_id' => $visit->doctor_id,
                            'nurse_id' => $visit->nursingAssessment?->nurse_id,
                            'doctor_fee' => $doctorFee,
                            'nurse_fee' => $nurseFee,
                            'clinic_fee' => max(0, $clinicFee),
                            'status' => 'pending',
                        ]);
                    }
                }

                // Add Procedures to Billing
                foreach ($medicalRecord->procedures as $procedure) {
                    if ($procedure->tariff > 0) {
                        $hasProc = $invoice->items()
                            ->where('reference_type', MedicalRecordProcedure::class)
                            ->where('reference_id', $procedure->id)
                            ->exists();

                        if (! $hasProc) {
                            $procItem = $invoice->items()->create([
                                'item_type' => 'tindakan_medis',
                                'item_code' => $procedure->icd9cm_code,
                                'item_name' => $procedure->description,
                                'quantity' => 1,
                                'unit_price' => $procedure->tariff,
                                'subtotal' => $procedure->tariff,
                                'reference_type' => MedicalRecordProcedure::class,
                                'reference_id' => $procedure->id,
                            ]);

                            // 70% doctor fee, 30% clinic fee default for procedures if not configured
                            $docFee = $procedure->tariff * 0.7;
                            $clinicFee = $procedure->tariff * 0.3;

                            MedicalFeeTransaction::create([
                                'billing_item_id' => $procItem->id,
                                'visit_id' => $visit->id,
                                'doctor_id' => $visit->doctor_id,
                                'nurse_id' => null,
                                'doctor_fee' => $docFee,
                                'nurse_fee' => 0,
                                'clinic_fee' => $clinicFee,
                                'status' => 'pending',
                            ]);
                        }
                    }
                }

                // Recalculate invoice totals
                $newTotal = $invoice->items()->sum('subtotal');
                $newFinal = max(0, $newTotal - $invoice->discount_amount);
                $newBalance = max(0, $newFinal - $invoice->paid_amount);

                $invoice->update([
                    'total_amount' => $newTotal,
                    'final_amount' => $newFinal,
                    'balance_amount' => $newBalance,
                ]);
            }

            // Determine next visit status
            // If prescription exists -> menunggu_farmasi
            // Else -> menunggu_pembayaran
            $hasPrescription = $visit->prescriptions()->whereNotIn('status', ['selesai', 'dibatalkan', 'ditolak'])->exists();
            $nextStatus = $hasPrescription ? 'menunggu_farmasi' : 'menunggu_pembayaran';

            app(VisitService::class)->updateStatus(
                $visit,
                $nextStatus,
                'Pemeriksaan dokter telah difinalisasi dan catatan medis dikunci'
            );

            AuditLog::log('MEDICAL_RECORD_FINALIZED', 'CLINICAL', "Rekam medis kunjungan {$visit->visit_number} difinalisasi oleh dokter");

            return $medicalRecord;
        });
    }

    /**
     * Create an addendum for a locked medical record.
     */
    public function createAddendum(MedicalRecord $medicalRecord, string $content, string $reason): MedicalRecordAddendum
    {
        return DB::transaction(function () use ($medicalRecord, $content, $reason) {
            $doctorId = auth()->user()?->employee?->id ?? $medicalRecord->doctor_id;

            $addendum = MedicalRecordAddendum::create([
                'medical_record_id' => $medicalRecord->id,
                'doctor_id' => $doctorId,
                'addendum_content' => $content,
                'reason' => $reason,
                'created_at' => now(),
            ]);

            $medicalRecord->update(['status' => 'addended']);

            AuditLog::log('MEDICAL_RECORD_ADDENDED', 'CLINICAL', "Addendum dibuat untuk rekam medis #{$medicalRecord->id} dengan alasan: {$reason}");

            return $addendum;
        });
    }

    /**
     * Get patient's full medical timeline.
     */
    public function getPatientTimeline(int $patientId)
    {
        return Visit::where('patient_id', $patientId)
            ->with([
                'polyclinic',
                'doctor',
                'nursingAssessment',
                'medicalRecord.diagnoses',
                'medicalRecord.procedures',
                'medicalRecord.addendums.doctor',
                'prescriptions.items.medicine',
                'laboratoryRequests.results.laboratory',
                'odontogram.teeth',
                'antenatalCare',
                'childGrowth',
            ])
            ->where('status', '!=', 'dibatalkan')
            ->orderBy('registration_date', 'desc')
            ->get();
    }
}
