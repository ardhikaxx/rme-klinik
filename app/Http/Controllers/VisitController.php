<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\Visit;
use App\Services\VisitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitController extends Controller
{
    public function __construct(protected VisitService $visitService) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['date', 'polyclinic_id', 'doctor_id', 'status', 'payment_type', 'search']);

        $visits = $this->visitService->getVisits($filters, $this->perPage($request));
        $polyclinics = Polyclinic::where('is_active', true)->get();
        $doctors = Employee::where('profession', 'dokter')->where('is_active', true)->get();

        return view('visits.index', compact('visits', 'filters', 'polyclinics', 'doctors'));
    }

    public function create(Request $request): View
    {
        $patientId = $request->query('patient_id');
        $selectedPatient = $patientId ? Patient::find($patientId) : null;

        $polyclinics = Polyclinic::where('is_active', true)->get();
        $doctors = Employee::where('profession', 'dokter')->where('is_active', true)->get();

        return view('visits.create', compact('selectedPatient', 'polyclinics', 'doctors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'polyclinic_id' => 'required|exists:polyclinics,id',
            'doctor_id' => 'required|exists:employees,id',
            'registration_date' => 'nullable|date',
            'payment_type' => 'required|in:umum,bpjs,asuransi_lain',
            'insurance_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $visit = $this->visitService->registerVisit($data);

        return redirect()->route('visits.show', $visit->id)
            ->with('success', "Kunjungan {$visit->visit_number} (Antrean: {$visit->queue_number}) berhasil didaftarkan.");
    }

    public function show(Visit $visit): View
    {
        $visit->load([
            'patient.allergies',
            'patient.emergencyContacts',
            'polyclinic',
            'doctor',
            'registeredByUser',
            'statusHistories.user',
            'nursingAssessment.nurse',
            'medicalRecord.diagnoses',
            'medicalRecord.procedures',
            'prescriptions.items.medicine',
            'billingInvoice.payments.paymentMethod',
            'laboratoryRequests.results',
        ]);

        return view('visits.show', compact('visit'));
    }

    public function updateStatus(Request $request, Visit $visit): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:terdaftar,menunggu_pemeriksaan_awal,pemeriksaan_awal_berlangsung,menunggu_dokter,pemeriksaan_dokter_berlangsung,menunggu_pemeriksaan_penunjang,menunggu_farmasi,menunggu_pembayaran,selesai,dibatalkan',
            'reason' => 'nullable|string',
        ]);

        $this->visitService->updateStatus($visit, $request->input('status'), $request->input('reason'));

        return back()->with('success', "Status kunjungan berhasil diubah menjadi {$visit->fresh()->status_label}.");
    }

    public function cancel(Request $request, Visit $visit): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $this->visitService->updateStatus($visit, 'dibatalkan', $request->input('reason'));

        return redirect()->route('visits.index')->with('success', "Kunjungan {$visit->visit_number} telah dibatalkan.");
    }
}
