@extends('layouts.app')

@section('title', 'Pemeriksaan Poli KIA - ' . $visit->patient->name)
@section('page_title', 'Pelayanan Kesehatan Ibu dan Anak (Poli KIA)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('kia.index') }}">Poli KIA</a></li>
    <li class="breadcrumb-item active" aria-current="page">Layanan {{ $visit->visit_number }}</li>
@endsection

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 text-dark fw-bold">Pelayanan Kesehatan Ibu dan Anak (Poli KIA)</h4>
                <span class="badge bg-primary fs-6">{{ $visit->patient->name }}</span>
            </div>
            <div class="text-muted small mt-1">
                Kunjungan: <strong>{{ $visit->visit_number }}</strong> (Antrean: <strong>{{ $visit->queue_number }}</strong>) | 
                Pemeriksa: <strong>{{ $visit->doctor?->formatted_name ?? 'Bidan/Dokter KIA' }}</strong> | 
                Tanggal: {{ $visit->registration_date->format('d/m/Y') }}
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('kia.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Antrean KIA
            </a>
            <a href="{{ route('clinical.examination', $visit->id) }}" class="btn btn-outline-primary">
                <i class="fas fa-stethoscope me-1"></i> Ruang Periksa Dokter
            </a>
        </div>
    </div>

    <!-- Patient Header Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-info text-white p-3 fs-4 fw-bold text-center" style="width: 55px; height: 55px;">
                            <i class="fas fa-baby"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-5">{{ $visit->patient->name }}</div>
                            <div class="text-muted small">
                                No. RM: <span class="badge bg-primary">{{ $visit->patient->medical_record_number }}</span> | 
                                NIK: {{ $visit->patient->nik ?? '-' }} | 
                                TTL: {{ $visit->patient->place_of_birth ?? '-' }}, {{ $visit->patient->date_of_birth?->format('d/m/Y') }} ({{ $visit->patient->age }} thn) | 
                                JK: {{ $visit->patient->gender == 'P' ? 'Perempuan' : 'Laki-laki' }} (Gol: {{ $visit->patient->blood_type }})
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 border-start-md mt-3 mt-md-0">
                    <div class="small">
                        <div><strong class="text-muted">Alamat:</strong> {{ $visit->patient->full_address }}</div>
                        <div><strong class="text-muted">No. HP:</strong> {{ $visit->patient->phone ?? '-' }}</div>
                        <div><strong class="text-muted">Penjamin:</strong> <span class="badge bg-secondary text-uppercase">{{ $visit->payment_type }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nav Tabs for ANC vs Child Growth -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs card-header-tabs m-0 px-3 pt-2" id="kiaTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold py-3" id="anc-tab" data-bs-toggle="tab" data-bs-target="#anc" type="button" role="tab">
                        <i class="fas fa-female me-2 text-danger"></i>Pemeriksaan Kehamilan (Antenatal Care / ANC)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-3" id="child-tab" data-bs-toggle="tab" data-bs-target="#child" type="button" role="tab">
                        <i class="fas fa-child me-2 text-success"></i>Tumbuh Kembang Anak & Grafik Pertumbuhan (KMS)
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content" id="kiaTabsContent">
                <!-- 1. ANC TAB -->
                <div class="tab-pane fade show active" id="anc" role="tabpanel">
                    @php $anc = $visit->antenatalCare; @endphp
                    <form action="{{ route('kia.anc.store', $visit->id) }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <!-- Riwayat Obstetri & HPHT -->
                            <div class="col-lg-6">
                                <h6 class="fw-bold text-primary mb-3"><i class="fas fa-baby-carriage me-2"></i>1. Riwayat Obstetri & Usia Kehamilan</h6>
                                <div class="row g-3 mb-3">
                                    <div class="col-4">
                                        <label class="form-label fw-bold">Gravida (G) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="gravida" value="{{ old('gravida', $anc?->gravida ?? 1) }}" min="1" required>
                                        <div class="form-text">Kehamilan ke-</div>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label fw-bold">Para (P) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="para" value="{{ old('para', $anc?->para ?? 0) }}" min="0" required>
                                        <div class="form-text">Jumlah persalinan</div>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label fw-bold">Abortus (A) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="abortus" value="{{ old('abortus', $anc?->abortus ?? 0) }}" min="0" required>
                                        <div class="form-text">Jumlah keguguran</div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">HPHT (Hari Pertama Haid Terakhir)</label>
                                        <input type="date" class="form-control" id="hpht" name="hpht" value="{{ old('hpht', $anc?->hpht?->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Taksiran Persalinan (HPL)</label>
                                        <input type="date" class="form-control" id="estimated_due_date" name="estimated_due_date" value="{{ old('estimated_due_date', $anc?->estimated_due_date?->format('Y-m-d')) }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Usia Kehamilan Saat Ini (Minggu)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.1" class="form-control" name="gestational_age_weeks" value="{{ old('gestational_age_weeks', $anc?->gestational_age_weeks) }}" placeholder="Contoh: 28.5">
                                        <span class="input-group-text">Minggu</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Keluhan Ibu Hamil</label>
                                    <textarea class="form-control" name="complaints" rows="3" placeholder="Contoh: Mual, pusing, nyeri punggung, gerakan janin aktif...">{{ old('complaints', $anc?->complaints) }}</textarea>
                                </div>
                            </div>

                            <!-- Pemeriksaan Fisik Ibu & Janin -->
                            <div class="col-lg-6">
                                <h6 class="fw-bold text-primary mb-3"><i class="fas fa-heartbeat me-2"></i>2. Pemeriksaan Fisik Ibu & Janin</h6>
                                
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Tekanan Darah Ibu</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="systolic" value="{{ old('systolic', $anc?->systolic ?? $visit->nursingAssessment?->systolic ?? 120) }}" placeholder="Sistole">
                                            <span class="input-group-text">/</span>
                                            <input type="number" class="form-control" name="diastolic" value="{{ old('diastolic', $anc?->diastolic ?? $visit->nursingAssessment?->diastolic ?? 80) }}" placeholder="Diastole">
                                            <span class="input-group-text">mmHg</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Berat Badan Ibu</label>
                                        <div class="input-group">
                                            <input type="number" step="0.1" class="form-control" name="weight" value="{{ old('weight', $anc?->weight ?? $visit->nursingAssessment?->weight ?? 60) }}">
                                            <span class="input-group-text">kg</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Tinggi Fundus Uteri (TFU)</label>
                                        <div class="input-group">
                                            <input type="number" step="0.1" class="form-control" name="fundal_height" value="{{ old('fundal_height', $anc?->fundal_height) }}" placeholder="24">
                                            <span class="input-group-text">cm</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Denyut Jantung Janin (DJJ)</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="fetal_heart_rate" value="{{ old('fetal_heart_rate', $anc?->fetal_heart_rate) }}" placeholder="140">
                                            <span class="input-group-text">dpm</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Letak / Presentasi Janin</label>
                                    <select class="form-select" name="fetal_presentation">
                                        <option value="">-- Pilih Presentasi --</option>
                                        <option value="kepala" {{ old('fetal_presentation', $anc?->fetal_presentation) == 'kepala' ? 'selected' : '' }}>Presentasi Kepala (Vertex)</option>
                                        <option value="bokong" {{ old('fetal_presentation', $anc?->fetal_presentation) == 'bokong' ? 'selected' : '' }}>Presentasi Bokong (Breech / Sungsang)</option>
                                        <option value="lintang" {{ old('fetal_presentation', $anc?->fetal_presentation) == 'lintang' ? 'selected' : '' }}>Letak Lintang (Transverse)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Rencana Penatalaksanaan / Terapi</label>
                                    <textarea class="form-control" name="treatment_plan" rows="3" placeholder="Pemberian tablet Fe, kalsium, edukasi tanda bahaya kehamilan, USG rujukan...">{{ old('treatment_plan', $anc?->treatment_plan) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                <i class="fas fa-save me-2"></i>Simpan Catatan Antenatal Care (ANC)
                            </button>
                        </div>
                    </form>
                </div>

                <!-- 2. CHILD GROWTH & KMS TAB -->
                <div class="tab-pane fade" id="child" role="tabpanel">
                    @php $cg = $visit->childGrowth; @endphp
                    <div class="row g-4">
                        <!-- Left: Form -->
                        <div class="col-lg-5">
                            <h6 class="fw-bold text-success mb-3"><i class="fas fa-edit me-2"></i>Entri Data Pertumbuhan Hari Ini</h6>
                            <form action="{{ route('kia.child.store', $visit->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Usia Anak (Bulan) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="age_months" value="{{ old('age_months', $cg?->age_months ?? 12) }}" min="0" max="120" required>
                                        <span class="input-group-text">Bulan</span>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-bold">Berat Badan <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" step="0.1" class="form-control" name="weight" value="{{ old('weight', $cg?->weight ?? $visit->nursingAssessment?->weight) }}" min="1" max="100" required>
                                            <span class="input-group-text">kg</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold">Tinggi / Panjang <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" step="0.1" class="form-control" name="height" value="{{ old('height', $cg?->height ?? $visit->nursingAssessment?->height) }}" min="30" max="200" required>
                                            <span class="input-group-text">cm</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Lingkar Kepala</label>
                                    <div class="input-group">
                                        <input type="number" step="0.1" class="form-control" name="head_circumference" value="{{ old('head_circumference', $cg?->head_circumference) }}">
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status Imunisasi Diberikan</label>
                                    <input type="text" class="form-control" name="immunization_status" value="{{ old('immunization_status', $cg?->immunization_status) }}" placeholder="Contoh: DPT-HB-Hib 3, Polio 4, PCV 2...">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Perkembangan Motorik / Milestone (KPSP)</label>
                                    <textarea class="form-control" name="development_milestone" rows="2" placeholder="Contoh: Sudah bisa berdiri berpegangan, mengucap 1-2 kata bermakna...">{{ old('development_milestone', $cg?->development_milestone) }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Catatan Gizi / Edukasi</label>
                                    <textarea class="form-control" name="notes" rows="2" placeholder="Pemberian ASI eksklusif, MPASI adekuat, stimulasi motorik...">{{ old('notes', $cg?->notes) }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-success w-100 fw-bold">
                                    <i class="fas fa-save me-2"></i>Simpan Pemantauan Anak
                                </button>
                            </form>
                        </div>

                        <!-- Right: Growth Chart (Chart.js) & Historical Table -->
                        <div class="col-lg-7">
                            <h6 class="fw-bold text-primary mb-3"><i class="fas fa-chart-line me-2"></i>Grafik Pertumbuhan Anak (KMS Digital)</h6>
                            <div class="card border mb-3">
                                <div class="card-body p-3">
                                    <canvas id="growthChart" height="200"></canvas>
                                </div>
                            </div>

                            <h6 class="fw-bold text-dark mb-2 small"><i class="fas fa-table me-1"></i>Tabel Riwayat Pertumbuhan Sebelumnya:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Usia</th>
                                            <th>BB (kg)</th>
                                            <th>TB (cm)</th>
                                            <th>LK (cm)</th>
                                            <th>Imunisasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($historicalGrowths as $hg)
                                            <tr>
                                                <td class="fw-bold">{{ $hg->age_months }} bulan</td>
                                                <td>{{ $hg->weight }} kg</td>
                                                <td>{{ $hg->height }} cm</td>
                                                <td>{{ $hg->head_circumference ?? '-' }} cm</td>
                                                <td>{{ $hg->immunization_status ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-2">Belum ada riwayat pengukuran anak.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Naegele's rule for HPL: HPHT + 7 days, - 3 months, + 1 year
    $('#hpht').on('change', function() {
        var val = $(this).val();
        if (val) {
            var hphtDate = new Date(val);
            var hpl = new Date(hphtDate);
            hpl.setDate(hpl.getDate() + 7);
            hpl.setMonth(hpl.getMonth() + 9); // Equivalent to -3 months + 1 year
            
            var yyyy = hpl.getFullYear();
            var mm = String(hpl.getMonth() + 1).padStart(2, '0');
            var dd = String(hpl.getDate()).padStart(2, '0');
            $('#estimated_due_date').val(yyyy + '-' + mm + '-' + dd);
        }
    });

    // Chart.js Growth Chart
    var ctx = document.getElementById('growthChart');
    if (ctx) {
        var labels = @json($historicalGrowths->map(fn($h) => $h->age_months . ' bln'));
        var weights = @json($historicalGrowths->pluck('weight'));
        var heights = @json($historicalGrowths->pluck('height'));

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.length > 0 ? labels : ['0 bln', '3 bln', '6 bln', '9 bln', '12 bln'],
                datasets: [
                    {
                        label: 'Berat Badan (kg)',
                        data: weights.length > 0 ? weights : [3.2, 5.8, 7.5, 8.8, 9.6],
                        borderColor: '#2EACF0',
                        backgroundColor: 'rgba(46, 172, 240, 0.1)',
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Tinggi Badan (cm)',
                        data: heights.length > 0 ? heights : [50, 60, 67, 72, 75],
                        borderColor: '#28a745',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        tension: 0.3,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Berat (kg)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Tinggi (cm)' }
                    }
                }
            }
        });
    }
});
</script>
@endpush
