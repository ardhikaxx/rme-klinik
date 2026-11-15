<?php

namespace App\Http\Controllers;

use App\Models\FhirMapping;
use App\Models\MedicalRecord;
use App\Models\NursingAssessment;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Visit;
use App\Services\FhirService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FhirController extends Controller
{
    public function __construct(protected FhirService $fhirService) {}

    public function index(Request $request): View
    {
        $mappings = FhirMapping::with('syncLogs')
            ->orderBy('updated_at', 'desc')
            ->paginate($this->perPage($request));

        $patientsCount = Patient::count();
        $encountersCount = Visit::count();
        $conditionsCount = MedicalRecord::whereHas('diagnoses')->count();
        $observationsCount = NursingAssessment::count();
        $medicationsCount = Prescription::count();

        return view('settings.fhir', compact(
            'mappings', 'patientsCount', 'encountersCount', 'conditionsCount', 'observationsCount', 'medicationsCount'
        ));
    }

    public function preview(Request $request, string $resourceType, int $id): JsonResponse
    {
        $payload = match ($resourceType) {
            'Patient' => $this->fhirService->buildPatientPayload(Patient::findOrFail($id)),
            'Encounter' => $this->fhirService->buildEncounterPayload(Visit::findOrFail($id)),
            'Observation' => $this->fhirService->buildObservationPayload(NursingAssessment::where('visit_id', $id)->firstOrFail()),
            default => ['error' => 'Resource type preview not supported'],
        };

        return response()->json($payload);
    }

    public function stage(Request $request): RedirectResponse
    {
        $request->validate([
            'resource_type' => 'required|in:Patient,Encounter,Observation',
            'id' => 'required|integer',
        ]);

        $resType = $request->input('resource_type');
        $id = $request->input('id');

        if ($resType === 'Patient') {
            $patient = Patient::findOrFail($id);
            $payload = $this->fhirService->buildPatientPayload($patient);
            $this->fhirService->stageMapping('Patient', 'patients', $patient->id, $payload);
        } elseif ($resType === 'Encounter') {
            $visit = Visit::findOrFail($id);
            $payload = $this->fhirService->buildEncounterPayload($visit);
            $this->fhirService->stageMapping('Encounter', 'visits', $visit->id, $payload);
        }

        return back()->with('success', "Resource FHIR {$resType} berhasil disiapkan (staged) untuk sinkronisasi SATUSEHAT.");
    }
}
