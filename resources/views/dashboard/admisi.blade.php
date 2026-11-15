@extends('layouts.app')

@section('title', 'Dashboard Admisi & Kasir')
@section('page_title', 'Dashboard Petugas Admisi & Kasir')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Admisi & Kasir</li>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $totalRegisteredToday }}</div>
                <div class="stat-label">Pasien Terdaftar Hari Ini</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fa-solid fa-cash-register"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">Rp{{ number_format($todayPaidSum, 0, ',', '.') }}</div>
                <div class="stat-label">Penerimaan Kasir Hari Ini</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $pendingPayments->count() }}</div>
                <div class="stat-label">Tagihan Menunggu Pembayaran</div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Actions Card --}}
<div class="card-healthcare mb-4">
    <div class="card-body p-4 bg-white">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-7">
                <h5 class="fw-bold mb-1" style="color: var(--rme-primary);">Pencarian Pasien Cepat (MPI)</h5>
                <p class="text-muted small mb-3">Ketik Nomor RM, NIK 16 digit, Nama Lengkap, atau Nomor Telepon Pasien.</p>
                <div class="input-group">
                    <select id="quickPatientSearch" class="form-select select2-ajax" data-placeholder="Ketik nama atau No. RM pasien..."></select>
                    <button class="btn btn-rme-primary" type="button" id="btnRegisterSelected" disabled>
                        <i class="fa-solid fa-calendar-plus me-1"></i> Daftarkan Kunjungan
                    </button>
                </div>
            </div>
            <div class="col-12 col-md-5 text-md-end border-start ps-md-4">
                <span class="d-block text-muted small mb-2">Pasien baru belum pernah berobat?</span>
                <a href="{{ route('patients.create') }}" class="btn btn-rme-secondary">
                    <i class="fa-solid fa-user-plus me-1"></i> Daftarkan Pasien Baru
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Kunjungan Hari Ini --}}
    <div class="col-12 col-lg-7">
        <div class="card-healthcare h-100">
            <div class="card-header">
                <span class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-primary"></i> Kunjungan Terdaftar Hari Ini
                </span>
                <a href="{{ route('visits.index') }}" class="btn btn-sm btn-rme-outline">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-healthcare mb-0">
                        <thead>
                            <tr>
                                <th>Antrean</th>
                                <th>No. RM & Nama Pasien</th>
                                <th>Poliklinik</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($visitsToday as $v)
                                <tr>
                                    <td><strong class="badge bg-light text-dark border">{{ $v->queue_number ?? '-' }}</strong></td>
                                    <td>
                                        <div class="fw-bold">{{ $v->patient->name }}</div>
                                        <small class="text-muted">{{ $v->patient->medical_record_number }}</small>
                                    </td>
                                    <td>{{ $v->polyclinic->name }}</td>
                                    <td><span class="badge {{ $v->status_badge_class }}">{{ $v->status_label }}</span></td>
                                    <td>
                                        <a href="{{ route('visits.show', $v->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="Detail">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada kunjungan terdaftar hari ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Antrean Kasir / Tagihan Belum Dibayar --}}
    <div class="col-12 col-lg-5">
        <div class="card-healthcare h-100">
            <div class="card-header">
                <span class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-receipt text-warning"></i> Tagihan Belum Dibayar (Kasir)
                </span>
                <a href="{{ route('billing.index') }}" class="btn btn-sm btn-rme-outline">Semua Tagihan</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-healthcare mb-0">
                        <thead>
                            <tr>
                                <th>No. Invoice</th>
                                <th>Pasien</th>
                                <th>Tagihan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingPayments as $inv)
                                <tr>
                                    <td><small class="fw-semibold">{{ $inv->invoice_number }}</small></td>
                                    <td>
                                        <div>{{ $inv->patient->name }}</div>
                                    </td>
                                    <td>
                                        <strong class="text-danger">Rp{{ number_format($inv->balance_amount, 0, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        <a href="{{ route('billing.show', $inv->id) }}" class="btn btn-sm btn-rme-primary py-0 px-2">
                                            <i class="fa-solid fa-credit-card me-1"></i> Bayar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Tidak ada tagihan tertunda.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#quickPatientSearch').select2({
            theme: 'bootstrap-5',
            ajax: {
                url: '{{ route('patients.search.ajax') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data.results };
                },
                cache: true
            },
            minimumInputLength: 2,
            placeholder: 'Ketik nama pasien atau nomor RM...'
        }).on('select2:select', function (e) {
            const data = e.params.data;
            $('#btnRegisterSelected').prop('disabled', false).data('patient-id', data.id);
        });

        $('#btnRegisterSelected').on('click', function () {
            const patientId = $(this).data('patient-id');
            if (patientId) {
                window.location.href = "{{ route('visits.create') }}?patient_id=" + patientId;
            }
        });
    });
</script>
@endpush
