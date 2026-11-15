@extends('layouts.app')

@section('title', 'Kartu Stok Obat - ' . $medicine->brand_name)

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold">Kartu Stok Obat (Stock Movement Card)</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('pharmacy.medicines') }}">Katalog Obat</a></li>
                    <li class="breadcrumb-item active">{{ $medicine->brand_name }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('pharmacy.medicines') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Katalog
        </a>
    </div>

    <!-- Medicine Header Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary text-white p-3 fs-3 fw-bold text-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold text-dark mb-1">{{ $medicine->brand_name }}</h4>
                            <div class="text-muted small">
                                Kode: <span class="badge bg-light text-primary border">{{ $medicine->code }}</span> | 
                                Generik: <strong>{{ $medicine->generic_name }}</strong> ({{ $medicine->strength }}) | 
                                Kategori: {{ $medicine->category?->name }} | 
                                Sediaan: {{ $medicine->dosage_form }} ({{ $medicine->unit?->name }})
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0 border-start-md">
                    <div class="text-muted small">Total Stok Aktif Saat Ini:</div>
                    <div class="fs-2 fw-bold {{ $medicine->total_stock <= $medicine->min_stock ? 'text-danger' : 'text-primary' }}">
                        {{ $medicine->total_stock }} <span class="fs-6 text-muted">{{ $medicine->dosage_form }}</span>
                    </div>
                    <span class="badge bg-light text-secondary border">Min. Stok: {{ $medicine->min_stock }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Batches Grid -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-layer-group me-2"></i>Rincian Batch Aktif & Tanggal Kedaluwarsa (FEFO)
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nomor Batch</th>
                            <th>Tanggal Kedaluwarsa (Expiry)</th>
                            <th>Stok Awal</th>
                            <th>Sisa Stok Saat Ini</th>
                            <th>Status Batch</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($medicine->batches as $batch)
                            @php
                                $isExpired = $batch->expiry_date->isPast();
                                $isNearExp = !$isExpired && $batch->expiry_date->diffInMonths(now()) <= 3;
                            @endphp
                            <tr>
                                <td class="fw-bold"><span class="badge bg-light text-dark border">{{ $batch->batch_number }}</span></td>
                                <td>
                                    {{ $batch->expiry_date->format('d F Y') }}
                                    @if($isExpired)
                                        <span class="badge bg-danger ms-1">Expired</span>
                                    @elseif($isNearExp)
                                        <span class="badge bg-warning text-dark ms-1">Mendekati Expired</span>
                                    @endif
                                </td>
                                <td>{{ $batch->initial_stock }}</td>
                                <td class="fw-bold fs-6 {{ $batch->current_stock > 0 ? 'text-primary' : 'text-muted' }}">
                                    {{ $batch->current_stock }}
                                </td>
                                <td>
                                    <span class="badge {{ $batch->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $batch->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">Belum ada batch penerimaan untuk obat ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Stock Movements Table (Kartu Stok) -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-dark">
                <i class="fas fa-list-alt me-2 text-primary"></i>Histori Mutasi Stok (Ledger Kartu Stok)
            </h5>
            <span class="small text-muted">Urutan Kronologis Terakhir</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu Transaksi</th>
                            <th>Jenis Mutasi</th>
                            <th>No. Referensi Transaksi</th>
                            <th>No. Batch</th>
                            <th class="text-end text-success">Masuk (+)</th>
                            <th class="text-end text-danger">Keluar (-)</th>
                            <th class="text-end text-dark">Sisa Saldo</th>
                            <th>Keterangan / Catatan</th>
                            <th>Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $m)
                            <tr>
                                <td>
                                    <div>{{ $m->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $m->created_at->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    @php
                                        $typeBadge = match($m->movement_type) {
                                            'penerimaan' => 'bg-success',
                                            'dispensing' => 'bg-primary',
                                            'penyesuaian_opname' => 'bg-warning text-dark',
                                            'retur' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $typeBadge }} text-capitalize">{{ str_replace('_', ' ', $m->movement_type) }}</span>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $m->reference_number }}</span></td>
                                <td>{{ $m->batch?->batch_number ?? '-' }}</td>
                                <td class="text-end fw-bold text-success">
                                    {{ $m->quantity_in > 0 ? '+' . $m->quantity_in : '-' }}
                                </td>
                                <td class="text-end fw-bold text-danger">
                                    {{ $m->quantity_out > 0 ? '-' . $m->quantity_out : '-' }}
                                </td>
                                <td class="text-end fw-bold text-dark fs-6">
                                    {{ $m->balance_after }}
                                </td>
                                <td><small class="text-muted">{{ $m->notes ?? '-' }}</small></td>
                                <td><small><i class="fas fa-user-circle me-1 text-muted"></i>{{ $m->creator?->name ?? 'Sistem' }}</small></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-clipboard-list fa-3x text-muted mb-2"></i>
                                    <div>Belum ada histori pergerakan stok untuk obat ini.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($movements->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
