@extends('layouts.app')

@section('title', 'Dashboard Farmasi')
@section('page_title', 'Dashboard Instalasi Farmasi & Apoteker')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Instalasi Farmasi</li>
@endsection

@section('content')
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fa-solid fa-prescription"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $pendingPrescriptions->count() }}</div>
                <div class="stat-label">Resep Menunggu Verifikasi & Dispensing</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $lowStockMedicines->count() }}</div>
                <div class="stat-label">Obat Mencapai Batas Minimum</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card">
            <div class="stat-icon info">
                <i class="fa-solid fa-hourglass-end"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $nearExpiryBatches->count() }}</div>
                <div class="stat-label">Batch Obat Mendekati Kedaluwarsa</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Antrean Resep Masuk --}}
    <div class="col-12 col-lg-7">
        <div class="card-healthcare h-100">
            <div class="card-header">
                <span class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-file-prescription text-primary"></i> Resep Masuk dari Dokter
                </span>
                <a href="{{ route('pharmacy.prescriptions') }}" class="btn btn-sm btn-rme-outline">Semua Resep</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-healthcare mb-0">
                        <thead>
                            <tr>
                                <th>No. Resep</th>
                                <th>Pasien & Umur</th>
                                <th>Dokter Pengirim</th>
                                <th>Status</th>
                                <th class="text-center">Aksi Telaah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingPrescriptions as $rx)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $rx->prescription_number }}</div>
                                        <small class="text-muted">{{ $rx->prescription_date->format('H:i') }} WIB</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $rx->visit->patient->name }}</div>
                                        <small class="text-muted">{{ $rx->visit->patient->medical_record_number }}</small>
                                        @if($rx->visit->patient->allergies && $rx->visit->patient->allergies->count() > 0)
                                            <span class="badge bg-danger ms-1">Alergi!</span>
                                        @endif
                                    </td>
                                    <td>{{ $rx->doctor->formatted_name }}</td>
                                    <td><span class="badge {{ $rx->status_badge_class }}">{{ $rx->status_label }}</span></td>
                                    <td class="text-center">
                                        <a href="{{ route('pharmacy.show_prescription', $rx->id) }}" class="btn btn-sm btn-rme-primary">
                                            <i class="fa-solid fa-magnifying-glass me-1"></i> Telaah & Dispense
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-check-circle fa-2x text-success mb-2 d-block"></i>
                                        Tidak ada resep yang menunggu saat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Peringatan Stok Minimum & Kedaluwarsa --}}
    <div class="col-12 col-lg-5">
        <div class="card-healthcare mb-4">
            <div class="card-header bg-light">
                <span class="d-flex align-items-center gap-2 text-danger fw-semibold">
                    <i class="fa-solid fa-triangle-exclamation"></i> Obat Stok Kritis (&le; Min)
                </span>
                <a href="{{ route('pharmacy.medicines') }}" class="btn btn-sm btn-outline-danger">Stok</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-healthcare mb-0 small">
                        <thead>
                            <tr>
                                <th>Nama Obat</th>
                                <th>Sisa Stok</th>
                                <th>Min</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockMedicines as $med)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $med->brand_name }}</div>
                                        <small class="text-muted">{{ $med->strength }}</small>
                                    </td>
                                    <td><strong class="text-danger">{{ $med->total_stock }} {{ $med->dosage_form }}</strong></td>
                                    <td>{{ $med->min_stock }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">Seluruh stok obat berada di atas batas minimum.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card-healthcare">
            <div class="card-header bg-light">
                <span class="d-flex align-items-center gap-2 text-warning fw-semibold">
                    <i class="fa-solid fa-clock text-warning"></i> Batch Mendekati Kedaluwarsa (&lt; 90 Hari)
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-healthcare mb-0 small">
                        <thead>
                            <tr>
                                <th>Obat & No. Batch</th>
                                <th>Tgl Exp</th>
                                <th>Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($nearExpiryBatches as $b)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $b->medicine->brand_name }}</div>
                                        <small class="text-muted">{{ $b->batch_number }}</small>
                                    </td>
                                    <td><span class="badge bg-warning text-dark">{{ $b->expiry_date->format('d-m-Y') }}</span></td>
                                    <td>{{ $b->current_stock }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">Tidak ada batch obat yang mendekati kedaluwarsa.</td>
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
