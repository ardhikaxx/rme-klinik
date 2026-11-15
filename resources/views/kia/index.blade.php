@extends('layouts.app')

@section('title', 'Poli Kesehatan Ibu dan Anak (KIA)')
@section('page_title', 'Poli Kesehatan Ibu dan Anak (KIA)')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Pelayanan KIA & KB</li>
@endsection

@section('page_actions')
    <div class="text-muted small">
        <i class="fas fa-baby me-1 text-primary"></i> Poliklinik: <strong>{{ $polyKia?->name ?? 'Poli KIA' }}</strong> | 
        <i class="fas fa-calendar-day ms-2 me-1 text-primary"></i> {{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }}
    </div>
@endsection

@section('content')
<div class="container-fluid p-0">

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-female me-2"></i>Antrean Pasien Poli KIA Hari Ini
                <span class="badge bg-primary ms-2">{{ $visits->count() }} Pasien</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;" class="text-center">No. Antre</th>
                            <th>No. RM / Nama Pasien</th>
                            <th>Usia & Gender</th>
                            <th>Jenis Layanan KIA</th>
                            <th>Bidan / Dokter</th>
                            <th>Status Pelayanan</th>
                            <th class="text-center" style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($visits as $v)
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-primary fs-5 px-3 py-2">{{ $v->queue_number }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold fs-6 text-dark">{{ $v->patient->name }}</div>
                                    <span class="badge bg-light text-primary border">{{ $v->patient->medical_record_number }}</span>
                                </td>
                                <td>
                                    {{ $v->patient->age }} tahun ({{ $v->patient->gender == 'P' ? 'Perempuan' : 'Laki-laki' }})
                                </td>
                                <td>
                                    @if($v->antenatalCare)
                                        <span class="badge bg-info text-white"><i class="fas fa-heart me-1"></i>ANC Bumil (G{{ $v->antenatalCare->gravida }}P{{ $v->antenatalCare->para }}A{{ $v->antenatalCare->abortus }})</span>
                                    @elseif($v->childGrowth)
                                        <span class="badge bg-success"><i class="fas fa-child me-1"></i>Tumbuh Kembang ({{ $v->childGrowth->age_months }} bln)</span>
                                    @else
                                        <span class="badge bg-secondary">Pemeriksaan KIA</span>
                                    @endif
                                </td>
                                <td>{{ $v->doctor?->formatted_name ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $v->status_badge_class }}">{{ $v->status_label }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('kia.show', $v->id) }}" class="btn btn-primary fw-bold">
                                        <i class="fas fa-notes-medical me-1"></i>Buka Layanan KIA
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-baby-carriage fa-3x text-muted mb-2"></i>
                                    <div>Tidak ada antrean pasien Poli KIA hari ini.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
