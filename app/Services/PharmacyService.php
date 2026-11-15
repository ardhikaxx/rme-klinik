<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\DispensingTransaction;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineReceipt;
use App\Models\MedicineStockMovement;
use App\Models\MedicineStockOpname;
use App\Models\Prescription;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PharmacyService
{
    /**
     * Create e-prescription by doctor.
     */
    public function createPrescription(Visit $visit, array $data): Prescription
    {
        return DB::transaction(function () use ($visit, $data) {
            $prescriptionNumber = Prescription::generateNextPrescriptionNumber();
            $doctorId = auth()->user()?->employee?->id ?? $visit->doctor_id;

            $prescription = Prescription::create([
                'prescription_number' => $prescriptionNumber,
                'visit_id' => $visit->id,
                'medical_record_id' => $visit->medicalRecord?->id,
                'doctor_id' => $doctorId,
                'prescription_date' => now(),
                'status' => 'menunggu_verifikasi',
                'doctor_notes' => $data['doctor_notes'] ?? null,
            ]);

            // Non-compound items
            if (! empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    if (! empty($item['medicine_id']) && ! empty($item['quantity'])) {
                        $prescription->items()->create([
                            'medicine_id' => $item['medicine_id'],
                            'quantity' => (int) $item['quantity'],
                            'dosage' => $item['dosage'] ?? '1 tab',
                            'frequency' => $item['frequency'] ?? '3x1 hari',
                            'route' => $item['route'] ?? 'oral',
                            'duration_days' => $item['duration_days'] ?? 3,
                            'instructions' => $item['instructions'] ?? 'Sesudah makan',
                            'special_notes' => $item['special_notes'] ?? null,
                        ]);
                    }
                }
            }

            // Compounds (Puyer / Racikan)
            if (! empty($data['compounds']) && is_array($data['compounds'])) {
                foreach ($data['compounds'] as $compoundData) {
                    if (! empty($compoundData['name']) && ! empty($compoundData['package_count'])) {
                        $compound = $prescription->compounds()->create([
                            'compound_name' => $compoundData['name'],
                            'dosage_form' => $compoundData['dosage_form'] ?? 'Puyer',
                            'package_count' => (int) $compoundData['package_count'],
                            'frequency' => $compoundData['frequency'] ?? '3x1 bungkus',
                            'instructions' => $compoundData['instructions'] ?? 'Sesudah makan',
                            'special_notes' => $compoundData['special_notes'] ?? null,
                        ]);

                        if (! empty($compoundData['ingredients']) && is_array($compoundData['ingredients'])) {
                            foreach ($compoundData['ingredients'] as $ing) {
                                if (! empty($ing['medicine_id']) && ! empty($ing['quantity'])) {
                                    $compound->items()->create([
                                        'medicine_id' => $ing['medicine_id'],
                                        'quantity' => (float) $ing['quantity'],
                                        'unit' => $ing['unit'] ?? 'tab',
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            AuditLog::log('PRESCRIPTION_CREATED', 'PHARMACY', "Resep elektronik {$prescription->prescription_number} dibuat untuk kunjungan {$visit->visit_number}");

            return $prescription;
        });
    }

    /**
     * Verify prescription by pharmacist.
     */
    public function verifyPrescription(Prescription $prescription, ?string $pharmacistNotes = null): Prescription
    {
        return DB::transaction(function () use ($prescription, $pharmacistNotes) {
            $prescription->update([
                'status' => 'diverifikasi',
                'pharmacist_notes' => $pharmacistNotes,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            AuditLog::log('PRESCRIPTION_VERIFIED', 'PHARMACY', "Resep {$prescription->prescription_number} diverifikasi oleh apoteker");

            return $prescription;
        });
    }

    /**
     * Dispense prescription using FEFO and row-level locking.
     */
    public function dispensePrescription(Prescription $prescription, ?string $notes = null): DispensingTransaction
    {
        return DB::transaction(function () use ($prescription, $notes) {
            if ($prescription->status === 'selesai') {
                throw ValidationException::withMessages([
                    'status' => 'Resep ini sudah didispensing sebelumnya.',
                ]);
            }

            $visit = $prescription->visit;
            $dispensingNumber = DispensingTransaction::generateNextDispensingNumber();

            $dispensingTransaction = DispensingTransaction::create([
                'dispensing_number' => $dispensingNumber,
                'prescription_id' => $prescription->id,
                'visit_id' => $visit->id,
                'dispensed_by' => auth()->id() ?? 1,
                'dispensed_at' => now(),
                'total_amount' => 0,
                'notes' => $notes,
            ]);

            $totalMedicineAmount = 0;
            $invoice = $visit->billingInvoice;

            // Process finished medicines (prescription_items)
            foreach ($prescription->items as $item) {
                $medicine = Medicine::where('id', $item->medicine_id)->lockForUpdate()->firstOrFail();
                $qtyNeeded = $item->quantity;

                // Retrieve batches with FEFO (First Expired First Out) with row-level lock
                $batches = MedicineBatch::where('medicine_id', $medicine->id)
                    ->where('current_stock', '>', 0)
                    ->where('expiry_date', '>=', Carbon::today())
                    ->orderBy('expiry_date', 'asc')
                    ->lockForUpdate()
                    ->get();

                $totalAvailable = $batches->sum('current_stock');
                if ($totalAvailable < $qtyNeeded) {
                    throw ValidationException::withMessages([
                        'stock' => "Stok obat [{$medicine->brand_name}] tidak mencukupi (Dibutuhkan: {$qtyNeeded}, Tersedia: {$totalAvailable}).",
                    ]);
                }

                $qtyRemaining = $qtyNeeded;
                foreach ($batches as $batch) {
                    if ($qtyRemaining <= 0) {
                        break;
                    }

                    $take = min($qtyRemaining, $batch->current_stock);
                    $subtotal = $take * $medicine->selling_price;

                    // Deduct batch stock
                    $stockBefore = $batch->current_stock;
                    $batch->decrement('current_stock', $take);
                    $stockAfter = $batch->fresh()->current_stock;

                    // Record Dispensing Item
                    $dispensingTransaction->items()->create([
                        'prescription_item_id' => $item->id,
                        'medicine_id' => $medicine->id,
                        'batch_id' => $batch->id,
                        'quantity' => $take,
                        'unit_price' => $medicine->selling_price,
                        'subtotal' => $subtotal,
                    ]);

                    // Record Stock Movement Card
                    MedicineStockMovement::create([
                        'medicine_id' => $medicine->id,
                        'batch_id' => $batch->id,
                        'movement_type' => 'dispense',
                        'quantity' => -$take,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reference_type' => DispensingTransaction::class,
                        'reference_id' => $dispensingTransaction->id,
                        'notes' => "Dispensing resep {$prescription->prescription_number} (Pasien: {$visit->patient->name})",
                        'created_by' => auth()->id(),
                    ]);

                    $totalMedicineAmount += $subtotal;
                    $qtyRemaining -= $take;
                }

                // Add to Billing Invoice
                if ($invoice) {
                    $invoice->items()->create([
                        'item_type' => 'obat',
                        'item_code' => $medicine->code,
                        'item_name' => "{$medicine->brand_name} ({$medicine->strength}) - {$item->dosage}",
                        'quantity' => $qtyNeeded,
                        'unit_price' => $medicine->selling_price,
                        'subtotal' => $qtyNeeded * $medicine->selling_price,
                        'reference_type' => PrescriptionItem::class,
                        'reference_id' => $item->id,
                    ]);
                }
            }

            // Process compounds if any
            foreach ($prescription->compounds as $compound) {
                $compoundTotal = 0;
                foreach ($compound->items as $compItem) {
                    $medicine = Medicine::where('id', $compItem->medicine_id)->lockForUpdate()->firstOrFail();
                    $qtyNeeded = (int) ceil($compItem->quantity * $compound->package_count);

                    $batches = MedicineBatch::where('medicine_id', $medicine->id)
                        ->where('current_stock', '>', 0)
                        ->where('expiry_date', '>=', Carbon::today())
                        ->orderBy('expiry_date', 'asc')
                        ->lockForUpdate()
                        ->get();

                    $totalAvailable = $batches->sum('current_stock');
                    if ($totalAvailable < $qtyNeeded) {
                        throw ValidationException::withMessages([
                            'stock' => "Bahan racikan [{$medicine->brand_name}] tidak mencukupi untuk {$compound->compound_name}.",
                        ]);
                    }

                    $qtyRemaining = $qtyNeeded;
                    foreach ($batches as $batch) {
                        if ($qtyRemaining <= 0) {
                            break;
                        }
                        $take = min($qtyRemaining, $batch->current_stock);
                        $subtotal = $take * $medicine->selling_price;

                        $stockBefore = $batch->current_stock;
                        $batch->decrement('current_stock', $take);
                        $stockAfter = $batch->fresh()->current_stock;

                        $dispensingTransaction->items()->create([
                            'prescription_item_id' => null,
                            'medicine_id' => $medicine->id,
                            'batch_id' => $batch->id,
                            'quantity' => $take,
                            'unit_price' => $medicine->selling_price,
                            'subtotal' => $subtotal,
                        ]);

                        MedicineStockMovement::create([
                            'medicine_id' => $medicine->id,
                            'batch_id' => $batch->id,
                            'movement_type' => 'dispense',
                            'quantity' => -$take,
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockAfter,
                            'reference_type' => DispensingTransaction::class,
                            'reference_id' => $dispensingTransaction->id,
                            'notes' => "Dispensing racikan: {$compound->compound_name}",
                            'created_by' => auth()->id(),
                        ]);

                        $compoundTotal += $subtotal;
                        $totalMedicineAmount += $subtotal;
                        $qtyRemaining -= $take;
                    }
                }

                if ($invoice) {
                    $invoice->items()->create([
                        'item_type' => 'obat',
                        'item_code' => 'RACIKAN',
                        'item_name' => "Obat Racikan: {$compound->compound_name} ({$compound->package_count} {$compound->dosage_form})",
                        'quantity' => $compound->package_count,
                        'unit_price' => $compound->package_count > 0 ? ($compoundTotal / $compound->package_count) : $compoundTotal,
                        'subtotal' => $compoundTotal,
                        'reference_type' => Prescription::class,
                        'reference_id' => $prescription->id,
                    ]);
                }
            }

            $dispensingTransaction->update(['total_amount' => $totalMedicineAmount]);

            // Update prescription status
            $prescription->update([
                'status' => 'selesai',
                'dispensed_at' => now(),
            ]);

            // Recalculate invoice totals
            if ($invoice) {
                $newTotal = $invoice->items()->sum('subtotal');
                $newFinal = max(0, $newTotal - $invoice->discount_amount);
                $newBalance = max(0, $newFinal - $invoice->paid_amount);

                $invoice->update([
                    'total_amount' => $newTotal,
                    'final_amount' => $newFinal,
                    'balance_amount' => $newBalance,
                ]);
            }

            // Move visit status to awaiting payment
            app(VisitService::class)->updateStatus(
                $visit,
                'menunggu_pembayaran',
                'Obat resep telah diserahkan oleh apoteker'
            );

            AuditLog::log('PRESCRIPTION_DISPENSED', 'PHARMACY', "Resep {$prescription->prescription_number} berhasil didispensing dengan no transaksi {$dispensingNumber}");

            return $dispensingTransaction;
        });
    }

    /**
     * Record receipt of goods / medicine purchase invoice.
     */
    public function recordReceipt(array $data): MedicineReceipt
    {
        return DB::transaction(function () use ($data) {
            $receiptNumber = MedicineReceipt::generateNextReceiptNumber();

            $receipt = MedicineReceipt::create([
                'receipt_number' => $receiptNumber,
                'supplier_id' => $data['supplier_id'],
                'invoice_number' => $data['invoice_number'],
                'received_date' => $data['received_date'] ?? now()->toDateString(),
                'total_amount' => 0,
                'notes' => $data['notes'] ?? null,
                'received_by' => auth()->id() ?? 1,
            ]);

            $totalAmount = 0;

            foreach ($data['items'] as $item) {
                $medicine = Medicine::findOrFail($item['medicine_id']);
                $qty = (int) $item['quantity'];
                $price = (float) $item['purchase_price'];
                $subtotal = $qty * $price;

                $receipt->items()->create([
                    'medicine_id' => $medicine->id,
                    'batch_number' => $item['batch_number'],
                    'expiry_date' => $item['expiry_date'],
                    'quantity' => $qty,
                    'purchase_price' => $price,
                    'subtotal' => $subtotal,
                ]);

                // Find or create batch
                $batch = MedicineBatch::firstOrCreate(
                    [
                        'medicine_id' => $medicine->id,
                        'batch_number' => $item['batch_number'],
                    ],
                    [
                        'expiry_date' => $item['expiry_date'],
                        'current_stock' => 0,
                        'purchase_price' => $price,
                        'supplier_id' => $data['supplier_id'],
                    ]
                );

                $stockBefore = $batch->current_stock;
                $batch->increment('current_stock', $qty);
                $stockAfter = $batch->fresh()->current_stock;

                // Record movement
                MedicineStockMovement::create([
                    'medicine_id' => $medicine->id,
                    'batch_id' => $batch->id,
                    'movement_type' => 'receipt',
                    'quantity' => $qty,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_type' => MedicineReceipt::class,
                    'reference_id' => $receipt->id,
                    'notes' => "Penerimaan faktur {$data['invoice_number']}",
                    'created_by' => auth()->id(),
                ]);

                $totalAmount += $subtotal;
            }

            $receipt->update(['total_amount' => $totalAmount]);

            AuditLog::log('MEDICINE_RECEIPT_CREATED', 'PHARMACY', "Penerimaan obat {$receipt->receipt_number} faktur {$receipt->invoice_number} berhasil dicatat");

            return $receipt;
        });
    }

    /**
     * Conduct Stock Opname and adjustment.
     */
    public function approveStockOpname(MedicineStockOpname $opname): void
    {
        DB::transaction(function () use ($opname) {
            if ($opname->status === 'approved') {
                return;
            }

            foreach ($opname->items as $item) {
                $batch = MedicineBatch::where('id', $item->batch_id)->lockForUpdate()->first();
                if ($batch && $item->difference != 0) {
                    $stockBefore = $batch->current_stock;
                    $batch->update(['current_stock' => $item->physical_stock]);
                    $stockAfter = $item->physical_stock;

                    MedicineStockMovement::create([
                        'medicine_id' => $item->medicine_id,
                        'batch_id' => $batch->id,
                        'movement_type' => 'opname',
                        'quantity' => $item->difference,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reference_type' => MedicineStockOpname::class,
                        'reference_id' => $opname->id,
                        'notes' => "Penyesuaian stok opname: {$item->reason}",
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            $opname->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            AuditLog::log('STOCK_OPNAME_APPROVED', 'PHARMACY', "Stock opname {$opname->opname_number} disetujui dan stok disesuaikan");
        });
    }
}
