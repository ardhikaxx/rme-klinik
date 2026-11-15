@extends('layouts.app')

@section('title', 'Form Pengkajian Awal Keperawatan - ' . $visit->patient->name)
@section('page_title', 'Form Pengkajian Awal & Triase')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('nursing.index') }}">Pengkajian Perawat</a></li>
    <li class="breadcrumb-item active" aria-current="page">Asesmen {{ $visit->visit_number }}</li>
@endsection

@section('page_actions')
    <a href="{{ route('nursing.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Antrean
    </a>
@endsection

@section('content')
<div class="container-fluid p-0">
    </div>

    <!-- Patient Header Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary text-white p-3 fs-4 fw-bold text-center" style="width: 55px; height: 55px;">
                            {{ substr($visit->patient->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-5">{{ $visit->patient->name }}</div>
                            <div class="text-muted small">
                                No. RM: <span class="badge bg-primary">{{ $visit->patient->medical_record_number }}</span> | 
                                NIK: {{ $visit->patient->nik ?? '-' }} | 
                                TTL: {{ $visit->patient->place_of_birth ?? '-' }}, {{ $visit->patient->date_of_birth?->format('d/m/Y') }} ({{ $visit->patient->age }} thn) | 
                                JK: {{ $visit->patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }} (Gol: {{ $visit->patient->blood_type }})
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 border-start-md mt-3 mt-md-0">
                    <div class="small">
                        <div><strong class="text-muted">Poli Tujuan:</strong> <span class="fw-bold text-primary">{{ $visit->polyclinic?->name }}</span></div>
                        <div><strong class="text-muted">Dokter:</strong> <span class="fw-bold text-dark">{{ $visit->doctor?->formatted_name }}</span></div>
                        <div><strong class="text-muted">Penjamin:</strong> <span class="badge bg-secondary text-uppercase">{{ $visit->payment_type }}</span> (Antrean: <strong>{{ $visit->queue_number }}</strong>)</div>
                    </div>
                </div>
            </div>

            <!-- Allergy Alert -->
            @if($visit->patient->allergies && $visit->patient->allergies->count() > 0)
                <div class="alert alert-danger mt-3 mb-0 py-2 d-flex align-items-center" role="alert">
                    <i class="fas fa-hand-dots fs-5 me-2 text-danger"></i>
                    <div>
                        <strong class="text-danger">Peringatan Alergi:</strong>
                        @foreach($visit->patient->allergies as $alg)
                            <span class="badge bg-danger ms-1">{{ $alg->allergen_name }} ({{ $alg->reaction }})</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Form Section -->
    <form action="{{ route('nursing.store', $visit->id) }}" method="POST" id="nursingForm">
        @csrf

        <div class="row g-4">
            <!-- Anamnesis Awal -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas fa-comments me-2"></i>Anamnesis Keperawatan
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Keluhan Utama <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('chief_complaint') is-invalid @enderror" 
                                      name="chief_complaint" rows="3" required 
                                      placeholder="Uraikan keluhan yang dirasakan pasien saat ini...">{{ old('chief_complaint', $assessment?->chief_complaint) }}</textarea>
                            @error('chief_complaint')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Riwayat Penyakit Sekarang</label>
                            <textarea class="form-control" name="current_illness_history" rows="2" 
                                      placeholder="Perjalanan penyakit saat ini, sudah berapa lama, onset...">{{ old('current_illness_history', $assessment?->current_illness_history) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Riwayat Penyakit Dahulu</label>
                            <textarea class="form-control" name="past_illness_history" rows="2" 
                                      placeholder="Hipertensi, diabetes, asma, riwayat operasi...">{{ old('past_illness_history', $assessment?->past_illness_history) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Riwayat Penyakit Keluarga</label>
                            <textarea class="form-control" name="family_illness_history" rows="2" 
                                      placeholder="Penyakit turunan dalam keluarga...">{{ old('family_illness_history', $assessment?->family_illness_history) }}</textarea>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Riwayat Konsumsi Obat / Pengobatan Terakhir</label>
                            <textarea class="form-control" name="current_medication" rows="2" 
                                      placeholder="Obat rutin yang sedang dikonsumsi pasien...">{{ old('current_medication', $assessment?->current_medication) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tanda-Tanda Vital & Antropometri -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas fa-heartbeat me-2"></i>Tanda-Tanda Vital & Antropometri
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Tanda Vital -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tekanan Darah (Sistolik / Diastolik)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="systolic" id="systolic" 
                                           value="{{ old('systolic', $assessment?->systolic ?? 120) }}" placeholder="Sistole" min="50" max="300">
                                    <span class="input-group-text">/</span>
                                    <input type="number" class="form-control" name="diastolic" id="diastolic" 
                                           value="{{ old('diastolic', $assessment?->diastolic ?? 80) }}" placeholder="Diastole" min="30" max="200">
                                    <span class="input-group-text">mmHg</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Suhu Tubuh</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" class="form-control" name="temperature" id="temperature" 
                                           value="{{ old('temperature', $assessment?->temperature ?? 36.5) }}" min="30" max="45">
                                    <span class="input-group-text">°C</span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Denyut Nadi</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="pulse_rate" 
                                           value="{{ old('pulse_rate', $assessment?->pulse_rate ?? 80) }}" min="30" max="250">
                                    <span class="input-group-text">x/mnt</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Pernapasan</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="respiratory_rate" 
                                           value="{{ old('respiratory_rate', $assessment?->respiratory_rate ?? 20) }}" min="5" max="80">
                                    <span class="input-group-text">x/mnt</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Saturasi Oksigen</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" class="form-control" name="oxygen_saturation" 
                                           value="{{ old('oxygen_saturation', $assessment?->oxygen_saturation ?? 98) }}" min="50" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Antropometri -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Berat Badan</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" class="form-control" name="weight" id="weight" 
                                           value="{{ old('weight', $assessment?->weight ?? 60) }}" min="1" max="500">
                                    <span class="input-group-text">kg</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Tinggi Badan</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" class="form-control" name="height" id="height" 
                                           value="{{ old('height', $assessment?->height ?? 165) }}" min="30" max="250">
                                    <span class="input-group-text">cm</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Lingkar Perut</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" class="form-control" name="waist_circumference" 
                                           value="{{ old('waist_circumference', $assessment?->waist_circumference) }}">
                                    <span class="input-group-text">cm</span>
                                </div>
                            </div>
                        </div>

                        <!-- Live BMI Indicator -->
                        <div class="p-3 border rounded bg-light d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <div class="small text-muted fw-bold">Indeks Massa Tubuh (IMT / BMI):</div>
                                <div class="fs-4 fw-bold text-primary" id="bmiValue">0.0</div>
                            </div>
                            <div class="text-end">
                                <div class="small text-muted fw-bold">Kategori Status Gizi:</div>
                                <span class="badge fs-6 px-3 py-2" id="bmiBadge">Menghitung...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Skrining Khusus -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold text-primary">
                            <i class="fas fa-clipboard-check me-2"></i>Skrining Nyeri, Kesadaran & Risiko Jatuh
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tingkat Kesadaran <span class="text-danger">*</span></label>
                                <select class="form-select" name="consciousness" required>
                                    @foreach(['compos_mentis' => 'Compos Mentis (Sadar Penuh)', 'apatis' => 'Apatis (Acuh tak acuh)', 'somnolen' => 'Somnolen (Mengantuk)', 'sopor' => 'Sopor (Merespon nyeri)', 'koma' => 'Koma (Tidak sadar)'] as $cKey => $cLabel)
                                        <option value="{{ $cKey }}" {{ old('consciousness', $assessment?->consciousness ?? 'compos_mentis') == $cKey ? 'selected' : '' }}>
                                            {{ $cLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Risiko Jatuh (Skala Morse) <span class="text-danger">*</span></label>
                                <select class="form-select" name="fall_risk" required>
                                    <option value="rendah" {{ old('fall_risk', $assessment?->fall_risk ?? 'rendah') == 'rendah' ? 'selected' : '' }}>Rendah (0 - 24)</option>
                                    <option value="sedang" {{ old('fall_risk', $assessment?->fall_risk) == 'sedang' ? 'selected' : '' }}>Sedang (25 - 50)</option>
                                    <option value="tinggi" {{ old('fall_risk', $assessment?->fall_risk) == 'tinggi' ? 'selected' : '' }}>Tinggi (≥ 51) - Pasang Pita Kuning</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Skala Nyeri (NRS / Wong-Baker: 0 - 10) <span class="text-danger">*</span></label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="range" class="form-range flex-grow-1" min="0" max="10" step="1" 
                                       id="pain_scale" name="pain_scale" value="{{ old('pain_scale', $assessment?->pain_scale ?? 0) }}">
                                <span class="badge bg-primary fs-5 px-3 py-2" id="painScaleVal">0</span>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mt-1">
                                <span>0 (Tidak Nyeri)</span>
                                <span>1-3 (Ringan)</span>
                                <span>4-6 (Sedang)</span>
                                <span>7-10 (Berat/Sangat Berat)</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Rencana Asuhan Keperawatan</label>
                            <textarea class="form-control" name="nursing_care_plan" rows="2" 
                                      placeholder="Rencana tindakan mandiri atau kolaboratif perawat...">{{ old('nursing_care_plan', $assessment?->nursing_care_plan) }}</textarea>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Catatan Tambahan</label>
                            <textarea class="form-control" name="notes" rows="2" 
                                      placeholder="Catatan khusus triase perawat...">{{ old('notes', $assessment?->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <a href="{{ route('nursing.index') }}" class="btn btn-light px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4 fw-bold">
                    <i class="fas fa-save me-2"></i>Simpan Pengkajian Awal & Lanjutkan ke Dokter
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function calculateBmi() {
        var weight = parseFloat($('#weight').val());
        var height = parseFloat($('#height').val());

        if (weight > 0 && height > 0) {
            var heightM = height / 100.0;
            var bmi = weight / (heightM * heightM);
            var bmiFormatted = bmi.toFixed(1);
            $('#bmiValue').text(bmiFormatted);

            var badge = $('#bmiBadge');
            badge.removeClass('bg-warning bg-success bg-danger bg-info text-dark text-white');

            if (bmi < 18.5) {
                badge.addClass('bg-warning text-dark').text('Kurus (Underweight)');
            } else if (bmi <= 24.9) {
                badge.addClass('bg-success text-white').text('Normal');
            } else if (bmi <= 29.9) {
                badge.addClass('bg-warning text-dark').text('Kelebihan BB (Overweight)');
            } else {
                badge.addClass('bg-danger text-white').text('Obesitas');
            }
        } else {
            $('#bmiValue').text('0.0');
            $('#bmiBadge').text('-');
        }
    }

    $('#weight, #height').on('input change', calculateBmi);
    calculateBmi();

    // Pain scale slider update
    $('#pain_scale').on('input', function() {
        var val = $(this).val();
        $('#painScaleVal').text(val);
        var badge = $('#painScaleVal');
        badge.removeClass('bg-success bg-warning bg-danger text-dark text-white');
        if (val == 0) {
            badge.addClass('bg-success text-white');
        } else if (val <= 3) {
            badge.addClass('bg-info text-white');
        } else if (val <= 6) {
            badge.addClass('bg-warning text-dark');
        } else {
            badge.addClass('bg-danger text-white');
        }
    }).trigger('input');
});
</script>
@endpush
