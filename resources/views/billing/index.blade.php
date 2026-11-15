@extends('layouts.app')

@section('title', 'Billing & Kasir Klinik')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold">Kasir & Billing Pembayaran Terpadu</h4>
            <p class="text-muted small mb-0">Pengelolaan tagihan pasien, pembayaran multi-metode, diskon, refund, dan cetak kuitansi</p>
        </div>
        <div class="text-muted small">
            <i class="fas fa-cash-register me-1 text-primary"></i> Kasir Aktif: <strong>{{ auth()->user()->name }}</strong>
        </div>
    </div>

    <!-- Filter Status Tabs -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <ul class="nav nav-pills gap-2">
                    <li class="nav-item">
                        <a class="nav-link {{ empty($status) ? 'active bg-primary' : 'text-dark' }}" href="{{ route('billing.index') }}">
                            Semua Tagihan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'belum_lunas' ? 'active bg-danger text-white' : 'text-dark' }}" href="{{ route('billing.index', ['status' => 'belum_lunas']) }}">
                            <i class="fas fa-exclamation-circle me-1"></i>Belum Lunas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $status === 'lunas' ? 'active bg-success text-white' : 'text-dark' }}" href="{{ route('billing.index', ['status' => 'lunas']) }}">
                            <i class="fas fa-check-circle me-1"></i>Lunas
                        </a>
                    </li>
                </ul>

                <form action="{{ route('billing.index') }}" method="GET" class="d-flex gap-2">
                    <input type="date" class="form-control form-control-sm" name="date" value="{{ $date ?? '' }}">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-filter me-1"></i>Filter</button>
                    @if($date || $status)
                        <a href="{{ route('billing.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-file-invoice-dollar me-2"></i>Daftar Faktur Tagihan Pasien
            </h5>
            <span class="small text-muted">Total: {{ $invoices->total() }} tagihan</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Tagihan</th>
                            <th>Waktu Terbit</th>
                            <th>No. RM / Pasien</th>
                            <th>Poli Tujuan</th>
                            <th class="text-end">Total Tagihan</th>
                            <th class="text-end">Sudah Bayar</th>
                            <th class="text-end">Sisa Tagihan</th>
                            <th>Status Pembayaran</th>
                            <th class="text-center" style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary">{{ $inv->invoice_number }}</div>
                                    <small class="badge bg-light text-dark border">Kunjungan: {{ $inv->visit?->visit_number }}</small>
                                </td>
                                <td>
                                    <div>{{ $inv->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $inv->created_at->format('H:i') }} WIB</small>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-6">{{ $inv->patient?->name }}</div>
                                    <small class="text-muted">{{ $inv->patient?->medical_record_number }} ({{ $inv->visit?->payment_type }})</small>
                                </td>
                                <td>{{ $inv->visit?->polyclinic?->name ?? '-' }}</td>
                                <td class="text-end fw-bold text-dark">
                                    Rp{{ number_format($inv->final_amount, 0, ',', '.') }}
                                </td>
                                <td class="text-end text-success fw-bold">
                                    Rp{{ number_format($inv->paid_amount, 0, ',', '.') }}
                                </td>
                                <td class="text-end fw-bold {{ $inv->balance_due > 0 ? 'text-danger' : 'text-muted' }}">
                                    Rp{{ number_format($inv->balance_due, 0, ',', '.') }}
                                </td>
                                <td>
                                    <span class="badge {{ $inv->status_badge_class }}">{{ $inv->status_label }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('billing.show', $inv->id) }}" class="btn btn-sm btn-primary fw-bold">
                                        <i class="fas fa-cash-register me-1"></i>Buka Kasir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-receipt fa-3x text-muted mb-2"></i>
                                    <div>Tidak ada data tagihan pada filter ini.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($invoices->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
