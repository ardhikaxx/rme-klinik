<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\NursingAssessment;
use App\Models\Visit;
use App\Models\VitalSign;
use Illuminate\Support\Facades\DB;

class NursingAssessmentService
{
    /**
     * Store or update nurse initial assessment.
     */
    public function saveAssessment(Visit $visit, array $data): NursingAssessment
    {
        return DB::transaction(function () use ($visit, $data) {
            $nurseId = auth()->user()?->employee?->id ?? $data['nurse_id'] ?? null;

            $weight = ! empty($data['weight']) ? (float) $data['weight'] : null;
            $height = ! empty($data['height']) ? (float) $data['height'] : null;
            $bmi = NursingAssessment::calculateBmi($weight, $height);

            $assessmentData = [
                'visit_id' => $visit->id,
                'patient_id' => $visit->patient_id,
                'nurse_id' => $nurseId,
                'assessment_time' => now(),
                'chief_complaint' => $data['chief_complaint'],
                'current_illness_history' => $data['current_illness_history'] ?? null,
                'past_illness_history' => $data['past_illness_history'] ?? null,
                'family_illness_history' => $data['family_illness_history'] ?? null,
                'current_medication' => $data['current_medication'] ?? null,
                'systolic' => ! empty($data['systolic']) ? (int) $data['systolic'] : null,
                'diastolic' => ! empty($data['diastolic']) ? (int) $data['diastolic'] : null,
                'temperature' => ! empty($data['temperature']) ? (float) $data['temperature'] : null,
                'pulse_rate' => ! empty($data['pulse_rate']) ? (int) $data['pulse_rate'] : null,
                'respiratory_rate' => ! empty($data['respiratory_rate']) ? (int) $data['respiratory_rate'] : null,
                'oxygen_saturation' => ! empty($data['oxygen_saturation']) ? (float) $data['oxygen_saturation'] : null,
                'weight' => $weight,
                'height' => $height,
                'bmi' => $bmi,
                'waist_circumference' => ! empty($data['waist_circumference']) ? (float) $data['waist_circumference'] : null,
                'consciousness' => $data['consciousness'] ?? 'compos_mentis',
                'pain_scale' => ! empty($data['pain_scale']) ? (int) $data['pain_scale'] : 0,
                'fall_risk' => $data['fall_risk'] ?? 'rendah',
                'nursing_care_plan' => $data['nursing_care_plan'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];

            $assessment = NursingAssessment::updateOrCreate(
                ['visit_id' => $visit->id],
                $assessmentData
            );

            // Also record in vital signs timeline
            VitalSign::create([
                'patient_id' => $visit->patient_id,
                'visit_id' => $visit->id,
                'recorded_by' => auth()->id(),
                'recorded_at' => now(),
                'systolic' => $assessmentData['systolic'],
                'diastolic' => $assessmentData['diastolic'],
                'temperature' => $assessmentData['temperature'],
                'pulse_rate' => $assessmentData['pulse_rate'],
                'respiratory_rate' => $assessmentData['respiratory_rate'],
                'oxygen_saturation' => $assessmentData['oxygen_saturation'],
                'weight' => $weight,
                'height' => $height,
                'bmi' => $bmi,
            ]);

            // Transition visit status to waiting for doctor (menunggu_dokter)
            app(VisitService::class)->updateStatus(
                $visit,
                'menunggu_dokter',
                'Pengkajian awal perawat telah diselesaikan'
            );

            AuditLog::log('NURSING_ASSESSMENT_SAVED', 'CLINICAL', "Pengkajian awal perawat disimpan untuk kunjungan {$visit->visit_number}");

            return $assessment;
        });
    }
}
