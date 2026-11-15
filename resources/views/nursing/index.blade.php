@extends('layouts.app')

@section('title', 'Daftar Pengkajian Awal Keperawatan')
@section('page_title', 'Pengkajian Awal & Tanda Vital')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Pengkajian Keperawatan</li>
@endsection

@section('page_actions')
    <div class="text-muted small">
        <i class="fas fa-calendar-day me-1 text-primary"></i> {{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }}
    </div>
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Waiting List Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-user-clock me-2"></i>Antrean Menunggu Pengkajian Awal
                <span class="badge bg-warning text-dark ms-2">{{ $waitingList->total() }} Pasien</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 90px;" class="text-center">No. Antre</th>
                            <th>No. Kunjungan / RM</th>
                            <th>Nama Pasien</th>
                            <th>Poli & Dokter</th>
                            <th>Penjamin</th>
                            <th>Status Alergi</th>
                            <th class="text-center" style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($waitingList as $visit)
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-primary fs-6 px-3 py-2">{{ $visit->queue_number }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $visit->visit_number }}</div>
                                    <span class="badge bg-light text-primary border">{{ $visit->patient->medical_record_number }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold fs-6">{{ $visit->patient->name }}</div>
                                    <div class="text-muted small">
                                        {{ $visit->patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}, 
                                        {{ $visit->patient->age }} thn ({{ $visit->patient->date_of_birth?->format('d/m/Y') }})
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">{{ $visit->polyclinic?->name }}</div>
                                    <div class="small text-muted">{{ $visit->doctor?->formatted_name }}</div>
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
                                    <a href="{{ route('nursing.create', $visit->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-stethoscope me-1"></i>Mulai Pengkajian
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                                    <div>Tidak ada antrean pengkajian awal perawat saat ini.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3">
            <x-table-footer :paginator="$waitingList" label="pasien antrean" />
        </div>
    </div>

    <!-- Completed Today Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-secondary">
                <i class="fas fa-clipboard-check me-2 text-success"></i>Daftar Pengkajian Awal Selesai
                <span class="badge bg-success ms-2">{{ $completedList->total() }} Selesai</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable-responsive">
                    <thead class="table-light">
                        <tr>
                            <th>No. Antre</th>
                            <th>No. RM / Pasien</th>
                            <th>Poli Tujuan</th>
                            <th>TTV Terakhir (TD / Suhu / IMT)</th>
                            <th>Skala Nyeri</th>
                            <th>Perawat Pengkaji</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($completedList as $cVisit)
                            @php $ns = $cVisit->nursingAssessment; @endphp
                            <tr>
                                <td><span class="badge bg-light text-dark border">{{ $cVisit->queue_number }}</span></td>
                                <td>
                                    <div class="fw-bold">{{ $cVisit->patient->name }}</div>
                                    <div class="small text-muted">{{ $cVisit->patient->medical_record_number }}</div>
                                </td>
                                <td>{{ $cVisit->polyclinic?->name }}</td>
                                <td>
                                    @if($ns)
                                        <span class="badge {{ $ns->is_blood_pressure_abnormal ? 'bg-danger' : 'bg-light text-dark border' }}">{{ $ns->systolic }}/{{ $ns->diastolic }} mmHg</span> |
                                        <span class="badge {{ $ns->is_temperature_abnormal ? 'bg-danger' : 'bg-light text-dark border' }}">{{ $ns->temperature }}°C</span> |
                                        <span class="badge bg-info text-white">IMT: {{ $ns->bmi }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($ns)
                                        <span class="badge {{ $ns->pain_scale > 3 ? 'bg-warning text-dark' : 'bg-light text-dark border' }}">
                                            Nyeri: {{ $ns->pain_scale }}/10
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted"><i class="fas fa-user-nurse me-1"></i>{{ $ns?->nurse?->formatted_name ?? 'Perawat' }}</small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('nursing.create', $cVisit->id) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit me-1"></i>Ubah / Periksa
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3">
            <x-table-footer :paginator="$completedList" label="pasien selesai" />
        </div>
    </div>
</div>
@endsection
