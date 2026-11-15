<?php

namespace App\Http\Controllers;

use App\Models\MedicalDocument;
use App\Models\Patient;
use App\Models\PatientDocument;
use App\Models\Visit;
use App\Services\MedicalDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MedicalDocumentController extends Controller
{
    public function __construct(protected MedicalDocumentService $documentService) {}

    public function index(Request $request): View
    {
        $type = $request->query('type');

        $query = MedicalDocument::with(['patient', 'doctor', 'visit.polyclinic'])
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        $documents = $query->paginate(15);

        return view('documents.index', compact('documents', 'type'));
    }

    public function create(Visit $visit): View
    {
        $visit->load(['patient', 'doctor', 'polyclinic', 'medicalRecord.diagnoses']);

        return view('documents.create', compact('visit'));
    }

    public function store(Request $request, Visit $visit): RedirectResponse
    {
        $data = $request->validate([
            'type' => 'required|in:surat_keterangan_sakit,surat_keterangan_sehat,surat_rujukan,surat_pengantar_lab,informed_consent',
            'notes' => 'nullable|string',
            'content_data' => 'required|array',
        ]);

        $doc = $this->documentService->createDocument($visit, $data['type'], $data['content_data'], $data['notes'] ?? null);

        return redirect()->route('documents.show', $doc->id)
            ->with('success', "Dokumen {$doc->type_label} No: {$doc->document_number} berhasil diterbitkan.");
    }

    public function show(MedicalDocument $document): View
    {
        $document->load(['patient', 'doctor', 'visit.polyclinic']);

        return view('documents.show', compact('document'));
    }

    public function print(MedicalDocument $document): View
    {
        $document->load(['patient', 'doctor', 'visit.polyclinic']);

        return view('documents.print', compact('document'));
    }

    public function uploadAttachment(Request $request, Patient $patient): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document_type' => 'required|in:radiologi,lab_eksternal,rujukan_eksternal,identitas,lainnya',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // max 10MB
            'visit_id' => 'nullable|exists:visits,id',
        ]);

        $this->documentService->uploadPatientDocument(
            $patient->id,
            $request->input('visit_id'),
            $request->file('file'),
            $request->input('title'),
            $request->input('document_type')
        );

        return back()->with('success', 'Berkas penunjang pasien berhasil diunggah.');
    }

    public function downloadAttachment(PatientDocument $document): BinaryFileResponse
    {
        // Permission check
        if (! auth()->user()->hasAnyRole(['dokter', 'perawat_bidan', 'kepala_klinik'])) {
            abort(403, 'Akses ditolak: Hanya tenaga klinis berwenang yang dapat mengunduh berkas medis.');
        }

        if (! Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File tidak ditemukan di penyimpanan server.');
        }

        return response()->download(storage_path('app/'.$document->file_path), $document->title.'.'.pathinfo($document->file_path, PATHINFO_EXTENSION));
    }
}
