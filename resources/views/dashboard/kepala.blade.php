@extends('layouts.app')

@section('title', 'Dashboard Direktur & Kepala Klinik')
@section('page_title', 'Dashboard Eksekutif Operasional')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
{{-- Stat Cards Row 1: Kunjungan & Pasien --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ number_format($totalPatients, 0, ',', '.') }}</div>
                <div class="stat-label">Total Pasien Terdaftar</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon info">
                <i class="fa-solid fa-hospital-user"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $visitsToday }}</div>
                <div class="stat-label">Kunjungan Hari Ini ({{ $newPatientsToday }} Baru, {{ $oldPatientsToday }} Lama)</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fa-solid fa-user-doctor"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $activeExaminations }}</div>
                <div class="stat-label">Pemeriksaan Sedang Berlangsung</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="fa-solid fa-prescription-bottle-medical"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $pendingPrescriptions }}</div>
                <div class="stat-label">Resep Menunggu Dispensing</div>
            </div>
        </div>
    </div>
</div>

{{-- Stat Cards Row 2: Keuangan & Stok --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">Rp{{ number_format($revenueToday, 0, ',', '.') }}</div>
                <div class="stat-label">Pendapatan Masuk Hari Ini</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">Rp{{ number_format($pendingBillings, 0, ',', '.') }}</div>
                <div class="stat-label">Piutang / Tagihan Belum Lunas</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $lowStockCount }}</div>
                <div class="stat-label">Obat Mencapai Stok Minimum</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fa-solid fa-calendar-xmark"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value">{{ $nearExpiryCount }}</div>
                <div class="stat-label">Batch Mendekati Kedaluwarsa</div>
            </div>
        </div>
    </div>
</div>

{{-- Charts & Analytics Row --}}
<div class="row g-4 mb-4">
    <div class="col-12 col-lg-8">
        <div class="card-healthcare h-100">
            <div class="card-header">
                <span class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-chart-column text-primary"></i> Tren Kunjungan Pasien 7 Hari Terakhir
                </span>
            </div>
            <div class="card-body">
                <canvas id="visitsTrendChart" style="max-height: 280px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card-healthcare h-100">
            <div class="card-header">
                <span class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-info"></i> Distribusi Kunjungan per Poli
                </span>
            </div>
            <div class="card-body">
                <canvas id="polyDistributionChart" style="max-height: 240px;"></canvas>
                <div class="mt-3">
                    @foreach($visitsByPoly as $poly)
                        <div class="d-flex justify-content-between align-items-center small py-1 border-bottom">
                            <span><i class="fa-solid fa-circle-dot text-primary me-2"></i>{{ $poly->name }}</span>
                            <span class="fw-bold">{{ $poly->total }} Pasien</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Operational Activity Logs Table --}}
<div class="card-healthcare">
    <div class="card-header">
        <span class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-shield-halved text-secondary"></i> Log Aktivitas Operasional Terbaru
        </span>
        <a href="{{ route('audit.index') }}" class="btn btn-sm btn-rme-outline">Lihat Seluruh Audit Trail</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-healthcare mb-0">
                <thead>
                    <tr>
                        <th>Waktu (WIB)</th>
                        <th>Pengguna</th>
                        <th>Modul</th>
                        <th>Tindakan</th>
                        <th>Keterangan</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs as $log)
                        <tr>
                            <td class="text-nowrap">{{ $log->created_at->format('d-m-Y H:i:s') }}</td>
                            <td>
                                <strong>{{ $log->user?->name ?? 'Sistem' }}</strong>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $log->module }}</span></td>
                            <td><span class="badge bg-secondary">{{ $log->action }}</span></td>
                            <td>{{ $log->description }}</td>
                            <td><small class="text-muted">{{ $log->ip_address }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada aktivitas tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Visits Trend Bar Chart
        const ctxTrend = document.getElementById('visitsTrendChart').getContext('2d');
        new Chart(ctxTrend, {
            type: 'bar',
            data: {
                labels: {!! json_encode($dates) !!},
                datasets: [{
                    label: 'Jumlah Kunjungan',
                    data: {!! json_encode($trendVisits) !!},
                    backgroundColor: '#2EACF0',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        // 2. Polyclinic Doughnut Chart
        const ctxPoly = document.getElementById('polyDistributionChart').getContext('2d');
        const polyLabels = {!! json_encode($visitsByPoly->pluck('name')) !!};
        const polyData = {!! json_encode($visitsByPoly->pluck('total')) !!};

        new Chart(ctxPoly, {
            type: 'doughnut',
            data: {
                labels: polyLabels.length > 0 ? polyLabels : ['Belum Ada Kunjungan'],
                datasets: [{
                    data: polyData.length > 0 ? polyData : [1],
                    backgroundColor: ['#416B82', '#2EACF0', '#547F96', '#0083C9', '#87B1C7'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    });
</script>
@endpush
