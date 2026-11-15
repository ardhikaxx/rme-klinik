@extends('layouts.app')

@section('title', 'Laporan Manajemen & Analisis Eksekutif Klinik')
@section('page_title', 'Laporan Manajemen & Analisis Operasional')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Laporan & Analitik</li>
@endsection

@section('page_actions')
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('reports.export', ['type' => 'visits', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-csv me-1"></i> Export Kunjungan
        </a>
        <a href="{{ route('reports.export', ['type' => 'diseases', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-file-csv me-1"></i> Export ICD-10
        </a>
        <a href="{{ route('reports.export', ['type' => 'fees', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-outline-warning text-dark btn-sm">
            <i class="fas fa-file-csv me-1"></i> Export Jasa Medis
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Date Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('reports.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label class="form-label small fw-bold mb-1">Mulai Tanggal</label>
                    <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold mb-1">Sampai Tanggal</label>
                    <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1 d-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Filter Laporan</button>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1 d-block">&nbsp;</label>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Executive KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold">Total Kunjungan</div>
                        <div class="fs-3 fw-bold text-primary mt-1">{{ number_format($summary['total_visits'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="rounded-circle bg-light p-3 text-primary fs-4">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold">Total Pasien Baru</div>
                        <div class="fs-3 fw-bold text-success mt-1">{{ number_format($summary['new_patients'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="rounded-circle bg-light p-3 text-success fs-4">
                        <i class="fas fa-user-plus"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold">Total Pendapatan (Billing)</div>
                        <div class="fs-4 fw-bold text-dark mt-1">Rp{{ number_format($summary['total_revenue'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="rounded-circle bg-light p-3 text-warning fs-4">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold">Jasa Medis Terhitung</div>
                        <div class="fs-4 fw-bold text-info mt-1">Rp{{ number_format($summary['total_doctor_fees'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="rounded-circle bg-light p-3 text-info fs-4">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold text-primary">
                        <i class="fas fa-chart-area me-2"></i>Tren Kunjungan Harian
                    </h5>
                    <span class="small text-muted">{{ $startDate }} s/d {{ $endDate }}</span>
                </div>
                <div class="card-body p-4">
                    <canvas id="trendChart" height="130"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>Distribusi Kunjungan per Poli
                    </h5>
                </div>
                <div class="card-body p-4">
                    <canvas id="polyChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 10 Diseases & Top 10 Medicines Row -->
    <div class="row g-4 mb-4">
        <!-- 10 Besar Penyakit ICD-10 -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold text-danger">
                        <i class="fas fa-virus me-2"></i>10 Besar Penyakit (ICD-10 Morbiditas)
                    </h5>
                    <a href="{{ route('reports.export', ['type' => 'diseases', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-download me-1"></i>CSV
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center">Rank</th>
                                    <th>Kode ICD-10</th>
                                    <th>Deskripsi Penyakit</th>
                                    <th class="text-center">Jumlah Kasus</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topDiseases as $idx => $dis)
                                    <tr>
                                        <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                                        <td><span class="badge bg-danger">{{ $dis->icd10_code }}</span></td>
                                        <td class="fw-bold text-dark">{{ $dis->description }}</td>
                                        <td class="text-center fw-bold fs-6 text-primary">{{ $dis->total_cases }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Belum ada data morbiditas pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 10 Besar Obat Terbanyak -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold text-warning text-dark">
                        <i class="fas fa-pills me-2"></i>10 Besar Pemakaian Obat Farmasi
                    </h5>
                    <a href="{{ route('reports.export', ['type' => 'medicines', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-sm btn-outline-warning text-dark">
                        <i class="fas fa-download me-1"></i>CSV
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center">Rank</th>
                                    <th>Nama Obat</th>
                                    <th>Bentuk</th>
                                    <th class="text-center">Jumlah Terpakai</th>
                                    <th class="text-end">Total Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topMedicines as $idx => $med)
                                    <tr>
                                        <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $med->brand_name }}</div>
                                            <small class="text-muted">{{ $med->generic_name }}</small>
                                        </td>
                                        <td>{{ $med->dosage_form }}</td>
                                        <td class="text-center fw-bold text-success fs-6">{{ $med->total_quantity }}</td>
                                        <td class="text-end fw-bold">Rp{{ number_format($med->total_revenue, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data pemakaian obat pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Medical Fees Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-hand-holding-usd me-2"></i>Laporan Pembagian Jasa Medis (Dokter & Perawat)
            </h5>
            <a href="{{ route('reports.export', ['type' => 'fees', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-download me-1"></i>Export CSV
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu Pelayanan</th>
                            <th>No. Kunjungan / Pasien</th>
                            <th>Dokter Penerima Jasa</th>
                            <th class="text-end">Jasa Dokter (Rp)</th>
                            <th class="text-end">Jasa Perawat (Rp)</th>
                            <th class="text-end">Porsi Klinik (Rp)</th>
                            <th>Status Transaksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($medicalFees as $mf)
                            <tr>
                                <td class="text-nowrap">{{ $mf->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="fw-bold">{{ $mf->visit?->patient?->name }}</div>
                                    <small class="text-muted">{{ $mf->visit?->visit_number }}</small>
                                </td>
                                <td>{{ $mf->doctor?->formatted_name ?? '-' }}</td>
                                <td class="text-end fw-bold text-primary">Rp{{ number_format($mf->doctor_fee, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold text-success">Rp{{ number_format($mf->nurse_fee, 0, ',', '.') }}</td>
                                <td class="text-end text-muted">Rp{{ number_format($mf->clinic_fee, 0, ',', '.') }}</td>
                                <td><span class="badge bg-success text-uppercase">{{ $mf->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada data jasa medis pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3">
            <x-table-footer :paginator="$medicalFees" label="transaksi jasa medis" />
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Trend Chart
    var trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        var trendLabels = @json($dailyTrend['labels'] ?? []);
        var trendValues = @json($dailyTrend['data'] ?? []);

        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: trendLabels.length > 0 ? trendLabels : ['H-4', 'H-3', 'H-2', 'H-1', 'Hari Ini'],
                datasets: [{
                    label: 'Jumlah Kunjungan Pasien',
                    data: trendValues.length > 0 ? trendValues : [0, 0, 0, 0, 0],
                    backgroundColor: '#2EACF0',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    // Polyclinic Doughnut Chart
    var polyCtx = document.getElementById('polyChart');
    if (polyCtx) {
        var polyLabels = @json($visitsByPoly->pluck('polyclinic_name'));
        var polyValues = @json($visitsByPoly->pluck('total'));

        new Chart(polyCtx, {
            type: 'doughnut',
            data: {
                labels: polyLabels.length > 0 ? polyLabels : ['Poli Umum', 'Poli Gigi', 'Poli KIA'],
                datasets: [{
                    data: polyValues.length > 0 ? polyValues : [1, 1, 1],
                    backgroundColor: ['#416B82', '#2EACF0', '#0083C9', '#87B1C7', '#547F96']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
});
</script>
@endpush
