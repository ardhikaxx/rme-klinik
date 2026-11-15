<?php

namespace App\Services;

use App\Models\BillingInvoice;
use App\Models\DispensingItem;
use App\Models\MedicalFeeTransaction;
use App\Models\MedicalRecordDiagnosis;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Payment;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get clinic operational KPI summary.
     */
    public function getSummary(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $totalVisits = Visit::whereBetween('registration_date', [$start, $end])
            ->where('status', '!=', 'dibatalkan')
            ->count();

        $newPatients = Visit::whereBetween('registration_date', [$start, $end])
            ->where('visit_type', 'baru')
            ->where('status', '!=', 'dibatalkan')
            ->count();

        $oldPatients = $totalVisits - $newPatients;

        $completedVisits = Visit::whereBetween('registration_date', [$start, $end])
            ->where('status', 'selesai')
            ->count();

        $totalRevenue = Payment::whereBetween('payment_time', [$start, $end])
            ->sum('amount_paid');

        $pendingBilling = BillingInvoice::whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['belum_dibayar', 'pembayaran_sebagian'])
            ->sum('balance_amount');

        $lowStockMedicines = Medicine::all()->filter(fn ($m) => $m->isLowStock())->count();

        $nearExpiryBatches = MedicineBatch::where('current_stock', '>', 0)
            ->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addDays(90)])
            ->count();

        $totalDoctorFees = MedicalFeeTransaction::whereBetween('created_at', [$start, $end])
            ->sum('doctor_fee');

        return [
            'total_visits' => $totalVisits,
            'new_patients' => $newPatients,
            'old_patients' => $oldPatients,
            'completed_visits' => $completedVisits,
            'total_revenue' => (float) $totalRevenue,
            'pending_billing' => (float) $pendingBilling,
            'low_stock_medicines' => $lowStockMedicines,
            'near_expiry_batches' => $nearExpiryBatches,
            'total_doctor_fees' => (float) $totalDoctorFees,
        ];
    }

    /**
     * Visits distribution by polyclinic.
     */
    public function getVisitsByPolyclinic(string $startDate, string $endDate)
    {
        return Visit::join('polyclinics', 'visits.polyclinic_id', '=', 'polyclinics.id')
            ->whereBetween('visits.registration_date', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
            ->where('visits.status', '!=', 'dibatalkan')
            ->select('polyclinics.name as polyclinic_name', DB::raw('count(visits.id) as total'))
            ->groupBy('polyclinics.id', 'polyclinics.name')
            ->orderBy('total', 'desc')
            ->get();
    }

    /**
     * Top 10 Diseases based on ICD-10.
     */
    public function getTopDiseases(string $startDate, string $endDate, int $limit = 10)
    {
        return MedicalRecordDiagnosis::join('medical_records', 'medical_record_diagnoses.medical_record_id', '=', 'medical_records.id')
            ->whereBetween('medical_records.examination_time', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
            ->select(
                'medical_record_diagnoses.icd10_code',
                'medical_record_diagnoses.description',
                DB::raw('count(medical_record_diagnoses.id) as total_cases')
            )
            ->groupBy('medical_record_diagnoses.icd10_code', 'medical_record_diagnoses.description')
            ->orderBy('total_cases', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Daily visit trend for Chart.js.
     */
    public function getDailyTrend(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $visits = Visit::whereBetween('registration_date', [$start->startOfDay(), $end->endOfDay()])
            ->where('status', '!=', 'dibatalkan')
            ->select(DB::raw('DATE(registration_date) as date'), DB::raw('count(id) as count'))
            ->groupBy('date')
            ->pluck('count', 'date');

        $labels = [];
        $data = [];

        $period = Carbon::parse($startDate);
        while ($period->lte($end)) {
            $dateStr = $period->toDateString();
            $labels[] = $period->translatedFormat('d M');
            $data[] = $visits[$dateStr] ?? 0;
            $period->addDay();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Top consumed medicines.
     */
    public function getTopMedicines(string $startDate, string $endDate, int $limit = 10)
    {
        return DispensingItem::join('medicines', 'dispensing_items.medicine_id', '=', 'medicines.id')
            ->join('dispensing_transactions', 'dispensing_items.dispensing_transaction_id', '=', 'dispensing_transactions.id')
            ->whereBetween('dispensing_transactions.dispensed_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
            ->select(
                'medicines.brand_name',
                'medicines.generic_name',
                'medicines.dosage_form',
                DB::raw('sum(dispensing_items.quantity) as total_quantity'),
                DB::raw('sum(dispensing_items.subtotal) as total_revenue')
            )
            ->groupBy('medicines.id', 'medicines.brand_name', 'medicines.generic_name', 'medicines.dosage_form')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Medical Fee report per doctor.
     */
    public function getMedicalFees(string $startDate, string $endDate)
    {
        return MedicalFeeTransaction::with(['doctor', 'nurse', 'visit.patient'])
            ->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
