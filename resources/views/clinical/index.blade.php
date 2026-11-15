@extends('layouts.app')

@section('title', 'Ruang Pemeriksaan Dokter (Klinis)')
@section('page_title', 'Ruang Pemeriksaan Dokter (Klinis)')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Pemeriksaan Dokter</li>
@endsection

@section('page_actions')
    <div class="text-muted small">
        <i class="fas fa-user-md me-1 text-primary"></i> Dokter: <strong>{{ auth()->user()->employee?->formatted_name ?? auth()->user()->name }}</strong> | 
        <i class="fas fa-calendar-day ms-2 me-1 text-primary"></i> {{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }}
    </div>
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Waiting Queue -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-user-clock me-2"></i>Antrean Pasien Menunggu Pemeriksaan
                <span class="badge bg-danger ms-2">{{ $waitingList->count() }} Pasien</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;" class="text-center">No. Antre</th>
                            <th>No. RM / Pasien</th>
                            <th>Keluhan Awal & Tanda Vital Perawat</th>
                            <th>Poli Tujuan</th>
                            <th>Penjamin</th>
                            <th>Status Alergi</th>
                            <th class="text-center" style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($waitingList as $visit)
                            @php $ns = $visit->nursingAssessment; @endphp
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-primary fs-5 px-3 py-2">{{ $visit->queue_number }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold fs-6 text-dark">{{ $visit->patient->name }}</div>
                                    <div class="text-muted small">
                                        <span class="badge bg-light text-primary border">{{ $visit->patient->medical_record_number }}</span> | 
                                        {{ $visit->patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}, {{ $visit->patient->age }} thn
                                    </div>
                                </td>
                                <td>
                                    @if($ns)
                                        <div class="small fw-bold text-dark">{{ \Illuminate\Support\Str::limit($ns->chief_complaint, 60) }}</div>
                                        <div class="small text-muted mt-1">
                                            <span class="badge {{ $ns->is_blood_pressure_abnormal ? 'bg-danger' : 'bg-light text-dark border' }}">TD: {{ $ns->systolic }}/{{ $ns->diastolic }}</span>
                                            <span class="badge {{ $ns->is_temperature_abnormal ? 'bg-danger' : 'bg-light text-dark border' }}">Suhu: {{ $ns->temperature }}°C</span>
                                            <span class="badge bg-light text-dark border">Nyeri: {{ $ns->pain_scale }}/10</span>
                                        </div>
                                    @else
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Menunggu TTV Perawat</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border fw-bold">{{ $visit->polyclinic?->name }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary text-uppercase">{{ $visit->payment_type }}</span>
                                </td>
                                <td>
                                    @if($visit->patient->allergies && $visit->patient->allergies->count() > 0)
                                        <span class="badge bg-danger">
                                            <i class="fas fa-hand-dots me-1"></i>{{ $visit->patient->allergies->count() }} Alergi
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border">Tidak Ada</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('clinical.examination', $visit->id) }}" class="btn btn-primary fw-bold">
                                        <i class="fas fa-stethoscope me-1"></i>Periksa Pasien
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-user-check fa-3x text-success mb-2"></i>
                                    <div>Tidak ada antrean pasien menunggu dokter saat ini.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Completed Examinations Today -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-secondary">
                <i class="fas fa-check-double me-2 text-success"></i>Pasien Selesai Diperiksa Hari Ini
                <span class="badge bg-success ms-2">{{ $completedList->count() }} Pasien</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable-responsive">
                    <thead class="table-light">
                        <tr>
                            <th>No. Antre</th>
                            <th>No. RM / Pasien</th>
                            <th>Poli</th>
                            <th>Diagnosis Utama (ICD-10)</th>
                            <th>Status Rekam Medis</th>
                            <th>Waktu Selesai</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($completedList as $cVisit)
                            @php 
                                $mr = $cVisit->medicalRecord; 
                                $primaryDiag = $mr?->diagnoses->firstWhere('type', 'primary');
                            @endphp
                            <tr>
                                <td><span class="badge bg-light text-dark border">{{ $cVisit->queue_number }}</span></td>
                                <td>
                                    <div class="fw-bold">{{ $cVisit->patient->name }}</div>
                                    <div class="small text-muted">{{ $cVisit->patient->medical_record_number }}</div>
                                </td>
                                <td>{{ $cVisit->polyclinic?->name }}</td>
                                <td>
                                    @if($primaryDiag)
                                        <span class="badge bg-primary">[{{ $primaryDiag->icd10_code }}]</span> 
                                        <span class="small fw-bold">{{ $primaryDiag->icd10_description }}</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($mr)
                                        <span class="badge {{ $mr->status === 'final' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ strtoupper($mr->status) }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $cVisit->updated_at->format('H:i') }} WIB</td>
                                <td class="text-center">
                                    <a href="{{ route('clinical.examination', $cVisit->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>Lihat RME
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
