<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function __construct(protected PatientService $patientService) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'gender', 'is_verified']);
        $patients = $this->patientService->getPatients($filters, 15);

        return view('patients.index', compact('patients', 'filters'));
    }

    public function create(): View
    {
        $nextMrn = Patient::generateNextMrn();

        return view('patients.create', compact('nextMrn'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nik' => 'nullable|string|size:16',
            'name' => 'required|string|max:150',
            'place_of_birth' => 'nullable|string|max:100',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:L,P',
            'blood_type' => 'required|in:A,B,AB,O,tidak_tahu',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'required|string',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'kelurahan' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'marital_status' => 'required|in:belum_kawin,kawin,cerai_hidup,cerai_mati',
            'occupation' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:50',
            'education' => 'nullable|string|max:50',
            'is_verified' => 'nullable|boolean',
        ]);

        $data['is_verified'] = $request->boolean('is_verified', true);

        // Parse allergies array from form
        $allergies = [];
        if ($request->has('allergies')) {
            $allergies = array_filter($request->input('allergies'), fn ($a) => ! empty($a['allergen_name']));
        }

        // Parse emergency contacts array from form
        $contacts = [];
        if ($request->has('contacts')) {
            $contacts = array_filter($request->input('contacts'), fn ($c) => ! empty($c['name']) && ! empty($c['phone']));
        }

        $patient = $this->patientService->createPatient($data, $allergies, $contacts);

        return redirect()->route('patients.show', $patient->id)
            ->with('success', "Pasien {$patient->name} berhasil didaftarkan dengan No. RM {$patient->medical_record_number}.");
    }

    public function show(Patient $patient): View
    {
        $patient->load([
            'allergies',
            'emergencyContacts',
            'documents.uploader',
            'visits' => fn ($q) => $q->with(['polyclinic', 'doctor', 'billingInvoice'])->orderBy('registration_date', 'desc'),
        ]);

        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient): View
    {
        $patient->load(['allergies', 'emergencyContacts']);

        return view('patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $data = $request->validate([
            'nik' => 'nullable|string|size:16',
            'name' => 'required|string|max:150',
            'place_of_birth' => 'nullable|string|max:100',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:L,P',
            'blood_type' => 'required|in:A,B,AB,O,tidak_tahu',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'required|string',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'kelurahan' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'marital_status' => 'required|in:belum_kawin,kawin,cerai_hidup,cerai_mati',
            'occupation' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:50',
            'education' => 'nullable|string|max:50',
            'is_verified' => 'nullable|boolean',
        ]);

        $data['is_verified'] = $request->boolean('is_verified', true);

        $allergies = $request->has('allergies')
            ? array_filter($request->input('allergies'), fn ($a) => ! empty($a['allergen_name']))
            : null;

        $contacts = $request->has('contacts')
            ? array_filter($request->input('contacts'), fn ($c) => ! empty($c['name']) && ! empty($c['phone']))
            : null;

        $this->patientService->updatePatient($patient, $data, $allergies, $contacts);

        return redirect()->route('patients.show', $patient->id)
            ->with('success', 'Data identitas pasien berhasil diperbarui.');
    }

    public function digitalCard(Patient $patient): View
    {
        return view('patients.card', compact('patient'));
    }

    public function checkDuplicate(Request $request): JsonResponse
    {
        $name = $request->input('name', '');
        $dob = $request->input('date_of_birth', '');
        $nik = $request->input('nik');
        $phone = $request->input('phone');
        $excludeId = $request->input('exclude_id');

        if (empty($name) && empty($nik) && empty($phone)) {
            return response()->json(['found' => false, 'duplicates' => []]);
        }

        $duplicates = $this->patientService->findDuplicates($name, $dob, $nik, $phone, $excludeId);

        return response()->json([
            'found' => $duplicates->isNotEmpty(),
            'duplicates' => $duplicates->map(fn ($p) => [
                'id' => $p->id,
                'medical_record_number' => $p->medical_record_number,
                'name' => $p->name,
                'nik' => $p->nik,
                'date_of_birth' => $p->date_of_birth?->format('d-m-Y'),
                'phone' => $p->phone,
                'address' => $p->full_address,
            ]),
        ]);
    }

    public function searchAjax(Request $request): JsonResponse
    {
        $term = trim($request->input('q', ''));
        $patients = Patient::query()
            ->where('medical_record_number', 'like', "%{$term}%")
            ->orWhere('name', 'like', "%{$term}%")
            ->orWhere('nik', 'like', "%{$term}%")
            ->orWhere('phone', 'like', "%{$term}%")
            ->limit(20)
            ->get();

        return response()->json([
            'results' => $patients->map(fn ($p) => [
                'id' => $p->id,
                'text' => "[{$p->medical_record_number}] {$p->name} - {$p->nik} ({$p->gender}, {$p->age} thn)",
                'name' => $p->name,
                'mrn' => $p->medical_record_number,
                'age' => $p->age,
                'gender' => $p->gender,
                'phone' => $p->phone,
                'address' => $p->full_address,
            ]),
        ]);
    }
}
