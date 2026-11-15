@extends('layouts.app')

@section('title', 'Master Patient Index (MPI)')
@section('page_title', 'Master Patient Index (MPI)')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Pasien</li>
@endsection

@section('page_actions')
    <a href="{{ route('patients.create') }}" class="btn btn-rme-primary">
        <i class="fa-solid fa-user-plus me-1"></i> Daftarkan Pasien Baru
    </a>
@endsection

@section('content')
{{-- Filter & Search Card --}}
<div class="card-healthcare mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('patients.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Cari No. RM, NIK 16 digit, Nama, atau Telepon...">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="gender" class="form-select">
                    <option value="">Semua Jenis Kelamin</option>
                    <option value="L" {{ ($filters['gender'] ?? '') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ ($filters['gender'] ?? '') === 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="is_verified" class="form-select">
                    <option value="">Status Verifikasi</option>
                    <option value="1" {{ ($filters['is_verified'] ?? '') === '1' ? 'selected' : '' }}>Terverifikasi (NIK)</option>
                    <option value="0" {{ ($filters['is_verified'] ?? '') === '0' ? 'selected' : '' }}>Identitas Sementara</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-rme-secondary w-100">
                    <i class="fa-solid fa-filter me-1"></i> Saring
                </button>
                @if(!empty($filters['search']) || !empty($filters['gender']) || isset($filters['is_verified']))
                    <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Patient Table --}}
<div class="card-healthcare">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-healthcare mb-0">
                <thead>
                    <tr>
                        <th>No. RM</th>
                        <th>Nama Pasien</th>
                        <th>NIK</th>
                        <th>Tgl Lahir / Umur</th>
                        <th>JK</th>
                        <th>No. Telepon</th>
                        <th>Alamat Domisili</th>
                        <th>Riwayat Alergi</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                        <tr>
                            <td>
                                <strong class="badge bg-light text-dark border fs-6">
                                    {{ $patient->medical_record_number }}
                                </strong>
                            </td>
                            <td>
                                <a href="{{ route('patients.show', $patient->id) }}" class="fw-bold text-decoration-none" style="color: var(--rme-info);">
                                    {{ $patient->name }}
                                </a>
                                @if($patient->is_verified)
                                    <i class="fa-solid fa-circle-check text-success ms-1 small" title="NIK Terverifikasi"></i>
                                @else
                                    <span class="badge bg-warning text-dark ms-1 small" style="font-size: 0.65rem;">Sementara</span>
                                @endif
                            </td>
                            <td>{{ $patient->nik ?? '-' }}</td>
                            <td class="text-nowrap">
                                {{ $patient->date_of_birth ? $patient->date_of_birth->translatedFormat('d M Y') : '-' }}
                                <small class="text-muted d-block">({{ $patient->age_detail }})</small>
                            </td>
                            <td>
                                <span class="badge {{ $patient->gender === 'L' ? 'bg-primary' : 'bg-danger' }}">
                                    {{ $patient->gender }}
                                </span>
                            </td>
                            <td>{{ $patient->phone }}</td>
                            <td>
                                <small class="text-muted d-inline-block text-truncate" style="max-width: 200px;">
                                    {{ $patient->full_address }}
                                </small>
                            </td>
                            <td>
                                @if($patient->allergies && $patient->allergies->count() > 0)
                                    <span class="badge bg-danger">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                        {{ $patient->allergies->count() }} Alergi
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-outline-secondary" title="Detail Pasien">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('visits.create') }}?patient_id={{ $patient->id }}" class="btn btn-outline-primary" title="Daftarkan Kunjungan">
                                        <i class="fa-solid fa-calendar-plus"></i>
                                    </a>
                                    <a href="{{ route('patients.card', $patient->id) }}" target="_blank" class="btn btn-outline-info" title="Cetak Kartu">
                                        <i class="fa-solid fa-id-card"></i>
                                    </a>
                                    <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-outline-secondary" title="Edit Identitas">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-user-slash fa-3x text-muted mb-3 d-block"></i>
                                Data pasien tidak ditemukan. Silakan lakukan pencarian lain atau daftarkan pasien baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-top py-3">
        <x-table-footer :paginator="$patients" label="pasien" />
    </div>
</div>
@endsection
