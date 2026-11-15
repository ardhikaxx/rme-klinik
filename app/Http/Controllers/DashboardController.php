<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BillingInvoice;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $today = Carbon::today();

        // 1. Dashboard Kepala Klinik
        if ($user->hasRole('kepala_klinik')) {
            $totalPatients = Patient::count();
            $visitsToday = Visit::whereDate('registration_date', $today)->count();
            $newPatientsToday = Visit::whereDate('registration_date', $today)->where('visit_type', 'baru')->count();
            $oldPatientsToday = $visitsToday - $newPatientsToday;

            $activeExaminations = Visit::whereDate('registration_date', $today)
                ->where('status', 'pemeriksaan_dokter_berlangsung')
                ->count();

            $pendingPrescriptions = Prescription::whereIn('status', ['menunggu_verifikasi', 'diverifikasi', 'disiapkan'])->count();

            $revenueToday = (float) Payment::whereDate('payment_time', $today)->sum('amount_paid');
            $pendingBillings = (float) BillingInvoice::whereIn('status', ['belum_dibayar', 'pembayaran_sebagian'])->sum('balance_amount');

            $lowStockCount = Medicine::all()->filter(fn ($m) => $m->isLowStock())->count();
            $nearExpiryCount = MedicineBatch::where('current_stock', '>', 0)
                ->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addDays(90)])
                ->count();

            $visitsByPoly = Visit::join('polyclinics', 'visits.polyclinic_id', '=', 'polyclinics.id')
                ->whereDate('visits.registration_date', $today)
                ->select('polyclinics.name', DB::raw('count(visits.id) as total'))
                ->groupBy('polyclinics.id', 'polyclinics.name')
                ->get();

            // 7 Days Trend
            $dates = [];
            $trendVisits = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = Carbon::today()->subDays($i);
                $dates[] = $d->translatedFormat('d M');
                $trendVisits[] = Visit::whereDate('registration_date', $d)->count();
            }

            $recentLogs = AuditLog::with('user')->orderBy('created_at', 'desc')->limit(8)->get();

            return view('dashboard.kepala', compact(
                'totalPatients', 'visitsToday', 'newPatientsToday', 'oldPatientsToday',
                'activeExaminations', 'pendingPrescriptions', 'revenueToday', 'pendingBillings',
                'lowStockCount', 'nearExpiryCount', 'visitsByPoly', 'dates', 'trendVisits', 'recentLogs'
            ));
        }

        // 2. Dashboard Petugas Admisi & Kasir
        if ($user->hasRole('admisi_kasir')) {
            $visitsToday = Visit::with(['patient', 'polyclinic', 'doctor'])
                ->whereDate('registration_date', $today)
                ->orderBy('registration_date', 'desc')
                ->limit(10)
                ->get();

            $totalRegisteredToday = Visit::whereDate('registration_date', $today)->count();
            $pendingPayments = BillingInvoice::with(['patient', 'visit'])
                ->whereIn('status', ['belum_dibayar', 'pembayaran_sebagian'])
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get();

            $todayPaidSum = (float) Payment::whereDate('payment_time', $today)->sum('amount_paid');

            return view('dashboard.admisi', compact('visitsToday', 'totalRegisteredToday', 'pendingPayments', 'todayPaidSum'));
        }

        // 3. Dashboard Perawat / Bidan
        if ($user->hasRole('perawat_bidan')) {
            $waitingAssessment = Visit::with(['patient.allergies', 'polyclinic', 'doctor'])
                ->whereDate('registration_date', $today)
                ->whereIn('status', ['menunggu_pemeriksaan_awal', 'terdaftar'])
                ->orderBy('registration_date', 'asc')
                ->get();

            $completedAssessment = Visit::with(['patient', 'polyclinic'])
                ->whereDate('registration_date', $today)
                ->whereHas('nursingAssessment')
                ->orderBy('updated_at', 'desc')
                ->limit(8)
                ->get();

            return view('dashboard.perawat', compact('waitingAssessment', 'completedAssessment'));
        }

        // 4. Dashboard Dokter Pemeriksa
        if ($user->hasRole('dokter')) {
            $empId = $user->employee?->id;

            $readyForExam = Visit::with(['patient.allergies', 'polyclinic', 'nursingAssessment'])
                ->whereDate('registration_date', $today)
                ->when($empId, fn ($q) => $q->where('doctor_id', $empId))
                ->whereIn('status', ['menunggu_dokter', 'pemeriksaan_dokter_berlangsung'])
                ->orderBy('registration_date', 'asc')
                ->get();

            $finishedExam = Visit::with(['patient', 'polyclinic', 'medicalRecord.diagnoses'])
                ->whereDate('registration_date', $today)
                ->when($empId, fn ($q) => $q->where('doctor_id', $empId))
                ->whereIn('status', ['menunggu_farmasi', 'menunggu_pembayaran', 'selesai'])
                ->orderBy('updated_at', 'desc')
                ->limit(8)
                ->get();

            return view('dashboard.dokter', compact('readyForExam', 'finishedExam'));
        }

        // 5. Dashboard Farmasi / Apoteker
        if ($user->hasRole('farmasi')) {
            $pendingPrescriptions = Prescription::with(['visit.patient', 'doctor', 'items.medicine'])
                ->whereIn('status', ['menunggu_verifikasi', 'diverifikasi', 'disiapkan'])
                ->orderBy('prescription_date', 'asc')
                ->get();

            $lowStockMedicines = Medicine::all()->filter(fn ($m) => $m->isLowStock());

            $nearExpiryBatches = MedicineBatch::with('medicine')
                ->where('current_stock', '>', 0)
                ->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addDays(90)])
                ->orderBy('expiry_date', 'asc')
                ->limit(8)
                ->get();

            return view('dashboard.farmasi', compact('pendingPrescriptions', 'lowStockMedicines', 'nearExpiryBatches'));
        }

        // Fallback default
        return view('dashboard.default');
    }
}
