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
        <div class="card-healthcare h-100 d-flex flex-column">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-chart-column text-primary"></i> Tren Kunjungan Pasien 7 Hari Terakhir
                </span>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 rounded-pill small">
                        <i class="fa-solid fa-users me-1"></i>Total: <strong>{{ array_sum($trendVisits) }}</strong> Pasien
                    </span>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Tipe Tampilan Grafik">
                        <button type="button" class="btn btn-sm btn-outline-primary active" id="btnChartBar" title="Grafik Batang">
                            <i class="fa-solid fa-chart-simple me-1"></i>Batang
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnChartLine" title="Grafik Garis Area">
                            <i class="fa-solid fa-chart-area me-1"></i>Garis
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body d-flex flex-column flex-grow-1 p-3">
                {{-- Quick Metrics Summary Row --}}
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <div class="p-2 rounded bg-light border text-center">
                            <div class="text-muted small">Total 7 Hari</div>
                            <div class="fw-bold text-primary fs-6">{{ array_sum($trendVisits) }} <span class="small fw-normal text-muted">Pasien</span></div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded bg-light border text-center">
                            <div class="text-muted small">Rata-rata Harian</div>
                            <div class="fw-bold text-success fs-6">{{ count($trendVisits) > 0 ? round(array_sum($trendVisits) / count($trendVisits), 1) : 0 }} <span class="small fw-normal text-muted">/ hari</span></div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded bg-light border text-center">
                            <div class="text-muted small">Tertinggi</div>
                            <div class="fw-bold text-info fs-6">{{ count($trendVisits) > 0 ? max($trendVisits) : 0 }} <span class="small fw-normal text-muted">Pasien</span></div>
                        </div>
                    </div>
                </div>

                {{-- Chart Canvas Container (Takes 100% of remaining vertical card space) --}}
                <div class="position-relative flex-grow-1 w-100" style="min-height: 280px;">
                    <canvas id="visitsTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card-healthcare h-100 d-flex flex-column">
            <div class="card-header">
                <span class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-chart-pie text-info"></i> Distribusi Kunjungan per Poli
                </span>
            </div>
            <div class="card-body d-flex flex-column flex-grow-1">
                <div class="position-relative" style="height: 220px;">
                    <canvas id="polyDistributionChart"></canvas>
                </div>
                <div class="mt-3 flex-grow-1">
                    @forelse($visitsByPoly as $poly)
                        <div class="d-flex justify-content-between align-items-center small py-1 border-bottom">
                            <span><i class="fa-solid fa-circle-dot text-primary me-2"></i>{{ $poly->name }}</span>
                            <span class="fw-bold">{{ $poly->total }} Pasien</span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3 small">
                            <i class="fa-solid fa-info-circle me-1"></i>Belum ada data kunjungan hari ini
                        </div>
                    @endforelse
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
        // 1. Visits Trend Bar & Line Chart
        const canvasTrend = document.getElementById('visitsTrendChart');
        if (canvasTrend) {
            const ctxTrend = canvasTrend.getContext('2d');
            const datesLabels = {!! json_encode($dates) !!};
            const visitsData = {!! json_encode($trendVisits) !!};

            // Dynamic Gradients
            function getBarGradient(ctx, chartArea) {
                if (!chartArea) return '#2EACF0';
                const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                gradient.addColorStop(0, '#2EACF0');
                gradient.addColorStop(1, '#0083C9');
                return gradient;
            }

            function getLineFillGradient(ctx, chartArea) {
                if (!chartArea) return 'rgba(46, 172, 240, 0.2)';
                const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                gradient.addColorStop(0, 'rgba(46, 172, 240, 0.45)');
                gradient.addColorStop(0.65, 'rgba(46, 172, 240, 0.12)');
                gradient.addColorStop(1, 'rgba(46, 172, 240, 0.00)');
                return gradient;
            }

            // Value Labels Plugin on top of bars/points
            const customValueLabelsPlugin = {
                id: 'customValueLabels',
                afterDatasetsDraw(chart) {
                    const { ctx, data } = chart;
                    const meta = chart.getDatasetMeta(0);
                    if (!meta || !meta.data) return;

                    meta.data.forEach((element, index) => {
                        const val = data.datasets[0].data[index];
                        if (val > 0) {
                            ctx.save();
                            ctx.font = '600 11px system-ui, -apple-system, sans-serif';
                            ctx.fillStyle = '#0083C9';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'bottom';
                            const yPos = chart.config.type === 'line' ? element.y - 8 : element.y - 4;
                            ctx.fillText(val + ' Pasien', element.x, yPos);
                            ctx.restore();
                        }
                    });
                }
            };

            const chartConfig = {
                type: 'bar',
                data: {
                    labels: datesLabels,
                    datasets: [{
                        label: 'Jumlah Kunjungan',
                        data: visitsData,
                        backgroundColor: function(context) {
                            const chart = context.chart;
                            const { ctx, chartArea } = chart;
                            return chart.config.type === 'line' 
                                ? getLineFillGradient(ctx, chartArea) 
                                : getBarGradient(ctx, chartArea);
                        },
                        borderColor: '#0083C9',
                        borderWidth: 2,
                        borderRadius: { topLeft: 8, topRight: 8, bottomLeft: 0, bottomRight: 0 },
                        borderSkipped: false,
                        maxBarThickness: 50,
                        // Line chart styling
                        fill: true,
                        tension: 0.35,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#0083C9',
                        pointBorderWidth: 2.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 24,
                            bottom: 6,
                            left: 4,
                            right: 4
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.92)',
                            titleColor: '#ffffff',
                            titleFont: { weight: 'bold', size: 13 },
                            bodyColor: '#f1f5f9',
                            bodyFont: { size: 12 },
                            padding: { top: 10, bottom: 10, left: 14, right: 14 },
                            cornerRadius: 8,
                            displayColors: false,
                            borderColor: 'rgba(255, 255, 255, 0.12)',
                            borderWidth: 1,
                            callbacks: {
                                title: function(context) {
                                    return 'Tanggal: ' + context[0].label;
                                },
                                label: function(context) {
                                    return 'Kunjungan: ' + context.parsed.y + ' Pasien';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grace: '18%',
                            grid: {
                                color: 'rgba(226, 232, 240, 0.7)',
                                borderDash: [4, 4],
                                drawBorder: false
                            },
                            ticks: {
                                precision: 0,
                                stepSize: 1,
                                color: '#64748b',
                                font: { size: 11, weight: '500' },
                                padding: 6
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: '#334155',
                                font: { size: 12, weight: '600' },
                                padding: 6
                            }
                        }
                    }
                },
                plugins: [customValueLabelsPlugin]
            };

            const trendChart = new Chart(ctxTrend, chartConfig);

            // Toggle view mode: Bar vs Line
            const btnBar = document.getElementById('btnChartBar');
            const btnLine = document.getElementById('btnChartLine');

            if (btnBar && btnLine) {
                btnBar.addEventListener('click', function () {
                    if (trendChart.config.type === 'bar') return;
                    btnBar.classList.add('active');
                    btnLine.classList.remove('active');
                    trendChart.config.type = 'bar';
                    trendChart.data.datasets[0].borderWidth = 2;
                    trendChart.update();
                });

                btnLine.addEventListener('click', function () {
                    if (trendChart.config.type === 'line') return;
                    btnLine.classList.add('active');
                    btnBar.classList.remove('active');
                    trendChart.config.type = 'line';
                    trendChart.data.datasets[0].borderWidth = 3;
                    trendChart.update();
                });
            }
        }

        // 2. Polyclinic Doughnut Chart
        const canvasPoly = document.getElementById('polyDistributionChart');
        if (canvasPoly) {
            const ctxPoly = canvasPoly.getContext('2d');
            const polyLabels = {!! json_encode($visitsByPoly->pluck('name')) !!};
            const polyData = {!! json_encode($visitsByPoly->pluck('total')) !!};

            new Chart(ctxPoly, {
                type: 'doughnut',
                data: {
                    labels: polyLabels.length > 0 ? polyLabels : ['Belum Ada Kunjungan'],
                    datasets: [{
                        data: polyData.length > 0 ? polyData : [1],
                        backgroundColor: ['#416B82', '#2EACF0', '#547F96', '#0083C9', '#87B1C7'],
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 12,
                                font: { size: 11, weight: '500' }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
