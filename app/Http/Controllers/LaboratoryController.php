<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\LaboratoryRequest;
use App\Models\Visit;
use App\Services\LaboratoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaboratoryController extends Controller
{
    public function __construct(protected LaboratoryService $labService) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');

        $query = LaboratoryRequest::with(['visit.patient', 'doctor', 'items.laboratory'])
            ->orderBy('request_time', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(15);
        $testsMaster = Laboratory::where('is_active', true)->get();

        return view('laboratory.index', compact('requests', 'testsMaster', 'status'));
    }

    public function show(LaboratoryRequest $request): View
    {
        $request->load([
            'visit.patient.allergies',
            'doctor',
            'items.laboratory',
            'results.laboratory',
            'results.tester',
            'results.verifier',
        ]);

        return view('laboratory.show', compact('request'));
    }

    public function storeRequest(Request $request, Visit $visit): RedirectResponse
    {
        $data = $request->validate([
            'tests' => 'required|array|min:1',
            'tests.*' => 'exists:laboratories,id',
            'clinical_notes' => 'nullable|string',
        ]);

        $labReq = $this->labService->createRequest($visit, $data['tests'], $data['clinical_notes'] ?? null);

        return redirect()->route('clinical.examination', $visit->id)
            ->with('success', "Permintaan laboratorium {$labReq->request_number} berhasil dibuat.");
    }

    public function collectSample(Request $request, LaboratoryRequest $labRequest): RedirectResponse
    {
        $request->validate([
            'sample_collector' => 'required|string|max:100',
        ]);

        $this->labService->collectSample($labRequest, $request->input('sample_collector'));

        return back()->with('success', 'Waktu dan petugas pengambil sampel berhasil dicatat.');
    }

    public function saveResults(Request $request, LaboratoryRequest $labRequest): RedirectResponse
    {
        $data = $request->validate([
            'results' => 'required|array',
            'results.*.value' => 'required|string',
            'results.*.notes' => 'nullable|string',
        ]);

        $this->labService->saveResults($labRequest, $data['results']);

        return redirect()->route('laboratory.show', $labRequest->id)
            ->with('success', 'Hasil laboratorium berhasil disimpan dan diverifikasi. Hasil otomatis terhubung ke rekam medis dan billing pasien.');
    }

    public function print(LaboratoryRequest $request): View
    {
        $request->load([
            'visit.patient',
            'doctor',
            'results.laboratory',
            'results.verifier',
        ]);

        return view('laboratory.print', compact('request'));
    }
}
