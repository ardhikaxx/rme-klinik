@extends('layouts.app')

@section('title', 'Detail Pasien - ' . $patient->name)
@section('page_title', 'Profil Pasien')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('patients.index') }}" style="color: var(--rme-info);">Pasien</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $patient->name }}</li>
@endsection

@section('page_actions')
    <div class="d-flex gap-2">
        <a href="{{ route('visits.create') }}?patient_id={{ $patient->id }}" class="btn btn-rme-primary">
            <i class="fa-solid fa-calendar-plus me-1"></i> Daftarkan Kunjungan Baru
        </a>
        <a href="{{ route('patients.card', $patient->id) }}" target="_blank" class="btn btn-outline-info">
            <i class="fa-solid fa-id-card me-1"></i> Cetak Kartu Pasien
        </a>
        <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-pen-to-square me-1"></i> Edit
        </a>
    </div>
@endsection

@section('content')
{{-- Patient Card Component with Allergies --}}
<x-patient-card :patient="$patient" />

<div class="row g-4">
    {{-- Left Column: Patient Profile & Contacts --}}
    <div class="col-12 col-lg-4">
        {{-- Identitas Ringkas --}}
        <div class="card-healthcare mb-4">
            <div class="card-header">
                <span class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-user text-primary"></i> Data Administratif
                </span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Nomor Rekam Medis:</span>
                        <strong class="text-primary">{{ $patient->medical_record_number }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">NIK KTP:</span>
                        <span>{{ $patient->nik ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Tempat, Tanggal Lahir:</span>
                        <span>{{ $patient->place_of_birth }}, {{ $patient->date_of_birth ? $patient->date_of_birth->translatedFormat('d-m-Y') : '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Usia:</span>
                        <span>{{ $patient->age_detail }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Golongan Darah:</span>
                        <span class="badge bg-secondary">{{ strtoupper($patient->blood_type) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Status Pernikahan:</span>
                        <span>{{ ucfirst(str_replace('_', ' ', $patient->marital_status)) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Pekerjaan:</span>
                        <span>{{ $patient->occupation ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Agama:</span>
                        <span>{{ $patient->religion ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">No. Telepon:</span>
                        <strong>{{ $patient->phone }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2">
                        <span class="text-muted">Terdaftar Sejak:</span>
                        <span>{{ $patient->created_at->translatedFormat('d F Y') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Kontak Darurat --}}
        <div class="card-healthcare mb-4">
            <div class="card-header">
                <span class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-phone-volume text-success"></i> Kontak Darurat (Keluarga)
                </span>
            </div>
            <div class="card-body">
                @forelse($patient->emergencyContacts as $contact)
                    <div class="p-2 border rounded mb-2 bg-light">
                        <div class="fw-bold">{{ $contact->name }} ({{ $contact->relationship }})</div>
                        <div class="text-muted small"><i class="fa-solid fa-phone me-1"></i> {{ $contact->phone }}</div>
                        @if($contact->address)
                            <div class="text-muted small"><i class="fa-solid fa-location-dot me-1"></i> {{ $contact->address }}</div>
                        @endif
                    </div>
                @empty
                    <p class="text-muted small mb-0">Belum ada kontak darurat yang dicatat.</p>
                @endforelse
            </div>
        </div>

        {{-- Berkas Penunjang Eksternal / Uploads --}}
        <div class="card-healthcare">
            <div class="card-header">
                <span class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-file-arrow-up text-info"></i> Berkas Penunjang Pasien
                </span>
                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                    <i class="fa-solid fa-plus me-1"></i> Unggah
                </button>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    @forelse($patient->documents as $doc)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <div>
                                <div class="fw-semibold">{{ $doc->title }}</div>
                                <span class="badge bg-light text-dark border">{{ ucfirst($doc->document_type) }}</span>
                                <small class="text-muted ms-1">{{ $doc->formatted_file_size }}</small>
                            </div>
                            <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="Unduh File">
                                <i class="fa-solid fa-download"></i>
                            </a>
                        </li>
                    @empty
                        <li class="list-group-item text-center py-3 text-muted">Belum ada berkas penunjang yang diunggah.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- Right Column: Chronological Visit History --}}
    <div class="col-12 col-lg-8">
        <div class="card-healthcare">
            <div class="card-header">
                <span class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-primary"></i> Riwayat Pelayanan & Kunjungan Pasien
                </span>
                <span class="badge bg-secondary">{{ $patient->visits->count() }} Kunjungan</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-healthcare mb-0">
                        <thead>
                            <tr>
                                <th>No. Kunjungan</th>
                                <th>Tanggal & Jam</th>
                                <th>Poliklinik</th>
                                <th>Dokter Pemeriksa</th>
                                <th>Status Pelayanan</th>
                                <th>Tagihan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($patient->visits as $v)
                                <tr>
                                    <td>
                                        <a href="{{ route('visits.show', $v->id) }}" class="fw-bold text-decoration-none">
                                            {{ $v->visit_number }}
                                        </a>
                                        <small class="d-block text-muted">{{ $v->queue_number ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $v->registration_date->format('d-m-Y') }}</div>
                                        <small class="text-muted">{{ $v->registration_date->format('H:i') }} WIB</small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $v->polyclinic->name }}</span></td>
                                    <td>{{ $v->doctor->formatted_name }}</td>
                                    <td><span class="badge {{ $v->status_badge_class }}">{{ $v->status_label }}</span></td>
                                    <td>
                                        @if($v->billingInvoice)
                                            <span class="badge {{ $v->billingInvoice->status_badge_class }}">
                                                {{ $v->billingInvoice->status_label }}
                                            </span>
                                            <div class="small fw-semibold mt-1">Rp{{ number_format($v->billingInvoice->final_amount, 0, ',', '.') }}</div>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('visits.show', $v->id) }}" class="btn btn-sm btn-rme-outline py-0 px-2">
                                            <i class="fa-solid fa-folder-open me-1"></i> Buka
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-calendar-xmark fa-2x text-muted mb-2 d-block"></i>
                                        Pasien belum memiliki riwayat kunjungan di klinik ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Upload Berkas Penunjang --}}
<div class="modal fade" id="uploadDocModal" tabindex="-1" aria-labelledby="uploadDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="uploadDocModalLabel">
                    <i class="fa-solid fa-file-arrow-up text-info me-2"></i> Unggah Berkas Penunjang Pasien
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('documents.upload', $patient->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul Dokumen <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required placeholder="Contoh: Rontgen Thorax AP, Hasil Lab Prodia">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jenis Dokumen <span class="text-danger">*</span></label>
                        <select name="document_type" class="form-select" required>
                            <option value="radiologi">Foto Radiologi / Rontgen / USG</option>
                            <option value="lab_eksternal">Hasil Laboratorium Eksternal</option>
                            <option value="rujukan_eksternal">Surat Rujukan Balik RS</option>
                            <option value="identitas">Scan Identitas / BPJS</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih File (PDF, JPG, PNG - Maks 10MB) <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" required accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-rme-primary">Unggah Berkas</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
