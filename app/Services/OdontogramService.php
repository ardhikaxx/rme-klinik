<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Odontogram;
use App\Models\OdontogramTooth;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;

class OdontogramService
{
    /**
     * Get or initialize odontogram for visit.
     */
    public function getOdontogramForVisit(Visit $visit): Odontogram
    {
        $odontogram = Odontogram::firstOrCreate(
            ['visit_id' => $visit->id],
            [
                'patient_id' => $visit->patient_id,
                'doctor_id' => $visit->doctor_id,
            ]
        );

        if ($odontogram->wasRecentlyCreated || $odontogram->teeth()->count() === 0) {
            $allTeeth = [
                18, 17, 16, 15, 14, 13, 12, 11,
                21, 22, 23, 24, 25, 26, 27, 28,
                55, 54, 53, 52, 51, 61, 62, 63, 64, 65,
                85, 84, 83, 82, 81, 71, 72, 73, 74, 75,
                48, 47, 46, 45, 44, 43, 42, 41,
                31, 32, 33, 34, 35, 36, 37, 38,
            ];
            $now = now();
            $batch = [];
            foreach ($allTeeth as $tNum) {
                $batch[] = [
                    'odontogram_id' => $odontogram->id,
                    'tooth_number' => (string) $tNum,
                    'condition' => 'sound',
                    'surfaces' => null,
                    'treatment_notes' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            OdontogramTooth::insert($batch);
        }

        return $odontogram;
    }

    /**
     * Save tooth status update.
     */
    public function updateTooth(Odontogram $odontogram, string $toothNumber, string $condition, ?string $surfaces = null, ?string $treatmentNotes = null): OdontogramTooth
    {
        return DB::transaction(function () use ($odontogram, $toothNumber, $condition, $surfaces, $treatmentNotes) {
            $tooth = OdontogramTooth::updateOrCreate(
                [
                    'odontogram_id' => $odontogram->id,
                    'tooth_number' => $toothNumber,
                ],
                [
                    'condition' => $condition,
                    'surfaces' => $surfaces,
                    'treatment_notes' => $treatmentNotes,
                ]
            );

            AuditLog::log('ODONTOGRAM_UPDATED', 'DENTAL', "Update kondisi gigi {$toothNumber} menjadi {$condition} pada odontogram #{$odontogram->id}");

            return $tooth;
        });
    }

    /**
     * Bulk save full odontogram state.
     */
    public function saveFullOdontogram(Odontogram $odontogram, array $teethData, ?string $notes = null): Odontogram
    {
        return DB::transaction(function () use ($odontogram, $teethData, $notes) {
            if ($notes !== null) {
                $odontogram->update(['notes' => $notes]);
            }

            foreach ($teethData as $toothNumber => $data) {
                $condition = is_array($data) ? ($data['condition'] ?? 'sound') : $data;
                $surfaces = is_array($data) ? ($data['surfaces'] ?? null) : null;
                $notes = is_array($data) ? ($data['notes'] ?? null) : null;

                OdontogramTooth::updateOrCreate(
                    [
                        'odontogram_id' => $odontogram->id,
                        'tooth_number' => (string) $toothNumber,
                    ],
                    [
                        'condition' => $condition,
                        'surfaces' => $surfaces,
                        'treatment_notes' => $notes,
                    ]
                );
            }

            AuditLog::log('ODONTOGRAM_SAVED', 'DENTAL', "Odontogram lengkap disimpan untuk kunjungan {$odontogram->visit->visit_number}");

            return $odontogram->fresh('teeth');
        });
    }
}
