@extends('layouts.app')

@section('title', 'Dashboard Dokter Pemeriksa')
@section('page_title', 'Dashboard Pelayanan Medis Dokter (RME)')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Pelayanan Medis</li>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fa-solid fa-stethoscope"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $readyForExam->count() }}</div>
                <div class="stat-label">Pasien Siap Diperiksa Dokter Hari Ini</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fa-solid fa-file-circle-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $finishedExam->count() }}</div>
                <div class="stat-label">Pemeriksaan Dokter Selesai Hari Ini</div>
            </div>
        </div>
    </div>
</div>

{{-- Daftar Pasien Siap Diperiksa --}}
<div class="card-healthcare mb-4">
    <div class="card-header">
        <span class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-user-clock text-primary"></i> Pasien Menunggu Pemeriksaan Dokter
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-healthcare mb-0">
                <thead>
                    <tr>
                        <th>Antrean</th>
                        <th>No. RM & Nama Pasien</th>
                        <th>Poli</th>
                        <th>Keluhan Utama (Perawat)</th>
                        <th>Tanda Vital</th>
                        <th>Peringatan Alergi</th>
                        <th class="text-center">Aksi Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($readyForExam as $v)
                        @php $na = $v->nursingAssessment; @endphp
                        <tr>
                            <td><strong class="badge bg-light text-dark border fs-6">{{ $v->queue_number ?? '-' }}</strong></td>
                            <td>
                                <div class="fw-bold">{{ $v->patient->name }}</div>
                                <small class="text-muted">{{ $v->patient->medical_record_number }} &bull; {{ $v->patient->age }} thn ({{ $v->patient->gender }})</small>
                            </td>
                            <td><span class="badge bg-secondary">{{ $v->polyclinic->name }}</span></td>
                            <td style="max-width: 250px;">
                                <div class="text-truncate">{{ $na ? $na->chief_complaint : 'Belum diisi perawat' }}</div>
                            </td>
                            <td>
                                @if($na)
                                    <small class="d-block">TD: {{ $na->blood_pressure }}</small>
                                    <small class="d-block">S: {{ $na->temperature }} °C | N: {{ $na->pulse_rate }}</small>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                @if($v->patient->allergies && $v->patient->allergies->count() > 0)
                                    <span class="badge bg-danger">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                        {{ $v->patient->allergies->pluck('allergen_name')->implode(', ') }}
                                    </span>
                                @else
                                    <span class="text-muted small">Tidak ada</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('clinical.examination', $v->id) }}" class="btn btn-sm btn-rme-primary">
                                    <i class="fa-solid fa-notes-medical me-1"></i> Buka RME Pasien
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-circle-check fa-2x text-success mb-2 d-block"></i>
                                Tidak ada antrean pasien yang menunggu pemeriksaan dokter saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Riwayat Pemeriksaan Hari Ini --}}
<div class="card-healthcare">
    <div class="card-header">
        <span class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-success"></i> Pasien Selesai Diperiksa Hari Ini
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-healthcare mb-0">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>No. RM & Nama Pasien</th>
                        <th>Diagnosis ICD-10 (Primer)</th>
                        <th>Status SOAP</th>
                        <th>Status Kunjungan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($finishedExam as $v)
                        @php $mr = $v->medicalRecord; @endphp
                        <tr>
                            <td>{{ $mr ? $mr->updated_at->format('H:i') : '-' }} WIB</td>
                            <td>
                                <div class="fw-bold">{{ $v->patient->name }}</div>
                                <small class="text-muted">{{ $v->patient->medical_record_number }}</small>
                            </td>
                            <td>
                                @if($mr && $mr->primaryDiagnosis)
                                    <span class="badge bg-info text-dark">
                                        [{{ $mr->primaryDiagnosis->icd10_code }}] {{ $mr->primaryDiagnosis->description }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                @if($mr && $mr->status === 'final')
                                    <span class="badge bg-success"><i class="fa-solid fa-lock me-1"></i> Final & Terkunci</span>
                                @elseif($mr && $mr->status === 'addended')
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-pen-to-square me-1"></i> Addendum</span>
                                @else
                                    <span class="badge bg-secondary">Draf</span>
                                @endif
                            </td>
                            <td><span class="badge {{ $v->status_badge_class }}">{{ $v->status_label }}</span></td>
                            <td>
                                <a href="{{ route('clinical.examination', $v->id) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Buka Detail">
                                    <i class="fa-solid fa-folder-open"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-3 text-muted">Belum ada pasien yang selesai diperiksa hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
