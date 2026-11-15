<?php

namespace App\Http\Controllers;

use App\Models\AntenatalCare;
use App\Models\AuditLog;
use App\Models\ChildGrowth;
use App\Models\Polyclinic;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaternalChildController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $polyKia = Polyclinic::where('type', 'kia')->first();

        $visits = Visit::with(['patient', 'antenatalCare', 'childGrowth'])
            ->whereDate('registration_date', $today)
            ->where('polyclinic_id', $polyKia?->id)
            ->orderBy('registration_date', 'asc')
            ->get();

        return view('kia.index', compact('visits', 'polyKia'));
    }

    public function show(Visit $visit): View
    {
        $visit->load(['patient', 'antenatalCare', 'childGrowth', 'nursingAssessment']);

        // Historical child growth records for growth chart visualization
        $historicalGrowths = ChildGrowth::where('patient_id', $visit->patient_id)
            ->orderBy('age_months', 'asc')
            ->get();

        return view('kia.show', compact('visit', 'historicalGrowths'));
    }

    public function storeAntenatal(Request $request, Visit $visit): RedirectResponse
    {
        $data = $request->validate([
            'gravida' => 'required|integer|min:1',
            'para' => 'required|integer|min:0',
            'abortus' => 'required|integer|min:0',
            'hpht' => 'nullable|date',
            'estimated_due_date' => 'nullable|date',
            'gestational_age_weeks' => 'nullable|numeric|between:1,45',
            'fundal_height' => 'nullable|numeric|between:5,50',
            'fetal_heart_rate' => 'nullable|integer|between:60,220',
            'fetal_presentation' => 'nullable|string|max:50',
            'systolic' => 'nullable|integer',
            'diastolic' => 'nullable|integer',
            'weight' => 'nullable|numeric',
            'complaints' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
        ]);

        $data['visit_id'] = $visit->id;
        $data['patient_id'] = $visit->patient_id;
        $data['doctor_id'] = auth()->user()->employee?->id ?? $visit->doctor_id;

        AntenatalCare::updateOrCreate(['visit_id' => $visit->id], $data);

        AuditLog::log('ANC_SAVED', 'MCH', "Pemeriksaan kehamilan ANC disimpan untuk kunjungan {$visit->visit_number}");

        return redirect()->route('kia.show', $visit->id)->with('success', 'Catatan ANC Ibu Hamil berhasil disimpan.');
    }

    public function storeChildGrowth(Request $request, Visit $visit): RedirectResponse
    {
        $data = $request->validate([
            'age_months' => 'required|integer|between:0,120',
            'weight' => 'required|numeric|between:1,100',
            'height' => 'required|numeric|between:30,200',
            'head_circumference' => 'nullable|numeric|between:20,80',
            'immunization_status' => 'nullable|string|max:100',
            'development_milestone' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $data['visit_id'] = $visit->id;
        $data['patient_id'] = $visit->patient_id;
        $data['doctor_id'] = auth()->user()->employee?->id ?? $visit->doctor_id;

        ChildGrowth::updateOrCreate(['visit_id' => $visit->id], $data);

        AuditLog::log('CHILD_GROWTH_SAVED', 'MCH', "Pencatatan tumbuh kembang anak disimpan untuk kunjungan {$visit->visit_number}");

        return redirect()->route('kia.show', $visit->id)->with('success', 'Pencatatan tumbuh kembang anak berhasil disimpan.');
    }

    public function growthChartData(int $patientId): JsonResponse
    {
        $records = ChildGrowth::where('patient_id', $patientId)
            ->orderBy('age_months', 'asc')
            ->get();

        return response()->json([
            'labels' => $records->map(fn ($r) => $r->age_months.' bln'),
            'weights' => $records->pluck('weight'),
            'heights' => $records->pluck('height'),
        ]);
    }
}
