<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Services\NursingAssessmentService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NursingAssessmentController extends Controller
{
    public function __construct(protected NursingAssessmentService $nursingService) {}

    public function index(Request $request): View
    {
        $today = Carbon::today();

        $waitingList = Visit::with(['patient.allergies', 'polyclinic', 'doctor'])
            ->whereDate('registration_date', $today)
            ->whereIn('status', ['menunggu_pemeriksaan_awal', 'terdaftar', 'pemeriksaan_awal_berlangsung'])
            ->orderBy('registration_date', 'asc')
            ->get();

        $completedList = Visit::with(['patient', 'polyclinic', 'nursingAssessment.nurse'])
            ->whereDate('registration_date', $today)
            ->whereHas('nursingAssessment')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('nursing.index', compact('waitingList', 'completedList'));
    }

    public function create(Visit $visit): View
    {
        $visit->load(['patient.allergies', 'polyclinic', 'doctor', 'nursingAssessment']);
        $assessment = $visit->nursingAssessment;

        return view('nursing.form', compact('visit', 'assessment'));
    }

    public function store(Request $request, Visit $visit): RedirectResponse
    {
        $data = $request->validate([
            'chief_complaint' => 'required|string',
            'current_illness_history' => 'nullable|string',
            'past_illness_history' => 'nullable|string',
            'family_illness_history' => 'nullable|string',
            'current_medication' => 'nullable|string',
            'systolic' => 'nullable|integer|between:50,300',
            'diastolic' => 'nullable|integer|between:30,200',
            'temperature' => 'nullable|numeric|between:30,45',
            'pulse_rate' => 'nullable|integer|between:30,250',
            'respiratory_rate' => 'nullable|integer|between:5,80',
            'oxygen_saturation' => 'nullable|numeric|between:50,100',
            'weight' => 'nullable|numeric|between:1,500',
            'height' => 'nullable|numeric|between:30,250',
            'waist_circumference' => 'nullable|numeric|between:20,200',
            'consciousness' => 'required|in:compos_mentis,apatis,somnolen,sopor,koma',
            'pain_scale' => 'required|integer|between:0,10',
            'fall_risk' => 'required|in:rendah,sedang,tinggi',
            'nursing_care_plan' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $this->nursingService->saveAssessment($visit, $data);

        return redirect()->route('nursing.index')
            ->with('success', "Pengkajian awal pasien {$visit->patient->name} berhasil disimpan. Pasien siap diperiksa dokter.");
    }
}
