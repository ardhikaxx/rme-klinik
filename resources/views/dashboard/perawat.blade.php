@extends('layouts.app')

@section('title', 'Dashboard Perawat')
@section('page_title', 'Dashboard Pelayanan Keperawatan & Bidan')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Keperawatan</li>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $waitingAssessment->count() }}</div>
                <div class="stat-label">Pasien Menunggu Pengkajian Awal Hari Ini</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fa-solid fa-check-double"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $completedAssessment->count() }}</div>
                <div class="stat-label">Pengkajian Telah Diselesaikan Hari Ini</div>
            </div>
        </div>
    </div>
</div>

{{-- Antrean Pengkajian Awal --}}
<div class="card-healthcare mb-4">
    <div class="card-header">
        <span class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-clipboard-list text-warning"></i> Daftar Pasien Menunggu Pengkajian Awal (Triage)
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-healthcare mb-0">
                <thead>
                    <tr>
                        <th>Antrean</th>
                        <th>Jam Daftar</th>
                        <th>No. RM & Nama Pasien</th>
                        <th>Umur / JK</th>
                        <th>Poliklinik Tujuan</th>
                        <th>Dokter Tujuan</th>
                        <th>Peringatan Alergi</th>
                        <th class="text-center">Aksi Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($waitingAssessment as $v)
                        <tr>
                            <td><strong class="badge bg-light text-dark border fs-6">{{ $v->queue_number ?? '-' }}</strong></td>
                            <td>{{ $v->registration_date->format('H:i') }} WIB</td>
                            <td>
                                <div class="fw-bold">{{ $v->patient->name }}</div>
                                <small class="text-muted">{{ $v->patient->medical_record_number }}</small>
                            </td>
                            <td>{{ $v->patient->age }} thn ({{ $v->patient->gender }})</td>
                            <td><span class="badge bg-info text-dark">{{ $v->polyclinic->name }}</span></td>
                            <td>{{ $v->doctor->formatted_name }}</td>
                            <td>
                                @if($v->patient->allergies && $v->patient->allergies->count() > 0)
                                    <span class="badge bg-danger">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                        {{ $v->patient->allergies->pluck('allergen_name')->implode(', ') }}
                                    </span>
                                @else
                                    <span class="text-muted small">Tidak ada alergi</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('nursing.create', $v->id) }}" class="btn btn-sm btn-rme-primary">
                                    <i class="fa-solid fa-file-waveform me-1"></i> Mulai Pengkajian
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-clipboard-check fa-2x text-muted mb-2 d-block"></i>
                                Tidak ada antrean pengkajian awal saat ini. Seluruh pasien telah diperiksa perawat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Riwayat Pengkajian Selesai Hari Ini --}}
<div class="card-healthcare">
    <div class="card-header">
        <span class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-check text-success"></i> Pengkajian Awal Selesai Hari Ini
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-healthcare mb-0">
                <thead>
                    <tr>
                        <th>No. RM & Nama Pasien</th>
                        <th>Poliklinik</th>
                        <th>Tekanan Darah</th>
                        <th>Suhu</th>
                        <th>Nadi</th>
                        <th>BMI / Kategori</th>
                        <th>Status Pasien</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($completedAssessment as $v)
                        @php $na = $v->nursingAssessment; @endphp
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $v->patient->name }}</div>
                                <small class="text-muted">{{ $v->patient->medical_record_number }}</small>
                            </td>
                            <td>{{ $v->polyclinic->name }}</td>
                            <td>{{ $na ? $na->blood_pressure : '-' }}</td>
                            <td>{{ $na ? $na->temperature . ' °C' : '-' }}</td>
                            <td>{{ $na ? $na->pulse_rate . ' bpm' : '-' }}</td>
                            <td>
                                @if($na && $na->bmi)
                                    <span class="badge {{ $na->bmi_badge_class }}">{{ $na->bmi }} ({{ $na->bmi_category }})</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td><span class="badge {{ $v->status_badge_class }}">{{ $v->status_label }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-3 text-muted">Belum ada pengkajian perawat yang selesai hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
