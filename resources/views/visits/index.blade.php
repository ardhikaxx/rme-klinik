@extends('layouts.app')

@section('title', 'Daftar Kunjungan Internal')
@section('page_title', 'Daftar Pekerjaan & Kunjungan Pasien')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Kunjungan</li>
@endsection

@section('page_actions')
    <a href="{{ route('visits.create') }}" class="btn btn-rme-primary">
        <i class="fa-solid fa-calendar-plus me-1"></i> Registrasi Kunjungan Baru
    </a>
@endsection

@section('content')
{{-- Filter Card --}}
<div class="card-healthcare mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('visits.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted mb-1">Tanggal Pelayanan</label>
                <input type="date" name="date" value="{{ $filters['date'] ?? date('Y-m-d') }}" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Poliklinik</label>
                <select name="polyclinic_id" class="form-select form-select-sm">
                    <option value="">Semua Poli</option>
                    @foreach($polyclinics as $poly)
                        <option value="{{ $poly->id }}" {{ ($filters['polyclinic_id'] ?? '') == $poly->id ? 'selected' : '' }}>
                            {{ $poly->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Dokter</label>
                <select name="doctor_id" class="form-select form-select-sm">
                    <option value="">Semua Dokter</option>
                    @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}" {{ ($filters['doctor_id'] ?? '') == $doc->id ? 'selected' : '' }}>
                            {{ $doc->formatted_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Status Pelayanan</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="menunggu_pemeriksaan_awal" {{ ($filters['status'] ?? '') === 'menunggu_pemeriksaan_awal' ? 'selected' : '' }}>Menunggu Pengkajian Awal</option>
                    <option value="menunggu_dokter" {{ ($filters['status'] ?? '') === 'menunggu_dokter' ? 'selected' : '' }}>Menunggu Dokter</option>
                    <option value="pemeriksaan_dokter_berlangsung" {{ ($filters['status'] ?? '') === 'pemeriksaan_dokter_berlangsung' ? 'selected' : '' }}>Pemeriksaan Dokter</option>
                    <option value="menunggu_farmasi" {{ ($filters['status'] ?? '') === 'menunggu_farmasi' ? 'selected' : '' }}>Menunggu Farmasi</option>
                    <option value="menunggu_pembayaran" {{ ($filters['status'] ?? '') === 'menunggu_pembayaran' ? 'selected' : '' }}>Menunggu Kasir</option>
                    <option value="selesai" {{ ($filters['status'] ?? '') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="dibatalkan" {{ ($filters['status'] ?? '') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex align-items-end gap-1">
                <button type="submit" class="btn btn-sm btn-rme-secondary w-100" style="margin-top: 1.6rem;">
                    <i class="fa-solid fa-filter me-1"></i> Terapkan Filter
                </button>
                <a href="{{ route('visits.index') }}" class="btn btn-sm btn-outline-secondary" style="margin-top: 1.6rem;" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Visits Table --}}
<div class="card-healthcare">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-healthcare mb-0">
                <thead>
                    <tr>
                        <th>Antrean</th>
                        <th>No. Kunjungan</th>
                        <th>Waktu (WIB)</th>
                        <th>No. RM & Nama Pasien</th>
                        <th>Poliklinik</th>
                        <th>Dokter Pemeriksa</th>
                        <th>Pembiayaan</th>
                        <th>Status Pelayanan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visits as $v)
                        <tr>
                            <td>
                                <strong class="badge bg-light text-dark border fs-6">
                                    {{ $v->queue_number ?? '-' }}
                                </strong>
                            </td>
                            <td>
                                <a href="{{ route('visits.show', $v->id) }}" class="fw-bold text-decoration-none">
                                    {{ $v->visit_number }}
                                </a>
                                <small class="text-muted d-block">{{ ucfirst($v->visit_type) }}</small>
                            </td>
                            <td>{{ $v->registration_date->format('H:i') }} WIB</td>
                            <td>
                                <a href="{{ route('patients.show', $v->patient->id) }}" class="fw-semibold text-decoration-none" style="color: var(--rme-info);">
                                    {{ $v->patient->name }}
                                </a>
                                <small class="text-muted d-block">{{ $v->patient->medical_record_number }} &bull; {{ $v->patient->age }} thn</small>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $v->polyclinic->name }}</span></td>
                            <td>{{ $v->doctor->formatted_name }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ strtoupper($v->payment_type) }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $v->status_badge_class }}">
                                    {{ $v->status_label }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('visits.show', $v->id) }}" class="btn btn-sm btn-rme-outline py-0 px-2" title="Buka Detail">
                                    <i class="fa-solid fa-folder-open me-1"></i> Buka
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-calendar-xmark fa-3x text-muted mb-3 d-block"></i>
                                Tidak ada kunjungan pasien yang sesuai kriteria pencarian pada tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($visits->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $visits->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
