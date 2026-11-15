<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Laboratory;
use App\Models\LaboratoryRequest;
use App\Models\LaboratoryResult;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;

class LaboratoryService
{
    /**
     * Create laboratory request by doctor.
     */
    public function createRequest(Visit $visit, array $testIds, ?string $clinicalNotes = null): LaboratoryRequest
    {
        return DB::transaction(function () use ($visit, $testIds, $clinicalNotes) {
            $requestNumber = LaboratoryRequest::generateNextRequestNumber();
            $doctorId = auth()->user()?->employee?->id ?? $visit->doctor_id;

            $request = LaboratoryRequest::create([
                'request_number' => $requestNumber,
                'visit_id' => $visit->id,
                'doctor_id' => $doctorId,
                'request_time' => now(),
                'clinical_notes' => $clinicalNotes,
                'status' => 'menunggu_sampel',
            ]);

            foreach ($testIds as $testId) {
                $lab = Laboratory::findOrFail($testId);
                $request->items()->create([
                    'laboratory_id' => $lab->id,
                    'tariff' => $lab->tariff,
                ]);
            }

            AuditLog::log('LAB_REQUEST_CREATED', 'LABORATORY', "Permintaan laboratorium {$request->request_number} dibuat untuk kunjungan {$visit->visit_number}");

            return $request;
        });
    }

    /**
     * Record sample collection.
     */
    public function collectSample(LaboratoryRequest $request, string $collectorName): LaboratoryRequest
    {
        return DB::transaction(function () use ($request, $collectorName) {
            $request->update([
                'sample_collected_at' => now(),
                'sample_collector' => $collectorName,
                'status' => 'diproses',
            ]);

            AuditLog::log('LAB_SAMPLE_COLLECTED', 'LABORATORY', "Sampel laboratorium untuk permintaan {$request->request_number} diambil oleh {$collectorName}");

            return $request;
        });
    }

    /**
     * Record and verify laboratory results.
     */
    public function saveResults(LaboratoryRequest $request, array $resultsData): LaboratoryRequest
    {
        return DB::transaction(function () use ($request, $resultsData) {
            $hasCritical = false;

            foreach ($resultsData as $labId => $data) {
                $lab = Laboratory::findOrFail($labId);
                $resultVal = trim($data['value'] ?? '');
                $notes = $data['notes'] ?? null;

                $isCritical = false;
                if (is_numeric($resultVal)) {
                    $numVal = (float) $resultVal;
                    if ($lab->critical_low !== null && $numVal < $lab->critical_low) {
                        $isCritical = true;
                    }
                    if ($lab->critical_high !== null && $numVal > $lab->critical_high) {
                        $isCritical = true;
                    }
                }

                if ($isCritical) {
                    $hasCritical = true;
                }

                LaboratoryResult::updateOrCreate(
                    [
                        'laboratory_request_id' => $request->id,
                        'laboratory_id' => $lab->id,
                    ],
                    [
                        'result_value' => $resultVal,
                        'is_critical' => $isCritical,
                        'notes' => $notes,
                        'tested_by' => auth()->id(),
                        'verified_by' => auth()->id(),
                        'verified_at' => now(),
                    ]
                );
            }

            $request->update(['status' => 'selesai']);

            // Add lab test items to billing invoice if not yet added
            $invoice = $request->visit->billingInvoice;
            if ($invoice) {
                foreach ($request->items as $item) {
                    $hasItem = $invoice->items()
                        ->where('reference_type', Laboratory::class)
                        ->where('reference_id', $item->laboratory_id)
                        ->exists();

                    if (! $hasItem && $item->tariff > 0) {
                        $invoice->items()->create([
                            'item_type' => 'laboratorium',
                            'item_code' => $item->laboratory->code,
                            'item_name' => "Pemeriksaan Lab: {$item->laboratory->name}",
                            'quantity' => 1,
                            'unit_price' => $item->tariff,
                            'subtotal' => $item->tariff,
                            'reference_type' => Laboratory::class,
                            'reference_id' => $item->laboratory_id,
                        ]);
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

            AuditLog::log('LAB_RESULTS_SAVED', 'LABORATORY', "Hasil laboratorium permintaan {$request->request_number} diverifikasi".($hasCritical ? ' (Terdapat nilai kritis!)' : ''));

            return $request;
        });
    }
}
