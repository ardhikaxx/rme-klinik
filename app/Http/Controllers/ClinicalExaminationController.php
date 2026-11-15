<?php

namespace App\Http\Controllers;

use App\Models\Icd10Code;
use App\Models\Icd9cmCode;
use App\Models\MedicalRecord;
use App\Models\ServiceTariff;
use App\Models\Visit;
use App\Services\MedicalRecordService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicalExaminationController extends Controller
{
    public function __construct(protected MedicalRecordService $medicalRecordService) {}

    public function index(Request $request): View
    {
        $today = Carbon::today();
        $doctorEmpId = auth()->user()->employee?->id;

        $waitingList = Visit::with(['patient.allergies', 'polyclinic', 'nursingAssessment'])
            ->whereDate('registration_date', $today)
            ->when($doctorEmpId, fn ($q) => $q->where('doctor_id', $doctorEmpId))
            ->whereIn('status', ['menunggu_dokter', 'pemeriksaan_dokter_berlangsung'])
            ->orderBy('registration_date', 'asc')
            ->get();

        $completedList = Visit::with(['patient', 'polyclinic', 'medicalRecord.diagnoses'])
            ->whereDate('registration_date', $today)
            ->when($doctorEmpId, fn ($q) => $q->where('doctor_id', $doctorEmpId))
            ->whereHas('medicalRecord')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('clinical.index', compact('waitingList', 'completedList'));
    }

    public function examination(Visit $visit): View
    {
        $visit->load([
            'patient.allergies',
            'polyclinic',
            'doctor',
            'nursingAssessment',
            'medicalRecord.diagnoses',
            'medicalRecord.procedures',
            'medicalRecord.addendums.doctor',
            'prescriptions.items.medicine',
            'laboratoryRequests.results',
            'odontogram.teeth',
        ]);

        $record = $visit->medicalRecord;
        $timeline = $this->medicalRecordService->getPatientTimeline($visit->patient_id);

        return view('clinical.examination', compact('visit', 'record', 'timeline'));
    }

    public function saveDraft(Request $request, Visit $visit): RedirectResponse
    {
        $data = $request->validate([
            'subjective' => 'required|string',
            'objective' => 'required|string',
            'assessment' => 'required|string',
            'plan' => 'required|string',
            'education_plan' => 'nullable|string',
            'follow_up_plan' => 'nullable|string',
            'next_visit_date' => 'nullable|date',
            'diagnoses' => 'nullable|array',
            'procedures' => 'nullable|array',
        ]);

        $this->medicalRecordService->saveDraft($visit, $data);

        return redirect()->route('clinical.examination', $visit->id)
            ->with('success', 'Draf rekam medis SOAP berhasil disimpan.');
    }

    public function finalize(Request $request, MedicalRecord $record): RedirectResponse
    {
        $this->medicalRecordService->finalizeRecord($record);

        return redirect()->route('clinical.examination', $record->visit_id)
            ->with('success', 'Pemeriksaan telah difinalisasi dan catatan medis terkunci secara permanen.');
    }

    public function addendum(Request $request, MedicalRecord $record): RedirectResponse
    {
        $data = $request->validate([
            'addendum_content' => 'required|string',
            'reason' => 'required|string',
        ]);

        $this->medicalRecordService->createAddendum($record, $data['addendum_content'], $data['reason']);

        return redirect()->route('clinical.examination', $record->visit_id)
            ->with('success', 'Catatan Addendum rekam medis berhasil ditambahkan.');
    }

    public function icd10Ajax(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));
        $codes = Icd10Code::query()
            ->where('is_active', true)
            ->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })
            ->limit(30)
            ->get(['code', 'description']);

        return response()->json([
            'results' => $codes->map(fn ($c) => [
                'id' => $c->code,
                'text' => "[{$c->code}] {$c->description}",
                'code' => $c->code,
                'description' => $c->description,
            ]),
        ]);
    }

    public function icd9Ajax(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));
        $codes = Icd9cmCode::query()
            ->where('is_active', true)
            ->where(function ($sub) use ($q) {
                $sub->where('code', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })
            ->limit(30)
            ->get(['code', 'description']);

        // Check tariff master match
        $tariffs = ServiceTariff::where('is_active', true)
            ->whereNotNull('icd9cm_code')
            ->pluck('tariff', 'icd9cm_code');

        return response()->json([
            'results' => $codes->map(fn ($c) => [
                'id' => $c->code,
                'text' => "[{$c->code}] {$c->description}".(isset($tariffs[$c->code]) ? ' - Tarif: Rp'.number_format($tariffs[$c->code], 0, ',', '.') : ' (Tarif belum diatur)'),
                'code' => $c->code,
                'description' => $c->description,
                'tariff' => $tariffs[$c->code] ?? 0,
            ]),
        ]);
    }

    public function timeline(Visit $visit): View
    {
        $timeline = $this->medicalRecordService->getPatientTimeline($visit->patient_id);

        return view('clinical.timeline', compact('visit', 'timeline'));
    }
}
