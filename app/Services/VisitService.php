<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\BillingInvoice;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\ServiceTariff;
use App\Models\Visit;
use App\Models\VisitStatusHistory;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VisitService
{
    /**
     * Get paginated visits with internal filters.
     */
    public function getVisits(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Visit::query()
            ->with(['patient.allergies', 'polyclinic', 'doctor', 'registeredByUser', 'billingInvoice']);

        if (! empty($filters['date'])) {
            $query->whereDate('registration_date', $filters['date']);
        } else {
            // Default to today if no date filter provided
            $query->whereDate('registration_date', Carbon::today());
        }

        if (! empty($filters['polyclinic_id'])) {
            $query->where('polyclinic_id', $filters['polyclinic_id']);
        }

        if (! empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['payment_type'])) {
            $query->where('payment_type', $filters['payment_type']);
        }

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('visit_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($p) use ($search) {
                        $p->where('name', 'like', "%{$search}%")
                            ->orWhere('medical_record_number', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderBy('registration_date', 'desc')->paginate($perPage);
    }

    /**
     * Register a patient visit.
     */
    public function registerVisit(array $data): Visit
    {
        return DB::transaction(function () use ($data) {
            $patientId = $data['patient_id'];
            $polyclinicId = $data['polyclinic_id'];
            $doctorId = $data['doctor_id'];
            $date = ! empty($data['registration_date']) ? Carbon::parse($data['registration_date']) : Carbon::now();

            // 1. Prevent duplicate active visit for the same patient in same clinic on the same day
            $existingVisit = Visit::where('patient_id', $patientId)
                ->where('polyclinic_id', $polyclinicId)
                ->whereDate('registration_date', $date->toDateString())
                ->whereNotIn('status', ['selesai', 'dibatalkan'])
                ->first();

            if ($existingVisit) {
                throw ValidationException::withMessages([
                    'polyclinic_id' => "Pasien sudah memiliki kunjungan aktif di poliklinik ini hari ini (No: {$existingVisit->visit_number}, Status: {$existingVisit->status_label}).",
                ]);
            }

            // 2. Determine visit type (baru vs lama)
            $previousVisitCount = Visit::where('patient_id', $patientId)
                ->where('status', '!=', 'dibatalkan')
                ->count();
            $visitType = $previousVisitCount > 0 ? 'lama' : 'baru';

            // 3. Generate queue number for polyclinic
            $poly = Polyclinic::findOrFail($polyclinicId);
            $polyPrefix = match ($poly->type) {
                'gigi' => 'GG',
                'kia' => 'KIA',
                default => 'UM',
            };
            $dailyCount = Visit::where('polyclinic_id', $polyclinicId)
                ->whereDate('registration_date', $date->toDateString())
                ->count();
            $queueNumber = $polyPrefix.'-'.str_pad($dailyCount + 1, 2, '0', STR_PAD_LEFT);

            // 4. Create visit
            $visitNumber = Visit::generateNextVisitNumber();
            $visit = Visit::create([
                'visit_number' => $visitNumber,
                'patient_id' => $patientId,
                'polyclinic_id' => $polyclinicId,
                'doctor_id' => $doctorId,
                'registered_by' => auth()->id() ?? 1,
                'registration_date' => $date,
                'visit_type' => $visitType,
                'payment_type' => $data['payment_type'] ?? 'umum',
                'insurance_number' => $data['insurance_number'] ?? null,
                'status' => 'menunggu_pemeriksaan_awal',
                'queue_number' => $queueNumber,
                'notes' => $data['notes'] ?? null,
            ]);

            // 5. Record initial status history
            VisitStatusHistory::create([
                'visit_id' => $visit->id,
                'status_from' => null,
                'status_to' => 'menunggu_pemeriksaan_awal',
                'changed_by' => auth()->id(),
                'reason' => 'Pendaftaran kunjungan baru',
            ]);

            // 6. Create initial billing invoice with registration fee
            $invoiceNumber = BillingInvoice::generateNextInvoiceNumber();
            $invoice = BillingInvoice::create([
                'invoice_number' => $invoiceNumber,
                'visit_id' => $visit->id,
                'patient_id' => $patientId,
                'total_amount' => 0,
                'discount_amount' => 0,
                'final_amount' => 0,
                'paid_amount' => 0,
                'balance_amount' => 0,
                'status' => 'belum_dibayar',
                'created_by' => auth()->id() ?? 1,
            ]);

            // Find administrative tariff if exists
            $adminTariff = ServiceTariff::where('category', 'administrasi')
                ->where('is_active', true)
                ->first();

            if ($adminTariff && $adminTariff->tariff > 0) {
                $invoice->items()->create([
                    'item_type' => 'administrasi',
                    'item_code' => $adminTariff->code,
                    'item_name' => $adminTariff->name,
                    'quantity' => 1,
                    'unit_price' => $adminTariff->tariff,
                    'subtotal' => $adminTariff->tariff,
                    'reference_type' => ServiceTariff::class,
                    'reference_id' => $adminTariff->id,
                ]);

                $invoice->update([
                    'total_amount' => $adminTariff->tariff,
                    'final_amount' => $adminTariff->tariff,
                    'balance_amount' => $adminTariff->tariff,
                ]);
            }

            AuditLog::log('VISIT_REGISTERED', 'VISITS', "Registrasi kunjungan: {$visit->visit_number} untuk pasien {$visit->patient->name}", null, $visit->toArray());

            return $visit;
        });
    }

    /**
     * Transition visit status.
     */
    public function updateStatus(Visit $visit, string $newStatus, ?string $reason = null): Visit
    {
        return DB::transaction(function () use ($visit, $newStatus, $reason) {
            $oldStatus = $visit->status;

            if ($oldStatus === $newStatus) {
                return $visit;
            }

            $visit->update(['status' => $newStatus]);

            VisitStatusHistory::create([
                'visit_id' => $visit->id,
                'status_from' => $oldStatus,
                'status_to' => $newStatus,
                'changed_by' => auth()->id(),
                'reason' => $reason,
            ]);

            AuditLog::log('VISIT_STATUS_CHANGED', 'VISITS', "Status kunjungan {$visit->visit_number} berubah dari {$oldStatus} menjadi {$newStatus}".($reason ? " ({$reason})" : ''));

            return $visit;
        });
    }
}
